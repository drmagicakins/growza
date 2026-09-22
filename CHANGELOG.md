# CHANGELOG
All notable changes to Growza are recorded here, newest first. Format loosely follows Keep a Changelog; versions track `PROJECT_STATE.md`.

## [0.7.0-foundation] — LEVEL 6: Service Catalogue

### Added
- **The database-driven service catalogue**, replacing `config('growza-marketing.services')` / `.platforms` as the source both the marketing site and the dashboard services page read from — the explicit LEVEL 6 requirement ("do not hard-code services into frontend code").
- 4 tables via migration: `platforms`, `service_categories`, `services`, `service_price_tiers`.
- `App\Domain\Catalogue` — `Platform`, `ServiceCategory`, `Service`, `ServicePriceTier` models plus a `PricingModel` backed enum.
- `CatalogueSeeder` — 9 platforms, 6 categories, 10 services, 7 retail price tiers; idempotent via `updateOrCreate` throughout.
- Public service detail pages at `/services/{service:slug}` (`services.show`), with real pricing, delivery estimates and requirements.
- `resources/views/marketing/service-detail.blade.php`.
- `tests/Feature/CatalogueTest.php` (12 tests), `tests/Feature/DashboardCatalogueTest.php` (1), `tests/Unit/ServiceModelTest.php` (5).
- `storage/app/probe-level6-diff.ps1`, `storage/app/probe-level6-detail.ps1`, `storage/app/apply-level6.ps1`, `storage/app/qa-level6-http.mjs` — the re-runnable integration + verification tooling.

### Changed
- `routes/marketing.php` — adds the `services.show` route.
- `MarketingPageController` — queries Eloquent instead of reading config; adds `serviceShow()`; 404s an inactive service.
- `DashboardController@services` — passes the real catalogue to the view.
- `SitemapController` — appends active service URLs from the database, so a new or deactivated service cannot leave a stale sitemap.
- `resources/views/marketing/{home,services}.blade.php` and `dashboard/services.blade.php` — services convert from array access (`$service['name']`) to object access (`$service->name`). This was flagged at LEVEL 5 as the anticipated, minimal-footprint change.
- `config/growza-marketing.php` — the `services` and `platforms` keys are **removed** (verified absent via `config()->has()`).

### Fixed
- **The LEVEL 6 archive shipped a file with a fatal PHP parse error, and it would have taken down the whole app.** `config/growza-marketing.php` in the archive had the `'audiences' => [` opening line deleted (and a duplicated `/*`) while the `services`/`platforms` keys were stripped — leaving six orphaned array entries. Loading it produced `syntax error, unexpected token ",", expecting ";"`, which broke `php artisan config:clear`, every route, and the homepage. The homepage view still reads `config('growza-marketing.audiences')`, so this was not latent. Repaired in the repo's own copy; `config/growza-marketing.php` is now classified `repoWins` so the apply script cannot reintroduce it.
- **`apply-level6.ps1` was not idempotent for a repo-side fix.** Its first re-run re-copied the archive's broken config over the repaired one (the exact hazard `HANDOFF-LEVEL5.md` §1 warns about). Fixed by moving the file into `$repoWins`; a re-run now reports `ADDED: 0 / ARCHIVE WON: 0`.
- **Two LEVEL 6 tests asserted the wrong thing about entity encoding.** `CatalogueTest` and `DashboardCatalogueTest` used `assertSee('Instagram & Facebook Ad Campaign', escape: false)` — but Blade escapes `&` to `&amp;`, and `escape: false` searches the needle raw, so the assertion could never match a correctly rendered page. Removed `escape: false` on those two assertions (Pest then escapes the needle to match the served bytes). This is the same measuring-instrument class of mistake as `HANDOFF-LEVEL5.md` §2a — **the app was correct; the probe was wrong.**

### Verified
- `php artisan migrate --force` — all 4 catalogue migrations applied against **real MySQL 8**.
- `php artisan db:seed --class=CatalogueSeeder` — seeded 9 platforms / 6 categories / 10 services / 7 tiers, read back from MySQL. Re-running left every count unchanged, proving the seeder is genuinely idempotent rather than assumed to be.
- `php artisan test` — **118 passed, 268 assertions, 0 failures** (LEVEL 5's 100 + LEVEL 6's 18).
- `vendor/bin/pint --test` passes on all 18 LEVEL 6 files.
- `storage/app/qa-level6-http.mjs` — **PASS, exit 0, 17 checks, zero issues.** Over raw HTTP: `/` and `/services` 200; `SoundCloud` present on the homepage (it exists only in the `platforms` table, so this proves the DB is the source, not config); all 6 categories render; the listing links exactly 10 detail pages; **all 10 render 200**; both pricing shapes present (`one-time fee`, and the budget-range copy); `/services/not-a-real-service` returns exactly **404**; `sitemap.xml` contains service URLs; `/dashboard/services` still 302s an anonymous visitor.
- `vite build` — succeeded; CSS 46.72 kB → 46.74 kB (no new utility classes, as expected).
- `php artisan route:list` — **62 routes**, with `services.show` registered and confirmed present in the live route collection (`in_array('services.show', ...)` → YES).

### Still unverified
- **No visual browser pass** at any of the LEVEL 58 widths — the same standing gap as LEVELS 1/5. The catalogue markup is asserted structurally over HTTP, not seen.
- **No admin CRUD for the catalogue.** Deliberately not built: `manage_services` (LEVEL 4's permission) stays unused until LEVEL 16 gives someone a UI. Building it now would be building ahead of its level.
- No quantity-based pricing column exists, by design — see the `services` migration docblock.

## [0.6.0-foundation] — LEVEL 5: Customer Dashboard
### Added
- **The real customer dashboard**, replacing the LEVEL 3 placeholder. `resources/views/dashboard/placeholder.blade.php` (which read "the full dashboard is not built yet") is deleted — nothing referenced it once these routes landed.
- `app/Http/Controllers/Web/Dashboard/DashboardController.php` — all ten authenticated screens in one controller, deliberately: every method does the same job (resolve what exists today, hand it to a view) and there is no business logic to split out beyond what the metrics service already provides.
- All 10 routes from the spec — `dashboard`, `dashboard.profile`, `.services`, `.orders`, `.wallet`, `.transactions`, `.referrals`, `.support`, `.notifications`, `.settings` — registered in `routes/auth.php` behind `auth` + `active` + `verified`.
- `app/Domain/Reporting/Services/CustomerDashboardMetricsService.php` + `DTOs/CustomerDashboardMetrics.php` — the seam LEVEL 7 (orders), LEVEL 8 (wallet) and LEVEL 14 (referrals) fill in with real queries without the view, controller or route changing. Every zero is commented with the exact query that will replace it.
- `app/Support/Money.php` — minor-unit (kobo) formatter. Presentation only: no arithmetic, no persistence, no business rules; LEVEL 8's WalletService owns real money logic.
- `resources/views/components/layouts/dashboard.blade.php` — desktop sidebar **plus a genuinely distinct mobile pattern**: a full-height off-canvas drawer with larger touch targets (`py-3.5` vs `py-2.5`) and a backdrop, not the sidebar shrunk. Both render from one `$navItems` array so they cannot drift.
- `resources/views/components/dashboard-nav-link.blade.php`, `resources/views/dashboard/*` (10 views).
- `tests/Feature/CustomerDashboardTest.php` (21 tests), `tests/Unit/CustomerDashboardMetricsServiceTest.php`, `tests/Unit/MoneyTest.php`.
- `storage/app/verify-level5-mysql.php`, `storage/app/qa-level5-http.mjs`, `storage/app/qa-level5-profile-http.mjs` — the re-runnable verification described under Verified below.

### Fixed
- **`x-dropdown-item` hardcoded `type="button"`.** Wrapping it in the logout `<form>` made the button a silent no-op — it would never submit. Fixed in the component (added a `type` prop) rather than worked around at one call site, since any future use inside a form hits the same trap.
- **`<x-card as="a" href="...">` does not work** — the card only ever renders a `<div>` and has no `as`/`href` prop, so the Settings hub's first tile would have looked clickable and done nothing. Rewritten to wrap the card in a real `<a>`.
- **`config/database.php` MySQL fallback named a non-existent user.** `username` defaulted to `growza` while the actual local MySQL 8 is XAMPP's passwordless `root`; the empty-config fallback now follows the driver (`root` for MySQL, `growza` elsewhere) and an explicit `DB_USERNAME` still wins. The failure read as `Access denied for user 'growza'@'localhost'` — an environment error that looks like a code defect.
- `config/session.php` protected again (the archive shipped the 500-causing `'connection' => 'session'` for the third level running).

### Verified
- `php artisan test` — **100 passed, 222 assertions, 0 failures** (76 existing + 24 new)
- `storage/app/verify-level5-mysql.php` against **real MySQL 8** (not SQLite): builds a real Customer through the registration path, then renders all 10 screens via the real controller — every one returns 11–18 kB of real HTML with the sidebar present, zero `Route [...] not defined` / `Undefined variable` / `ViewException`, `noindex, nofollow` on every private page, and `assignRole()` still writing `team_id = NULL` and reading back `true` (the LEVEL 4 pivot fix holds)
- `storage/app/qa-level5-http.mjs` — **PASS, exit 0.** Raw HTTP + cookie jar: all 10 dashboard URLs 302 an anonymous visitor to `/login`; real login 302s to `/dashboard`; all 10 pages 200 with every expected string; `/dashboard/orders/1` returns exactly **404** (not 500); both Settings tiles resolve to real URLs; marketing homepage unaffected; logout closes the dashboard again
- `storage/app/qa-level5-profile-http.mjs` — **PASS, exit 0.** The profile write driven as a browser does it: GET the form (posts to `.../user/profile-information`, spoofed `_method=PUT`, scoped CSRF token), PUT new values, re-GET and confirm the new name/phone render back, the success banner appears, the old name is gone, and the dashboard greets the new name. A duplicate phone is correctly rejected. Restores the fixture, so it is re-runnable
- `vite build` — CSS **46.17 kB → 46.72 kB**, matching the level's own claim that the dashboard utilities compiled; `public/build/manifest.json` and the served `<link>`/`<script>` tags agree on `app-DLubIMpg.css` (build is not stale)
- `vendor/bin/pint --test` passes on all LEVEL 5 files (one fix: multi-line empty constructor body)

### Still unverified
- No visual browser pass. The drawer's Alpine transitions and the sidebar↔drawer switch at `md` are asserted structurally but have not been seen at any of the LEVEL 58 widths. The drawer also has no focus trap — the same pre-existing gap LEVEL 29 owns.
- `/dashboard/orders/{order}` is deliberately **not** registered: no `Order` model exists until LEVEL 7, and a route bound to nothing would be the "button that does nothing" the project rules forbid. A test asserts it 404s.

## [0.5.0-foundation] — LEVEL 4: User & Role System (RBAC)
### Added
- `App\Domain\Identity\Enums\RoleName` / `PermissionName` — the exact 9 roles and 11 permissions named in the master prompt, backed enums so the value IS the string spatie stores (no mapping to drift)
- `RoleAndPermissionSeeder` — idempotent (`firstOrCreate` + `syncPermissions`); seeds the 9 roles and 11 permissions and grants each role a deliberately scoped permission set. Wired into `DatabaseSeeder`
- `App\Domain\Identity\Policies\UserPolicy` — the first real Policy, registering the pattern every later domain follows
- `Gate::before` Super Admin bypass in `AuthServiceProvider`, so "Super Admin means everything" is central rather than repeated in every future Policy
- Automatic `Customer` role assignment at registration (`CreateNewUser`), so self-registration can never reach admin authority
- Permission-gated `/admin` placeholder route (`auth` → `active` → `verified` → `permission:view_users`) proving the full chain admits and rejects
- `tests/Feature/RbacTest.php` (11 tests) + `tests/Unit/UserPolicyTest.php` (6 tests); `tests/Pest.php` now seeds roles/permissions before Feature/Integration tests so LEVEL 3 registration tests keep passing
- `database/seeders/DatabaseSeeder.php` (was missing entirely)

### Fixed
- **RBAC pivot `team_id` could not hold NULL, so every role assignment failed.** Migration `2026_01_03_000_make_rbac_pivot_team_id_nullable`. The LEVEL 0 stub (spatie's stub, verbatim) made `model_has_roles.team_id` / `model_has_permissions.team_id` NOT NULL *and* the first column of a composite PRIMARY KEY. With no team context (`getPermissionsTeamId()` returns null here until LEVEL 21), `assignRole()` inserts `team_id = NULL` and was rejected — killing registration and all 17 RBAC tests.
  - The naive `->change()` fix turned out to be a **silent no-op on MySQL**: InnoDB forces every PRIMARY KEY column to be NOT NULL, so `ALTER ... MODIFY team_id NULL` is accepted, the migration reports DONE, and `information_schema` still reads `NO`. Verified on MySQL 8 by reading `IS_NULLABLE` back after the statement.
  - The shipped fix **drops the primary key and replaces it with an equivalent UNIQUE index** over the same four columns — a UNIQUE index permits NULLs on both MySQL and SQLite while still preventing duplicate grant rows — makes `team_id` nullable, and drops/recreates the two pivot FKs around the key rewrite. Fully reversible.
  - **False green caught:** the Pest suite runs on SQLite in-memory, which *does* allow NULLs in a composite PK — so the 11 RBAC tests were passing while the MySQL schema the app runs against was broken. The fix is verified against MySQL directly (real `assignRole()` writing `team_id = null`, then `hasRole()` reading back `true`), not just via the suite.

### Verified
- `php artisan migrate:fresh --seed --force` — all **11 migrations + seeder** run clean on MySQL 8
- `php artisan test` — **76 passed, 181 assertions, 0 failures** (LEVEL 3's 59 + LEVEL 4's 17)
- `assignRole('Customer')` against the real MySQL DB writes a pivot row with `team_id = null`; `hasRole('Customer')` then reads `true` — the original `SQLSTATE[23000]` is gone
- Migration rolled back and re-applied cleanly; after rollback the pivots returned to NOT NULL + composite PK, after re-apply to nullable + unique index
- `vendor/bin/pint --test` passes on the migration
### Still unverified
- No browser render of the `/admin` placeholder (the QA driver cannot execute document script — see `storage/app/HANDOFF.md`); the middleware chain is proven by tests, not by a click

## [0.4.0-foundation] — LEVEL 3: Authentication
### Added
- **Fortify-backed authentication** (headless): registration, login, logout, email verification, password reset, password confirmation, and TOTP two-factor with recovery codes
- `routes/auth.php` — the screens Growza owns (`dashboard`, `settings.security`); Fortify registers the endpoints themselves
- `app/Domain/Auth/Actions/` — `CreateNewUser`, `UpdateUserProfileInformation`, `UpdateUserPassword`, `ResetUserPassword`: validation and business logic kept out of controllers and HTTP-testable on their own
- `app/Domain/Auth/Events/` + `Listeners/` — `HandleSuccessfulLogin`, `HandleFailedLogin` write audit records; suspension and verification notifications
- `config/fortify.php` (features enabled incl. 2FA with confirm + confirmPassword), `config/auth.php`
- `App\Providers\FortifyServiceProvider` — binds the domain actions and Blade views, and registers the `login` / `two-factor` rate limiters
- `App\Http\Middleware\EnsureUserIsActive` (alias `active`) — enforces suspension on the next request rather than at session expiry
- Auth Blade views under `resources/views/auth/` (login, register, forgot-password, reset-password, verify-email, confirm-password, two-factor-challenge) plus `layouts/auth`
- `dashboard` and `settings/security` views
- Migration `add_referral_code_to_users_table` — unique nullable `referral_code`, with backfill for pre-existing rows
- `tests/Feature/AuthenticationTest.php` — 20 tests covering register, login, throttle, verification gate, suspension, password reset and 2FA surface
### Fixed
- **`config/auth.php` did not exist.** Laravel 11's slim skeleton omits it, so the framework fell back to `App\Models\User` — a class this project deliberately does not have (the model lives in `App\Domain\Identity\Models`). Every guarded request and all five auth tests died with `Class "App\Models\User" not found`. The new config points the `users` provider at the domain model, and keeps guard name `web` / broker `users` so Fortify's `config('fortify.guard')` resolves.
- **`bootstrap/app.php` was missing the `active` middleware alias**, so any route using it threw `Target class [active] does not exist`.
- **`bootstrap/providers.php` was not registering `FortifyServiceProvider`**, so Fortify's actions, views and rate limiters were never bound.
- **`routes/web.php` still had `require auth.php` commented out**, so `dashboard` and `settings.security` were never registered.
- **The throttle test asserted the wrong mechanism.** Because `config('fortify.limiters.login')` names a limiter, Fortify deliberately skips its own `EnsureLoginIsNotThrottled` pipe and the throttle is enforced by Laravel's `ThrottleRequests` middleware, which returns a bare **429** and flashes no "try again in :seconds seconds" message. The assertion could never pass. It now asserts the 429 itself. **The app was already correct** — verified by driving the 6th attempt to a real 429. Added a second test proving the email+IP key scoping works, since that is what stops an attacker locking a known user out of their own account.

### Changed
- `config/session.php` — **the archive reintroduced the bug and it was reverted again.** See the LEVEL 2 entry; this file must never be overwritten from a level archive.

### Verified
- `php artisan test` — **59 passed, 137 assertions, 0 failures.**
- `php artisan migrate --force` — `add_referral_code_to_users_table` applied cleanly against real MySQL.
- `php artisan route:list` — **51 routes**, including all Fortify endpoints
- Auth flows driven over real HTTP (independent of the QA driver's script-inertness — see `storage/app/HANDOFF.md`):
  - anonymous `GET /dashboard` → **302 → `/login`**
  - `POST /register` (valid) → **302 → `/dashboard`**, and the row persisted: `status active`, bcrypt password, `email_verified_at` null, `referral_code` generated
  - `POST /login` (correct) → **302 → `/dashboard`**
  - unverified user `GET /dashboard` → **302 → `/email/verify`** (the `verified` middleware working)
  - `GET /settings/security` → **200**, 2FA markup present
  - `POST /logout` → **302 → `/`**, and `GET /dashboard` afterwards → **302 → `/login`** (session genuinely dead)
  - `POST /login` (wrong password) → rejected, **302 → `/login`**
  - `POST /login` ×6 → 6th returns **429**
- Audit trail confirmed populated in the database: `auth.login`, `auth.login_failed`, `user.registered`, each with actor id and IP.

### Environment notes (not code defects)
- Registration requires **HTTPS egress** for Laravel's `uncompromised()` breach check against HaveIBeenPwned. This machine's CLI PHP had `curl.cainfo` and `openssl.cafile` unset, so every registration threw a cURL 60 certificate error. Fixed by downloading `cacert.pem` to `storage/app/` and point `php.ini` at it. **Production on Linux has a system CA store and needs none of this.**
- `MAIL_MAILER` must be `log` locally; the `.env` default (`smtp` on port 2525) assumes a Mailpit/Mailhog instance that is not running, so registration fails on sending the verification email. Tests are unaffected — `phpunit.xml` sets `MAIL_MAILER=array`.

## [0.3.0-foundation] — LEVEL 2: Public Marketing Website
### Added
- Eleven public routes: `/`, `/services`, `/pricing`, `/how-it-works`, `/why-growza`, `/faq`, `/contact`, plus five legal pages under `/legal/*` (`terms`, `privacy`, `refund-policy`, `acceptable-use`, `cookie-policy`)
- `routes/marketing.php`, required from `web.php`
- `MarketingPageController`, `ContactController`, `SitemapController` (`app/Http/Controllers/Web/`)
- `app/Http/Controllers/Controller.php` — base controller with `AuthorizesRequests`; Laravel 11's slim skeleton omits it and LEVEL 0 never created it
- `config/marketing.php` and `config/growza-marketing.php` — all marketing content (services, platforms, pricing tiers, FAQs, process) kept out of Blade
- Working contact form: `ContactFormRequest` (server-side validation + honeypot), `ContactMessage` model, `StoreContactMessage` action, `contact_messages` migration, rate-limited POST route
- `sitemap.xml` and `robots.txt`, generated (not static) so robots can refuse indexing outside production
- `x-seo-meta` / `x-seo` components: title, description, canonical, robots, Open Graph, Twitter/X, JSON-LD
- Organization structured data, and FAQPage structured data on `/faq`
- `x-site-header` (active nav states, mobile menu), `x-footer`, `x-layouts.marketing` (with skip link)
- Branded 404 / 500 / 503 error pages
- Four legal pages, each carrying a visible "pending legal review" notice
- 33 new feature tests across marketing pages, contact form and SEO
### Changed
- `routes/web.php` — now requires `marketing.php`; the inline `/` closure moved to `MarketingPageController@home`
- `resources/views/marketing/home.blade.php` — LEVEL 0 placeholder replaced with the real homepage
- `config/session.php` — **bug fix carried forward.** The file had a literal `'connection' => 'session'`, which made every request throw `Database connection [session] not configured` when `SESSION_DRIVER=database`. It now reads `env('SESSION_CONNECTION')` and falls back to the default DB connection. Redis session pooling is unaffected (that path uses the store name, not the DB connection).

### Verified
- **PHP 8.4.12, MySQL, real HTTP via `php artisan serve` on 127.0.0.1:8125. First execution of this codebase.**
- `php artisan migrate --force` — `create_contact_messages_table` ran clean (73.68ms). Schema confirmed: 12 columns incl. `status` default `'new'`, three indexes.
- `php artisan test` — **36 passed, 69 assertions, 0 failures.** All six test classes pass (`Unit\ExampleTest`, `ContactFormTest`, `HealthCheckTest`, `HomepageTest`, `MarketingPagesTest`, `SeoTest`).
- All 16 routes return HTTP 200 (12 marketing/legal + `sitemap.xml` + `robots.txt`, and the `/dev/design-system` and `/up` regressions). Unknown routes return a branded 404.
- Contact form round-trip driven in a real browser: filled and submitted the live form → redirected to `/contact` with the flash message → row persisted. Verified in the database: `id 1`, `status 'new'`, `subjectLabel()` resolved to "Starting a campaign", IP captured, 169-char body.
- `npm run build` — CSS 37.65 kB → **44.02 kB**, confirming the new view classes compiled into the bundle.
- Every `route()` name referenced in a view was cross-checked against the route table — no broken references.
- Every `<x-...>` component tag was cross-checked against `resources/views/components/` — all resolve.

### Still unverified
- Responsive breakpoints have not been visually reviewed at mobile/tablet widths.
- Alpine-driven interactivity (FAQ disclosure, mobile menu, modal) is verified by the test-suite assertion that the markup renders, **not** by observing the interaction — see `storage/app/HANDOFF.md` for why the QA driver cannot execute document script.

## [0.1.0-foundation] — LEVEL 0

### Added
- Laravel 11 project skeleton (composer.json, bootstrap/app.php, bootstrap/providers.php)
- Domain-oriented folder structure (`app/Domain/*`) for all 16 bounded contexts
- Framework-baseline migrations: users, sessions, cache, jobs/failed_jobs, personal_access_tokens
- RBAC schema via spatie/laravel-permission (team-scoped)
- `teams`/`team_user`, `audit_logs`, `settings` tables (scaffolded ahead of their feature levels per the v1.0→v2.0 dependency map)
- `User` model in `App\Domain\Identity\Models`
- Six domain service providers (empty, registered)
- Permission/role/API-scope middleware aliases (functional, unseeded)
- Route skeleton (web/api/admin/console)
- `.env.example` with every required variable
- Docker Compose dev stack + production Supervisor unit
- Pest test setup with 3 baseline tests
- `PROJECT_STATE.md`, `ARCHITECTURE.md`, `DATABASE.md`

### Verified
- Nothing yet — see PROJECT_STATE.md "Unverified Items". No PHP/Composer execution was available in the authoring environment.

## [0.1.0-foundation] — LEVEL 0 verified

### Verified
- Full Docker Compose stack confirmed running: Laravel app, Nginx, PostgreSQL, Redis, queue worker, scheduler.
- `composer install`, `php artisan key:generate`, and migrations completed successfully against real Postgres.
- Homepage at `http://localhost:8000` renders correctly.
- Note for DEPLOYMENT.md (LEVEL 33): storage permissions needed a manual `chown` on first boot in this environment — worth a documented first-boot step.

## [0.2.0-foundation] — LEVEL 1: Brand & Design System

### Added
- Vite + Tailwind CSS + Alpine.js frontend pipeline
- Custom design tokens: `ink`/`ember` color scales, Fraunces/Public Sans typography, restrained radius/shadow scales (`tailwind.config.js`)
- Blade component library: button, input, textarea, select, card, badge, alert, modal, dropdown, marketing nav bar, breadcrumbs, pagination view, empty state, loading state
- Live style guide at `/dev/design-system` (non-production only)
- `DESIGN_SYSTEM.md` with full rationale and an explicit Known Gaps section

### Verified
- `npm install` and `npx vite build` both succeeded for real in the authoring environment.
- Found and fixed a genuine Tailwind JIT bug (dynamic class interpolation in the style guide's color loop) via a `safelist` entry, then confirmed the fix by grepping compiled CSS output for the specific classes.

### Still unverified
- Blade rendering, Alpine interactivity, and responsive breakpoints — no PHP/browser available in the authoring environment. Needs a real `php artisan serve` + browser check.
