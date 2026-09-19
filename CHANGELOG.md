# CHANGELOG
All notable changes to Growza are recorded here, newest first. Format loosely follows Keep a Changelog; versions track `PROJECT_STATE.md`.

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
