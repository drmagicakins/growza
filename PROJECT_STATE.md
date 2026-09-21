# PROJECT_STATE.md

> **Provenance note:** LEVEL 1 was integrated directly into the user's own
> deployed codebase (their real `vendor/`, `.env`, `composer.lock`, `artisan`
> from an actual `composer install` — not regenerated here). Only new LEVEL 1
> files were added and two existing files (`routes/web.php`,
> `resources/views/layouts/app.blade.php`) received minimal, targeted edits.
> Nothing in `app/`, `config/`, `bootstrap/`, or `database/migrations/` was
> touched. `npm install` + `npx vite build` were re-run against this exact
> merged codebase and produced identical, correct output to the earlier
> verification — see the LEVEL 1 entry in `CHANGELOG.md`.

**This file is the single source of truth for where Growza currently stands.**
Read this before starting any new level, per the master prompt's workflow (§57).

---

## Current Version

`0.6.0-foundation` (pre-1.0 — LEVEL 5 delivered)

## Completed Levels

| Level | Name | Status | Verified? |
|---|---|---|---|
| — | Master Architecture Specification | ✅ Complete | Reviewed with stakeholder |
| 0 | Project Foundation | ✅ Complete | ✅ Verified — confirmed running via Docker Compose (Laravel, Nginx, PostgreSQL, Redis, queue worker, scheduler all up; `/` serves Growza homepage) |
| 1 | Brand & Design System | ✅ Complete | ✅ Verified — the reported "Alpine never initialises" defect was a **QA harness artefact, not an app bug**; closed with user confirmation. See `storage/app/HANDOFF.md`. |
| 2 | Public Marketing Website | ✅ Complete | ✅ Verified — all 16 routes 200, contact form round-trip persisted to DB. |
| 3 | Authentication | ✅ Complete | ✅ Verified — **59 Pest tests pass (137 assertions)**, 51 routes, registration/login/logout/verification/2FA driven over real HTTP, audit trail populated. |
| 4 | User & Role System (RBAC) | ✅ Complete | ✅ Verified — **76 Pest tests pass (181 assertions)**; 9 roles / 11 permissions seeded on MySQL, `assignRole()` writes a global (NULL-team) grant, the pivot bug is fixed and reversible. See the LEVEL 4 section. |
| 5 | Customer Dashboard | ✅ Complete | ✅ Verified — **100 Pest tests pass (222 assertions)**; all 10 dashboard routes driven over real HTTP against real MySQL, profile edit round-trips through Fortify and reads back, `/dashboard/orders/1` is a deliberate 404. See the LEVEL 5 section. |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: LEVEL 6 (Service Catalogue)** — database-driven platforms/categories/services, replacing `config('growza-marketing.services')` as the source both the marketing site and the dashboard services page read from. This is the first level that gives LEVEL 7 (orders) something real to reference.

---

## LEVEL 5 — Customer Dashboard (this delivery)

### Delivered
- The LEVEL 3 placeholder `/dashboard` is gone. `resources/views/dashboard/placeholder.blade.php` was deleted — it was the last thing still rendering "the full dashboard is not built yet", and nothing referenced it once the real routes landed.
- Real dashboard layout: a desktop sidebar (`md:fixed md:w-60`) **plus a genuinely distinct mobile pattern** — a full-height off-canvas drawer with larger touch targets (`py-3.5` vs `py-2.5`) and a backdrop, not the sidebar resized. Both render from one `$navItems` array so they cannot drift.
- All 10 routes from the spec: `/dashboard` (name `dashboard`) plus `dashboard.profile`, `.services`, `.orders`, `.wallet`, `.transactions`, `.referrals`, `.support`, `.notifications`, `.settings`.
- The six required metrics (wallet balance, total/active/completed orders, total spent, referral earnings) via `CustomerDashboardMetricsService` — the seam LEVEL 7/8/14 fill in with real queries without the view, controller or route changing. Each zero carries a comment naming the exact query that will replace it.
- A functional profile edit page posting to Fortify's own `user/profile-information` endpoint, reusing the `UpdateUserProfileInformation` Action that already existed from LEVEL 3 — no new controller logic for the update itself.
- `App\Support\Money` — minor-units (kobo) currency formatter, presentation-only.

### Every metric is honestly zero, not faked
Wallet, orders and referral earnings all read `₦0.00` / `0` because no wallet, order or commission exists yet. A test asserts the dashboard renders `₦0.00` **and** the real empty state ("No campaigns yet"), not a fabricated number. Orders, wallet, transactions, referrals, support and notifications each render an explicit "not live yet" empty state rather than a fake list.

### Deliberate scope limits
- **`/dashboard/orders/{order}` is NOT registered.** There is no `Order` model to bind a route parameter to until LEVEL 7; registering it now would be the "fake dashboard where buttons do nothing" the project rules prohibit. A test asserts `/dashboard/orders/1` genuinely 404s.
- **Only the profile screen writes.** Everything else is read-only by design, because nothing else has a model behind it yet.
- **`/dashboard/services` reads `config('growza-marketing.services')`.** LEVEL 6 replaces that with a DB query without touching the view.

### Three real bugs caught while building, not shipped
1. **`x-dropdown-item as="button"` hardcoded `type="button"`.** Wrapping it in the logout `<form>` would have made the button a silent no-op — it would never submit. Fixed in the component itself (added a `type` prop) rather than worked around at one call site, since any future use inside a form hits the same trap.
2. **`<x-card as="a" href="...">` does not work** — the card component only ever renders a `<div>` and has no `as`/`href` prop, so the Settings hub's first tile would have looked clickable and done nothing. Rewritten to wrap the card in a real `<a>`.
3. **Route naming collision avoided before it shipped.** Naming the dashboard index `dashboard.index` inside the `dashboard.` group would have broken every existing `route('dashboard')` reference (Fortify's post-login redirect target, LEVEL 3 tests, view links). `/dashboard` is registered under the plain name `dashboard` outside the prefixed group; every sub-page is `dashboard.*`.

### Verified this level (real MySQL 8 + PHP 8.4.12)
- `php artisan test` — **100 passed, 222 assertions, 0 failures** (76 existing + 24 new).
- `storage/app/verify-level5-mysql.php` — creates a real Customer through the same path registration uses, against **MySQL** (not SQLite), and renders all 10 screens through the real controller: every one returns 11–18 KB of real HTML with the sidebar present, no `Route [...] not defined` / `Undefined variable` / `ViewException`, and `noindex, nofollow` on every private page. `assignRole()` still writes `team_id = NULL` and reads back `true` (the LEVEL 4 pivot fix holds).
- `storage/app/qa-level5-http.mjs` — **PASS, exit 0.** Over raw HTTP with a cookie jar: all 10 dashboard URLs 302 an anonymous visitor to `/login`; a real login 302s to `/dashboard`; all 10 pages then return 200 with every expected string present; `/dashboard/orders/1` returns exactly **404** (not 500); both Settings tiles resolve to real URLs; the marketing homepage is unaffected; logout 302s home and the dashboard is closed again.
- `storage/app/qa-level5-profile-http.mjs` — **PASS, exit 0.** The profile write driven the way a browser does it: GET the form (which posts to `.../user/profile-information` with a spoofed `_method=PUT` and a CSRF token), PUT new values, then re-GET and confirm the new name and phone are rendered back, the success banner appears, the old name is gone, and the dashboard home greets the user by the new name. A duplicate phone is correctly rejected. The probe restores the fixture afterwards, so it is re-runnable.
- `node_modules/vite/bin/vite.js build` — succeeded; CSS **46.17 kB → 46.72 kB**, matching the archive's claim that the dashboard utilities compiled. `public/build/manifest.json` and the served `<link>`/`<script>` tags agree on `app-DLubIMpg.css`, so the build is not stale.
- `vendor/bin/pint --test` passes on all LEVEL 5 files after one fix (`CustomerDashboardMetrics` had a multi-line empty constructor body).

### Still unverified
- **No visual browser pass.** The off-canvas drawer's Alpine transitions, and the sidebar↔drawer switch at the `md` breakpoint, are asserted structurally (the markup and the `md:` classes are present in both) but have not been seen at 1440/1280/1024/768/430/390/375px. That remains part of the LEVEL 58 responsive checklist.
- `php artisan route:list` now reports **62 routes** (was 58); 10 of them are the dashboard.

---

## LEVEL 4 — User & Role System / RBAC

### Delivered
- `RoleName` / `PermissionName` backed enums — the exact 9 roles and 11 permissions named in the master prompt, as the canonical source of the string values (no separate mapping to keep in sync)
- `RoleAndPermissionSeeder` (idempotent — `firstOrCreate` / `syncPermissions` throughout) granting each role a deliberately scoped permission set; wired into `DatabaseSeeder`
- `UserPolicy` — the first real Policy in the codebase, establishing the pattern every later domain follows
- `Gate::before` Super Admin bypass in `AuthServiceProvider`, so "Super Admin means everything" does not depend on every future Policy remembering to special-case it
- Every self-registered user is assigned the `Customer` role automatically; nothing above it is reachable through the public registration form
- A minimal permission-gated `/admin` placeholder proving the full chain (`auth` → `active` → `verified` → `permission:view_users`) actually admits and rejects correctly — not just that the seeder ran
### The pivot bug this level surfaced and fixed
`assignRole()` failed on MySQL with `SQLSTATE[23000] ... NOT NULL constraint failed: model_has_roles.team_id`, taking out registration and all 17 RBAC tests. The LEVEL 0 stub had made `team_id` both NOT NULL **and** the first column of a composite PRIMARY KEY.

- The naive `->change()` fix was a **silent no-op on MySQL** — InnoDB forces every PRIMARY KEY column to be NOT NULL, so the `ALTER ... MODIFY ... NULL` is accepted and the migration reports DONE while `information_schema` still reads `NO`. Confirmed by reading the schema back on MySQL 8.
- Migration `2026_01_03_000_make_rbac_pivot_team_id_nullable` therefore **drops the primary key and replaces it with an equivalent UNIQUE index** (which permits NULLs on both drivers while still preventing duplicate grants), makes `team_id` nullable, and drops/recreates the pivot FKs around the rewrite. Fully reversible.
- **False green caught:** the Pest suite runs on SQLite in-memory, which *does* allow NULLs inside a composite PK, so the RBAC tests were green while the MySQL schema the app runs against was broken. The fix is verified against MySQL directly, not just via the suite.

### Deliberate scope limits
- **Only `UserPolicy` exists.** `OrderPolicy`, `SupportTicketPolicy`, etc. are written by the level that introduces their model.
- **`Customer` and `Reseller` hold no admin-facing permissions.** A customer's access to their own data is an ownership check in that domain's Policy, not a blanket permission.
- **Team-scoped permissions (Agencies, LEVEL 21-22) are untouched.** Every role seeded here has `team_id = null` — spatie's default when `setPermissionsTeamId()` is never called.

### Verified this level (real MySQL 8 + PHP 8.4.12)
- `php artisan migrate:fresh --seed --force` — all **11 migrations + the RBAC seeder** run clean
- `php artisan test` — **76 passed, 181 assertions, 0 failures** (LEVEL 3's 59 + LEVEL 4's 17)
- `assignRole('Customer')` against real MySQL writes `model_has_roles` with `team_id = null`, and `hasRole('Customer')` reads back `true` — the original error is gone
- Migration rolled back and re-applied cleanly (NOT NULL + composite PK restored on rollback; nullable + unique index on re-apply)
- `vendor/bin/pint --test` passes on the migration
### Still unverified
- No browser render of the `/admin` placeholder (the QA driver cannot execute document script — see `storage/app/HANDOFF.md`); the middleware chain is proven by Pest, not by a click.

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

## LEVEL 1 — Brand & Design System (this delivery)

- Vite + Tailwind CSS + Alpine.js pipeline (`package.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js`)
- Bespoke two-color token system: `ink` (warm neutral scale) + `ember` (single amber accent) — not Tailwind's default slate/blue, specifically to avoid the generic-AI-SaaS look. Muted semantic colors (success/warning/danger/info).
- Typography pairing: Fraunces (display serif, headings) + Public Sans (UI/body) — see `DESIGN_SYSTEM.md` for the full rationale.
- Restrained radius scale (8px default, 14px `lg`, no 20px+ "blob" cards) and three low-elevation shadow levels (no glow, no glassmorphism).
- Blade component library covering exactly the LEVEL 1 scope: button, input/textarea/select, card, badge, alert, modal, dropdown, marketing nav bar, breadcrumbs, a Tailwind-styled pagination view, empty state, loading state. **Deliberately does not** include LEVEL 40's broader component set (Tabs, StatCard, ConfirmDialog, Toast) — that's a later level, not built ahead of schedule.
- Live style guide at `/dev/design-system` (non-production only) rendering every component with real props — this is the actual review mechanism for a design system, not a static screenshot.
- `DESIGN_SYSTEM.md` — full written spec plus an explicit **Known Gaps** section (modal has no focus trap yet — flagged for LEVEL 29, not silently shipped as done; no dark mode; a documented Tailwind dynamic-class-interpolation trap and how this codebase avoids it).

### What was actually verified this level (not just claimed)

Unlike LEVEL 0, **Node/npm are available in this sandbox**, so this was verified for real rather than only reviewed by eye:
- `npm install` — succeeded, 93 packages resolved cleanly.
- `npx vite build` — succeeded, produced `app-*.css` (37.65 kB) and `app-*.js` (54.11 kB).
- Caught and fixed a real bug during verification: the style guide's color-swatch loop builds Tailwind class names dynamically (`bg-ink-{{ $shade }}`), which Tailwind's JIT compiler does not detect from source scanning alone — this would have silently rendered blank swatches. Fixed with an explicit `safelist` in `tailwind.config.js`, then re-verified by grepping the compiled CSS output for the specific classes (`bg-ink-50`, `bg-ink-500`, `bg-ink-950`, `bg-ember-500`, `bg-ember-900`) and confirming each one actually compiled. This is the kind of check §5 of the master prompt calls for — not just "the code looks correct."

### Still unverified (flagged, not assumed)

- No responsive breakpoint check has been done against the LEVEL 58 checklist (1440/1280/1024/768/430/390/375px) — that requires actually opening the page at each width.
- The Alpine failure below is diagnosed but **not yet fixed**, so the modal, mobile nav toggle, FAQ disclosure and dismissible alert remain non-functional. The rest of LEVEL 1's component library is verified good.

### ✅ Real-browser QA (2026-02-14) — the gaps above, closed
`php artisan serve` on `127.0.0.1:8125` + headless Chromium. Scripts live in
`storage/app/` and are rerunnable: `qa-assets.mjs` (all routes), `qa-alpine.mjs`
(interactivity), `qa-faq.mjs` (disclosure), `probe-routes.ps1` (HTTP + build
artifact hashes).

- **Blade rendering: PASS.** All 8 routes return HTTP 200 with real content —
  `/` 3423 chars, `/services` 1820, `/pricing` 3230, `/how-it-works` 3401,
  `/faq` 1464, `/contact` 1648, `/dev/design-system` 1649, `/up` 66. Every title
  is correct. **Zero console errors, zero failed requests on all 8.**
- **Build artifacts: PASS.** `app-BosLBwkO.css` and `app-D8MKG-Ji.js` both serve
  200 with bodies that match the byte counts in `manifest.json` — no stale hash.
- **Tailwind safelist: PASS.** `bg-ink-50/500/950`, `bg-ember-500/900` all
  resolve in the compiled CSS and render as real swatches.
- **Alpine.js interactivity: ❌ FAIL — see `DESIGN_SYSTEM.md`.** `window.Alpine`
  is `undefined` on every page and the raw `x-` directives are still in the DOM
  (19× `x-data`, 21× `x-show`, 18× `:class`). The modal, mobile nav toggle, FAQ
  disclosure and dismissible alert are all dead. The bundle is proven valid and
  is fetched in full (55410 bytes, 200, `application/javascript`) but never
  executed. **Critically: the console is completely clean** — no error surfaces,
  so a console-only check would have wrongly passed this. Root cause not yet
  isolated; `data-navigate-track="reload"` is the standing suspicion.

This directly contradicts the earlier "LEVEL 1 partially verified" status: the
component library's markup and CSS are verified, its **interactivity is not
working at all**. Do not describe LEVEL 1 as done until the Alpine issue is fixed
and re-checked with `qa-alpine.mjs`.

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

**LEVEL 2 added (all verified 200):** `/`, `/services`, `/pricing`, `/how-it-works`, `/why-growza`, `/faq`, `/contact` (GET + throttled POST), the five legal pages under `/legal/*`, `/sitemap.xml`, `/robots.txt`.

**LEVEL 3 added (all verified):** Fortify's endpoints — `login` (GET/POST), `register`, `logout`, `password.request/email/reset/update`, `password.confirm`, `verification.notice/verify/send`, `two-factor.login/challenge/enable/confirm/disable/recovery-codes` — plus Growza's own `dashboard` and `settings.security` in `routes/auth.php`.

**LEVEL 5 added (all verified 200 over real HTTP):** the customer dashboard — `dashboard`, `dashboard.profile`, `dashboard.services`, `dashboard.orders`, `dashboard.wallet`, `dashboard.transactions`, `dashboard.referrals`, `dashboard.support`, `dashboard.notifications`, `dashboard.settings`. All ten carry `auth` + `active` + `verified`. `/dashboard/orders/{order}` is deliberately **not** registered until LEVEL 7 supplies an `Order` model to bind against.

Also present: `/dev/design-system` (LEVEL 1, non-production only) and the framework `/up` health check. Unknown routes return a branded 404. `php artisan route:list` reports **62 routes**.

No commented-out `require` remains in `routes/web.php`. LEVEL 5 chose to register the dashboard routes inside `routes/auth.php` (they are all authenticated screens, which is what that file is for) rather than in a separate `dashboard.php`, and the stale placeholder comment was removed rather than left as a trap.

## Environment Requirements
**Confirmed working:** PHP **8.4.12** (8.4.1 is the hard floor — `vendor/composer/platform_check.php` rejects 8.2/8.3), MySQL 8, Node 24, Composer 2. The CLI PHP used for verification lives at `C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe` and needs `extension=mbstring` enabled in its `php.ini`.

Node 20+ is now genuinely required — the marketing site will not render styled without `npm install && npm run build`.

PostgreSQL 16 and Redis 7 remain the documented production targets (`docker-compose.yml`); Redis is not required for local dev when `SESSION_DRIVER=database`.

## Integrations

None active. Payment gateway and provider adapter contracts exist only as documented interfaces in `ARCHITECTURE.md` — no concrete `PaystackGateway`/`FlutterwaveGateway`/named provider adapter classes exist yet (those are LEVEL 9 and LEVEL 10 respectively).

## Known Issues
- **✅ RESOLVED — "Alpine.js never initialises" was a FALSE DEFECT REPORT (closed).** The original note here claimed `window.Alpine` was `undefined` on every route and that the modal, mobile nav toggle, FAQ disclosure and dismissible alert did not work. That was an artefact of the QA harness: under the browser-automation driver, `<script>` elements in the page never execute, and every probe read its result back through that same script-inert document. **The user confirmed by clicking the FAQ disclosure in a real browser that it opens.** The bundle is valid, served correctly (HTTP 200, 55410 bytes, `application/javascript`) and executing its bytes by hand sets `window.Alpine` to an object. Full evidence, the falsified theories, the probe inventory and the docs that still need correcting are in **`storage/app/HANDOFF.md`** — read that before re-opening anything here. Do not bisect `resources/js/app.js`, `bootstrap/app.php`, or remove `data-navigate-track`; all three were proposed against a bug that does not exist.
- **✅ RESOLVED — PHP is now executable in this environment.** PHP 8.4.12 at `C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe`. `php artisan migrate`, `php artisan test`, `php artisan serve` and `php artisan tinker` have all been run successfully against real MySQL. **36 Pest tests pass, 69 assertions, 0 failures** (first execution of this codebase). The earlier "no PHP available" note below is retained only as history.
- **✅ RESOLVED — `config/session.php` threw on every request.** A literal `'connection' => 'session'` named a DB connection that does not exist, so with `SESSION_DRIVER=database` every page returned 500 `Database connection [session] not configured`. Now `env('SESSION_CONNECTION')` falling back to the default connection. Redis session pooling is unaffected. Fixed during LEVEL 2 integration after the archive's copy reintroduced the broken line.
- **LEVEL 1 style-guide sample copy needs correcting.** `resources/views/dev/design-system.blade.php` uses sample cards reading "Instagram Growth — 1,000–10,000 followers · 24–48h delivery" and "TikTok Engagement — Views & shares · Instant start". That describes a follower/engagement delivery panel, which master prompt §1 explicitly forbids. The sample data should be changed to legitimate service names (e.g. "Managed Ad Campaign — Instagram", "Creator Partnership — TikTok", "Release Campaign — Spotify"). **Not corrected automatically** — change it in place rather than replacing the file.
- **Contact enquiries are stored but no email notification is sent.** Deliberate: notifications are LEVEL 12, and dispatching mail from the controller now would violate ARCHITECTURE.md §2 and have to be unpicked. Submissions are durably persisted and logged, so nothing is lost. Until LEVEL 12, check `contact_messages` directly.
- **Legal pages are unreviewed drafts.** All five carry a visible draft notice. The highest-risk open item is whether Growza wallet balances constitute stored value under Nigerian financial regulation — that needs a professional answer before accepting live customer money.
- **`config/marketing.php` and `config/growza-marketing.php` both exist.** The archive shipped two competing content files; both are retained verbatim per the integration instruction. `growza-marketing` is the one the views actually read (`config('growza-marketing.faqs')`, company name in JSON-LD). Treat `marketing.php` as a candidate for removal once confirmed unused.
- **`ContactRequest` is dead code.** `ContactFormRequest` is the one wired into `ContactController`. Retained verbatim rather than pruned; safe to delete when convenient.
- **No PHP/Composer execution was available in the authoring environment (historical).** Superseded by the first bullet above.
- **`composer.json` now requires PHP >= 8.4.1** (`vendor/composer/platform_check.php` hard-fails on 8.2, which is what XAMPP ships). Confirmed 2026-02-14 with PHP 8.4.12. Anyone running the documented stack needs 8.4+, not the "PHP 8.3+" stated under Environment Requirements below.
- **⚠️ `config/session.php` MUST NOT be overwritten from a level archive.** The level-2, level-3 **and level-5** archives all ship a literal `'connection' => 'session'`, which names a DB connection that does not exist and makes every request 500 under `SESSION_DRIVER=database`. It has been reverted three times. If a future level archive contains this file, keep the repo's version. (Automated: `storage/app/apply-level5.ps1` lists it under `$repoWins`.)
- **✅ FIXED — `config/database.php` fell back to a non-existent MySQL user.** The `mysql` connection defaulted `username` to `growza`, but the actual local MySQL 8 is XAMPP's `root` with no password, and `.env` sets `DB_USERNAME=root`. `env('DB_USERNAME', 'growza')` therefore resolved fine, but any invocation where the env did not resolve (or an `.env` that omitted it) produced `Access denied for user 'growza'@'localhost'` — an environment failure that reads like a code defect. The empty-config fallback now follows the driver: `root` for MySQL, `growza` elsewhere. An explicit `DB_USERNAME` still wins.
- **⚠️ Running `php artisan test` destroys the local MySQL fixture data — and the failure mode is silent.** `phpunit.xml` pins `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:`, and `tests/Pest.php` applies `RefreshDatabase` to every Feature/Integration test. On one run during LEVEL 5 the suite wiped the real rows in the MySQL `growza` database (every user, including the `level5-verify@example.test` fixture the HTTP probes log in as). The probes then reported `login POST -> 302 to /login` and a cascade of 419s and "nothing persisted" failures that looked exactly like a broken dashboard. **Nothing was wrong with the app.** If an HTTP probe suddenly reports 419 or "invalid credentials", re-run `storage/app/verify-level5-mysql.php` to recreate the fixture before investigating the app. Worth confirming the phpunit env is genuinely overriding before trusting a green run against MySQL.
- **Local dev needs `MAIL_MAILER=log`.** The `.env` default (`smtp` on port 2525) assumes a Mailpit/Mailhog instance that is not running, so registration fails with a connection error when sending the verification email. Tests are unaffected (`phpunit.xml` sets `MAIL_MAILER=array`).
- **Registration needs HTTPS egress** for Laravel's `uncompromised()` breach check against HaveIBeenPwned. The CLI PHP at `C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\` had `curl.cainfo` and `openssl.cafile` unset, so every registration threw a cURL 60 error. Fixed by downloading `cacert.pem` into `storage/app/` and pointing `php.ini` at it. **Production on Linux needs none of this** (system CA store). If registration 500s after a fresh PHP install, this is why.
- **✅ PARTLY RESOLVED — `EnsureUserIsActive` now guards the dashboard.** LEVEL 5 put every one of the ten dashboard routes behind `['auth', 'active', 'verified']`, and `CustomerDashboardTest` proves a `status = suspended` user is ejected to `/login` on their very next request. It still is not on the marketing or admin groups; applying it to `/admin` belongs with LEVEL 16/17, when an admin can actually suspend someone.
- **Email verification is still enforced by `verified` on the dashboard, and the failure screen still has no resend button.** Fortify's `verification.send` route exists and the `verify-email` view links to it, but the dashboard itself never greets an unverified user (it redirects them away first). The expected LEVEL 5 delivery of a resend surface did not happen — record it against LEVEL 3's UI polish rather than claiming it here. No unverified user can reach the dashboard, so nothing is broken; it is simply a missing convenience.
- `config/permission.php` is included by hand (matching spatie/laravel-permission's published defaults, with `teams => true`) since `vendor:publish` cannot run here. Worth diffing against the package's actual published version after `composer install`, in case the installed package version's default config has shifted since this was written.

## Tests
**100 tests, 222 assertions — all passing** (`php artisan test`, PHP 8.4.12; suite runs on SQLite in-memory per `phpunit.xml`).

- `tests/Unit/MoneyTest.php` — LEVEL 5: minor-unit formatting (2 tests)
- `tests/Unit/CustomerDashboardMetricsServiceTest.php` — LEVEL 5: every metric zero (1 test)
- `tests/Feature/CustomerDashboardTest.php` — LEVEL 5: all 10 pages render for a verified Customer, guests are ejected, unverified users are sent to the notice, suspended users are ejected, metrics are genuinely zero, the referral code round-trips, the profile updates via Fortify's endpoint, and `/dashboard/orders/1` 404s (21 tests)

- `tests/Unit/ExampleTest.php`
- `tests/Unit/UserPolicyTest.php` — LEVEL 4: `UserPolicy` abilities (6 tests)
- `tests/Feature/HealthCheckTest.php` — `/up`
- `tests/Feature/HomepageTest.php`
- `tests/Feature/RbacTest.php` — LEVEL 4: seeded roles/permissions, per-role grants, Super Admin bypass, automatic Customer assignment, `/admin` middleware chain (11 tests)
- `tests/Feature/MarketingPagesTest.php` — every public page renders, plus tagline/

## Security Decisions
- Public contact POST is rate limited (`throttle:5,10`) and honeypot-guarded; CSRF applies via the standard `web` group.
- `robots.txt` and the `noindex` meta tag both refuse indexing in any non-production environment, so a staging deployment cannot leak into search results.
- Legal pages ship with visible draft notices rather than presenting unreviewed text as binding.
- `$fillable` (not `$guarded = []`) will be enforced on every model going forward, starting with `User`.
- Sessions, cache, and queue each use a **separate Redis logical database** so a cache flush can never touch queued jobs or active sessions.
- `APP_ALLOW_INDEXING` env flag exists now so LEVEL 31 has a single switch rather than per-route logic to keep dashboard/admin routes out of search indexes.

## Deployment Status

Not deployed anywhere. Docker Compose stack is for local development only — see `docker-compose.yml` comments. Production deployment steps belong in `DEPLOYMENT.md` (stub only at this level; fully written at LEVEL 33).

---

## ✅ LEVEL 0 Verification (confirmed by user after real deployment)

1. `composer install` — resolved successfully.
2. Migrations — ran successfully against real PostgreSQL.
3. `docker compose up` — full stack (app, nginx, postgres, redis, queue worker, scheduler) came up healthy.
4. `php artisan key:generate` — succeeded.
5. Storage permissions — required a manual fix on first boot (expected on some host/Docker UID setups; not a code defect, but worth a one-line note in DEPLOYMENT.md when that level is written: `chown -R www-data:www-data storage bootstrap/cache` if `APP_KEY`/log-write errors appear on first boot).
6. `http://localhost:8000` — responds and correctly renders the "Growza — Grow Smarter. Reach Further." placeholder homepage.

Pest test execution (`php artisan test`) was not explicitly reported — confirm this separately when convenient; not a blocker for starting LEVEL 1.

**LEVEL 0 is CLOSED.**
