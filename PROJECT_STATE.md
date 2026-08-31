# PROJECT_STATE.md

**This file is the single source of truth for where Growza currently stands.**
Read this before starting any new level, per the master prompt's workflow (§57).

---

## Current Version

`0.1.0-foundation` (pre-1.0 — LEVEL 0 in progress)

## Completed Levels

| Level | Name | Status | Verified? |
|---|---|---|---|
| — | Master Architecture Specification | ✅ Complete | Reviewed with stakeholder |
| 0 | Project Foundation | 🟡 In progress (this delivery) | ⚠️ Unverified — see below |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: Batch A remainder** — LEVEL 1 (Design System), LEVEL 3 (Authentication), LEVEL 4 (RBAC implementation on top of the schema this level creates).

---

## What LEVEL 0 Actually Delivered

- Full Laravel 11 project skeleton (composer.json, bootstrap/app.php, bootstrap/providers.php, config/*)
- Domain-oriented folder structure under `app/Domain/*` for all 16 bounded contexts identified in the architecture spec (empty except `.gitkeep`, ready for their owning levels)
- Framework-baseline migrations: `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`/`failed_jobs`/`job_batches`, `personal_access_tokens` (Sanctum)
- RBAC schema (`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`) via spatie/laravel-permission, **team-scoped** from day one
- `teams` / `team_user` tables — scaffolded now specifically so Agencies (LEVEL 21-22) never requires a multi-tenancy retrofit (per the v1.0→v2.0 dependency map)
- `audit_logs` table — scaffolded now so LEVEL 3/4 can start writing audit events immediately instead of LEVEL 18 backfilling
- `settings` key-value table — scaffolded now so LEVEL 10/14/15 have a config surface instead of hard-coded values
- `User` model in `App\Domain\Identity\Models` (not the framework-default namespace) — has `HasRoles`, `HasApiTokens`, `SoftDeletes`, `MustVerifyEmail`; deliberately has **no** business logic beyond an `isSuspended()` helper
- Six empty-but-registered service providers (`DomainServiceProvider`, `AuthServiceProvider`, `EventServiceProvider`, `PaymentServiceProvider`, `ProviderIntegrationServiceProvider`, `AppServiceProvider`) so later levels fill in bindings rather than also wiring bootstrap
- Three middleware aliases registered (`permission`, `role`, `api.scope`) with working but minimal implementations — real permission logic is exercised once LEVEL 4 seeds roles/permissions
- Route skeleton: `web.php`, `api.php` (`/api/v1` prefix), `admin.php` (registered under `/admin` with its own `web` middleware group + `admin.` name prefix), `console.php`
- `.env.example` — every variable named in the master prompt's §51 example plus the full set this architecture actually needs (Redis logical DB separation, payment gateway keys, provider adapter default, Sanctum config)
- Docker development stack (`docker-compose.yml`: app, nginx, postgres, redis, queue worker, scheduler) + a production Supervisor unit file (`docker/supervisor/growza-worker.conf`) separating payment-queue workers from general workers, matching ARCHITECTURE.md §12
- Pest testing configured (`phpunit.xml`, `tests/Pest.php`, `tests/TestCase.php`) with 3 baseline tests (environment boot, homepage 200, `/up` health check)
- `.gitignore` matching Laravel 11 defaults plus Growza-specific ignores
- One placeholder Blade layout + homepage view, solely so the LEVEL 0 skeleton has a real working request→response path rather than zero routes

## Architecture Decisions Finalized This Level

- **RBAC package:** `spatie/laravel-permission`, confirmed (was flagged open in the architecture spec §20)
- **Auth scaffolding:** Laravel Fortify (headless), confirmed — required for the LEVEL 3 registration/2FA flow
- **Testing framework:** Pest, confirmed
- **DB:** PostgreSQL as `DB_CONNECTION` default; MySQL connection config present but not default

## Database Changes This Level

8 migrations — see `DATABASE.md` for the full entity list and rationale per table. No domain tables (wallets, orders, services, etc.) are created yet; those arrive with their owning levels per the batch plan.

## Installed Packages (from composer.json)

`laravel/framework ^11.0`, `laravel/fortify`, `laravel/sanctum`, `livewire/livewire`, `spatie/laravel-permission`, `guzzlehttp/guzzle`, `pragmarx/google2fa-laravel` — dev: `pestphp/pest` (+ laravel plugin), `laravel/pint`, `laravel/sail`, `mockery`, `fakerphp/faker`, `laravel/pail`, `barryvdh/laravel-ide-helper`.

## Routes

Only `GET /` (marketing placeholder) and the framework `/up` health check exist. Everything else is a commented-out `require` waiting on its owning level.

## Environment Requirements

PHP 8.3+, PostgreSQL 16 (or MySQL 8 for compatibility mode), Redis 7, Composer 2, Node 20+ (for Vite once LEVEL 1 adds frontend build assets — not yet required at LEVEL 0).

## Integrations

None active. Payment gateway and provider adapter contracts exist only as documented interfaces in `ARCHITECTURE.md` — no concrete `PaystackGateway`/`FlutterwaveGateway`/named provider adapter classes exist yet (those are LEVEL 9 and LEVEL 10 respectively).

## Known Issues

- **No PHP/Composer execution available in the authoring environment.** Every file here was hand-written to match real Laravel 11 / Sanctum / spatie-permission conventions, but `composer install`, `php artisan migrate`, `php artisan test`, and `php artisan serve` have **not** been executed. See "Unverified Items" below — run these yourself and report back any error so it can be fixed under the master prompt's Error Protocol (§49).
- `config/permission.php` is included by hand (matching spatie/laravel-permission's published defaults, with `teams => true`) since `vendor:publish` cannot run here. Worth diffing against the package's actual published version after `composer install`, in case the installed package version's default config has shifted since this was written.

## Tests

3 Pest tests written (`tests/Unit/ExampleTest.php`, `tests/Feature/HomepageTest.php`, `tests/Feature/HealthCheckTest.php`). **Not executed** — see Known Issues.

## Security Decisions

- `$fillable` (not `$guarded = []`) will be enforced on every model going forward, starting with `User`.
- Sessions, cache, and queue each use a **separate Redis logical database** so a cache flush can never touch queued jobs or active sessions.
- `APP_ALLOW_INDEXING` env flag exists now so LEVEL 31 has a single switch rather than per-route logic to keep dashboard/admin routes out of search indexes.

## Deployment Status

Not deployed anywhere. Docker Compose stack is for local development only — see `docker-compose.yml` comments. Production deployment steps belong in `DEPLOYMENT.md` (stub only at this level; fully written at LEVEL 33).

---

## ⚠️ Unverified Items (per master prompt §5 — explicitly flagged, not assumed passing)

1. `composer install` has not been run — dependency resolution/version conflicts are unverified.
2. Migrations have not been run against a real PostgreSQL instance — column-level syntax is unverified beyond manual review.
3. Pest tests have not been executed.
4. `php artisan serve` / the `/` and `/up` routes have not been hit by an actual HTTP request.
5. Docker Compose stack has not been built or started.

**Action needed from you:** clone this into a real PHP 8.3 + Composer environment, run `composer install`, copy `.env.example` to `.env`, run `php artisan key:generate`, set up Postgres + Redis (or `docker compose up`), run `php artisan migrate`, then `php artisan test`. Report back anything that fails — I'll fix it via the Error Protocol rather than guessing.
