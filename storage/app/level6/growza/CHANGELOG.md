# CHANGELOG

All notable changes to Growza are recorded here, newest first. Format loosely follows Keep a Changelog; versions track `PROJECT_STATE.md`.

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

## [0.3.0-foundation] — LEVEL 2: Public Marketing Website

### Added
- Marketing pages: home, services, pricing, how-it-works, why-growza, faq, contact
- Legal pages: terms, privacy, refund policy, acceptable use, cookie policy — all rendered as drafts pending legal review
- `config/growza-marketing.php` holding all marketing content as data (LEVEL 6 swaps the services key for a DB query without template changes)
- Functional contact form: FormRequest validation, honeypot, route throttling, `contact_messages` table, `StoreContactMessage` domain action
- SEO: `<x-seo>` component (canonical, OG, Twitter), Organization + FAQPage structured data, generated `sitemap.xml`, environment-aware `robots.txt`
- Components: page-header, section-heading, cta-band, faq-item, footer, legal-page, legal-review-notice, marketing layout

### Fixed
- Created the missing base `App\Http\Controllers\Controller` class (absent from Laravel 11's slim skeleton, never added at LEVEL 0)
- Replaced closure-based legal routes that would have broken `php artisan route:cache`
- Relaxed `email:rfc,dns` to `email:rfc` (live MX lookup rejected valid addresses and broke tests)
- Cleared rate-limiter state between tests to prevent spurious 429s
- Replaced `route('register')`/`route('login')` with `url()` so pages render before LEVEL 3 exists

### Deliberately omitted
- Testimonials ship empty; inventing customer quotes for a live site is deceptive advertising
- Liability, governing law, and regulatory sections of the legal pages are left as REVIEW NOTE markers for counsel rather than fabricated

### Still unverified
- No PHP available in the authoring environment: migration, the 22 new tests, and browser rendering all need running locally

## [0.4.0-foundation] — LEVEL 3: Authentication

### Added
- Laravel Fortify wired headless to Growza's own `App\Domain\Auth\Actions`
- Registration (with phone + referral code + terms acceptance), login, logout, email verification, forgot/reset/change password
- Optional TOTP two-factor authentication with QR enrolment, confirmation step and recovery codes
- Security notifications: `PasswordChangedNotification`, `NewDeviceLoginNotification` (mail channel; LEVEL 12 adds database channel and preferences)
- `AuditLogger` service + `AuditLog` model writing to the `audit_logs` table created at LEVEL 0, with secret redaction
- `EnsureUserIsActive` middleware (`active` alias) enforcing `users.status` suspension per request
- Auth views (login, register, forgot/reset password, verify email, confirm password, 2FA challenge), security settings screen, placeholder `/dashboard`
- `users.referred_by_code` column — recorded at registration, resolved by LEVEL 14

### Fixed
- Corrected password-confirmation form target: Fortify's POST endpoint is `password.confirm.store`, not the GET-only `password.confirm` (would have been a 405)
- Restored `route('login')`/`route('register')` in marketing views now that those routes exist

### Security decisions
- Login throttled on email+IP together plus a wider per-IP limit, so throttling cannot be weaponised to lock a user out of their own account
- Passwords: 10+ characters, checked against known-breach corpus
- 2FA requires confirmation before activation; a secret alone does not enable it
- Passkeys intentionally left disabled pending a designed credential-recovery story

### Still unverified
- No PHP in the authoring environment: migration and the 22 new tests need running locally
- Mail must be configured before reset/verification emails will send

## [0.5.0-foundation] — LEVEL 4: User & Role System (RBAC)

### Added
- `RoleName`/`PermissionName` backed enums for the 9 roles and 11 permissions
- `RoleAndPermissionSeeder` (idempotent) with per-role permission grants
- `UserPolicy` — the first real Policy, plus `Gate::before` Super Admin bypass in `AuthServiceProvider`
- Automatic `Customer` role assignment at registration
- Minimal permission-gated `/admin` placeholder proving the full middleware chain end to end
- `DatabaseSeeder` (was missing entirely, like the base `Controller` class was at LEVEL 2)
- `tests/Pest.php` now seeds roles/permissions before every Feature/Integration test

### Verified
- Confirmed via package source (not assumed) that spatie registers permissions as Gate abilities automatically, and that team_id defaults to null

### Corrected during this level (not a shipped bug — caught before delivery)
- Briefly believed `tests/TestCase.php` needed a `createApplication()` override; verified against Laravel's actual base class, found the belief was wrong, and reverted rather than ship an unnecessary change

### Still unverified
- No PHP in the authoring environment: seeder run and 19 new/updated tests need executing locally

## [0.6.0-foundation] — LEVEL 5: Customer Dashboard

### Added
- Dashboard layout: desktop sidebar + off-canvas mobile drawer (genuinely different pattern, not a shrunk sidebar)
- All 10 dashboard routes/pages: home, profile, services, orders, wallet, transactions, referrals, support, notifications, settings
- `CustomerDashboardMetricsService` + `CustomerDashboardMetrics` DTO — the seam LEVEL 7/8/14 fill in later
- `App\Support\Money` currency formatter
- Functional profile edit page wired to the existing Fortify `user-profile-information.update` endpoint

### Fixed
- `x-dropdown-item as="button"` hardcoded `type="button"`, which would have silently no-op'd the logout form submit — added a `type` prop
- `<x-card as="a" href="...">` doesn't exist as a pattern; rewritten as a real `<a>` wrapper
- Cleaned a leftover dead comment in `routes/web.php`

### Deliberately omitted
- `/dashboard/orders/{order}` — no Order model exists until LEVEL 7; a route with nothing real behind it would be a fake button

### Still unverified
- No PHP in the authoring environment: 32 new tests need running locally; no browser check of the drawer/sidebar breakpoint switch

## [0.7.0-foundation] — LEVEL 6: Service Catalogue

### Added
- `platforms`, `service_categories`, `services`, `service_price_tiers` tables and matching models in `App\Domain\Catalogue`
- `CatalogueSeeder` — idempotent, 9 real platforms, 10 real services across 6 categories
- Public service detail pages at `/services/{slug}` with real pricing (fixed or budget-range) and delivery estimates
- Dynamic sitemap entries for every active service

### Changed
- `config('growza-marketing.services')` and `.platforms` removed — superseded by the database
- `MarketingPageController` now queries Eloquent; homepage, services listing, and dashboard services page updated from array to object access accordingly

### Deliberately omitted
- No quantity-based pricing (follower/like/stream counts) — ruled out structurally by the Acceptable Use Policy, not an oversight
- No admin CRUD for the catalogue — that's LEVEL 16's job

### Still unverified
- No PHP in the authoring environment: migrations and 21 new tests need running locally
