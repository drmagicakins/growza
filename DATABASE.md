# DATABASE.md

Database strategy and current schema for Growza. Update this file whenever a migration is added — this is the authoritative reference, `ARCHITECTURE.md` §4-5 only sketches the entity map at a conceptual level.

---

## Strategy

- **Primary driver:** PostgreSQL. MySQL is kept configured for compatibility but is not the default and is not tested against in CI at this stage — if you switch to it, re-verify any Postgres-specific column behavior first.
- **Monetary values:** stored as **integer minor units** (kobo, not naira-as-decimal) everywhere money is recorded. No `decimal`/`float` column will ever hold a currency amount in this schema. This is enforced starting with the Wallet domain (LEVEL 8) — see `ARCHITECTURE.md` §8.
- **Soft deletes:** applied to `users` and `teams` (entities other rows reference and where hard deletion would orphan financial/audit history). Catalogue and order data will get the same treatment when introduced — never hard-delete anything a `wallet_transaction`, `order`, or `audit_log` might reference.
- **Foreign keys:** every relationship is a real FK constraint, not an application-level convention. Cascade behavior is chosen deliberately per relationship (e.g. `team_user` cascades on team/user delete; `orders.user_id` will restrict, not cascade, once introduced — a user with orders cannot be hard-deleted).
- **Naming:** snake_case tables, plural; singular model names; standard Laravel `{table}_id` foreign key convention throughout.
- **Migrations are additive-only** once a level ships to a real environment — see `ARCHITECTURE.md` §53 ("never destroy existing production data during upgrades"). A later level that needs to change a LEVEL 0 table's shape will get a **new** migration, not an edit to these files.

---

## Current Schema (LEVEL 0, amended at LEVEL 4)

The tables below were created at LEVEL 0. Where LEVEL 4 changed a LEVEL 0 shape it did so with a **new** migration, per the additive rule above — see the RBAC pivot note under "RBAC".

### `users`
Core identity table. Notably:
- `phone` is nullable but unique (registration requires it per LEVEL 3's field list, but the column allows null so seeders/admin-created accounts aren't blocked pre-verification).
- `status` (`active` / `suspended` / `banned`) is independent of `email_verified_at` — suspension is an admin action (LEVEL 16/45), not a verification state.
- `two_factor_*` columns exist now so LEVEL 3 can implement optional 2FA without a follow-up migration.
- Soft-deleted, so a deleted account never breaks a historical order's `user_id` foreign key once orders exist.

### `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens`
Standard Laravel/Sanctum framework tables. `failed_jobs` uses the UUID variant specifically because `ARCHITECTURE.md` §12 requires "failed_jobs monitoring" that can be cross-referenced by a job's own idempotency key in the admin System Health view (LEVEL 16/35).

### RBAC — `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`
Spatie laravel-permission schema, **team-scoped** (the `team_id` columns are present now). This means a permission can, from day one, be granted to a user *within a specific team* — required for Agencies (LEVEL 21-22) where a "Manager" role's meaning is scoped to one client team, not global. Role/permission **seeding** (the actual Super Admin/Administrator/Finance Manager/etc. roles and view_users/manage_refunds/etc. permissions listed in the master prompt's LEVEL 4) happens in `database/seeders/` at LEVEL 4, not this migration.

#### Pivot `team_id` is nullable (LEVEL 4 fix — migration `2026_01_03_000_...`)
`model_has_roles.team_id` and `model_has_permissions.team_id` are **NULL-able**, meaning "this grant is global, not scoped to a team". Until Agencies (LEVEL 21) call `setPermissionsTeamId()`, every grant is global and rows carry `team_id = NULL`; spatie's `roles()`/`permissions()` filter on `wherePivot($teamsKey, getPermissionsTeamId())`, which matches NULL-to-NULL.

This required more than `->change()`: the LEVEL 0 stub had made `team_id` the first column of a composite **PRIMARY KEY**, and **MySQL/InnoDB forces every primary-key column to be NOT NULL** — so `ALTER ... MODIFY team_id NULL` is silently ignored (the migration reports DONE, `information_schema` still reads `NO`). The fix instead **drops the primary key and replaces it with an equivalent UNIQUE index** over the same columns; a UNIQUE index permits NULLs on both MySQL and SQLite while still preventing duplicate grant rows. The two pivot FKs are dropped and recreated around the key rewrite.

> **False green to remember:** the Pest suite runs on SQLite in-memory, where a NULL *is* allowed inside a composite PK. The 11 RBAC tests passed even while the MySQL schema the app actually runs against was broken. Schema changes that depend on key/constraint behaviour must be checked against the real driver, not just the suite.

### `teams`, `team_user`
Scaffolded at LEVEL 0 specifically to satisfy the v1.0→v2.0 dependency map (`ARCHITECTURE.md` §22): Agencies cannot be retrofitted onto a schema that assumed single-owner users. `team_role` is a plain string for now (Agency Owner / Manager / Finance / Campaign Manager / Viewer per LEVEL 22) — enforcement is a Policy concern, not a DB constraint.

### `audit_logs`
Polymorphic actor (`actor_type`/`actor_id`, nullable — a system-triggered action may have no user actor) and polymorphic subject (`subject_type`/`subject_id`). `before`/`after` are JSON snapshots. This table exists from LEVEL 0 so LEVEL 3 (password changes, 2FA toggles) and LEVEL 4 (role changes) can start writing to it immediately rather than LEVEL 18 needing a backfill migration.

### `settings`
Key-value store scoped by `group` (`general`, `payments`, `referrals`, `providers`, ...). `is_encrypted` flags rows whose `value` should be written/read through Laravel's `encrypted` cast at the application layer (e.g. provider credentials) — the column itself stores whatever the application layer put there; encryption is not automatic at the DB level.

---

## Explicitly NOT Created Yet

To avoid the master prompt's "do not build ahead of the current level" instruction being violated in spirit, the following tables are deliberately **absent** at LEVEL 0, even though `ARCHITECTURE.md` §4 lists them — they arrive with their owning batch:

- `wallets`, `wallet_transactions` — LEVEL 8
- `payment_transactions`, `payment_webhook_events` — LEVEL 9
- `platforms`, `service_categories`, `services`, `service_price_tiers` — LEVEL 6
- `orders`, `order_status_histories`, `order_provider_dispatches` — LEVEL 7
- `providers`, `provider_services`, `provider_health_logs` — LEVEL 10
- `referral_codes`, `referral_conversions`, `referral_commissions` — LEVEL 14
- `coupons`, `coupon_redemptions` — LEVEL 15
- `support_tickets`, `support_ticket_replies` — LEVEL 13
- `notification_preferences` — LEVEL 12 (`notifications` table itself ships with Laravel's own notification migration when LEVEL 12 is implemented)
- `reseller_profiles`, `api_credentials`, `agency_clients` — LEVEL 20/21

## Indexing Notes

At LEVEL 0, indexes exist only where a LEVEL 0 table's own query patterns demand them (`users.status`, `sessions.last_activity`, `audit_logs.action`/`created_at`, RBAC's team-scoped composite keys). Every future migration must justify its indexes against a real query pattern from that level's feature — index-everything-by-default is explicitly against `ARCHITECTURE.md` §28 (performance discipline).

## Seeders / Factories
`RoleAndPermissionSeeder` (LEVEL 4) is the first real seeder: it seeds the 9 roles and 11 permissions and is idempotent (`firstOrCreate` + `syncPermissions`). It is wired into `DatabaseSeeder`, so `php artisan db:seed` is the single entry point. `UserFactory` is the first real factory (LEVEL 4).
