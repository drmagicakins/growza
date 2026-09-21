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

`0.5.0-foundation` (pre-1.0 — LEVEL 4 delivered)

## Completed Levels

| Level | Name | Status | Verified? |
|---|---|---|---|
| — | Master Architecture Specification | ✅ Complete | Reviewed with stakeholder |
| 0 | Project Foundation | ✅ Complete | ✅ Verified — confirmed running via Docker Compose (Laravel, Nginx, PostgreSQL, Redis, queue worker, scheduler all up; `/` serves Growza homepage) |
| 1 | Brand & Design System | ✅ Complete | 🟡 Partially verified — Vite/Tailwind build verified; browser render not |
| 2 | Public Marketing Website | ✅ Complete | 🟡 Partially verified — see LEVEL 2 section |
| 3 | Authentication | ✅ Complete | 🟡 Partially verified — see LEVEL 3 section |
| 4 | User & Role System (RBAC) | ✅ Complete | 🟡 Partially verified — see LEVEL 4 section |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: LEVEL 5 (Customer Dashboard)** — replace the LEVEL 3
placeholder `/dashboard` with the real thing (wallet balance, order counts,
referral earnings). Note LEVEL 5's metrics reference the wallet (LEVEL 8) and
orders (LEVEL 7), which do not exist yet — the dashboard will need to render
sensible zero-states for those until Batch B/C land, per ARCHITECTURE.md §21's
batch ordering.

## LEVEL 4 — User & Role System / RBAC (this delivery)

### Delivered
- `RoleName` and `PermissionName` backed enums — the exact 9 roles and 11
  permissions named in the master prompt, as the canonical source of the
  string values (no separate mapping to keep in sync)
- `RoleAndPermissionSeeder` (idempotent — `firstOrCreate`/`syncPermissions`
  throughout) granting each role a deliberately scoped permission set
- `UserPolicy` — the first real Policy in the codebase, establishing the
  pattern every later domain follows
- `Gate::before` Super Admin bypass in `AuthServiceProvider`, so "Super Admin
  means everything" doesn't depend on every future Policy remembering to
  special-case it
- Every self-registered user is assigned the `Customer` role automatically;
  nothing above it is reachable through the public registration form
- A minimal permission-gated `/admin` placeholder proving the full chain
  (`auth` → `active` → `verified` → `permission:view_users`) actually admits
  and rejects correctly — not just that the seeder ran

### Deliberate scope limits
- **Only `UserPolicy` exists.** `OrderPolicy`, `SupportTicketPolicy`, etc. are
  written by the level that introduces their model — writing them now against
  models that don't exist would be fabricated authorization logic with nothing
  real to authorize.
- **`Customer` and `Reseller` hold no admin-facing permissions.** A customer's
  access to their own orders/wallet is an ownership check in that domain's own
  Policy (`$actor->is($order->user)`), not a blanket permission — this is why
  the RBAC test asserts a Customer has *zero* of the eleven permissions.
- **Team-scoped permissions (Agencies, LEVEL 21-22) are untouched.** The
  `roles`/`permissions` tables were created team-aware at LEVEL 0, but every
  role seeded here has `team_id = null` (spatie's default when
  `setPermissionsTeamId()` is never called) — confirmed by reading
  `DefaultTeamResolver` in the installed package rather than assumed. LEVEL 21
  introduces the middleware that sets a team context; nothing before it needs to.

### A correction made during this level, not a bug found
While wiring RBAC's Policy tests I initially "fixed" `tests/TestCase.php` by
adding an explicit `createApplication()` override, believing the empty class
was broken. It was not: Laravel 11's own base `TestCase` already implements
`createApplication()` correctly via `Application::inferBasePath()`. I verified
this by reading the vendor source before shipping the change, caught that my
first instinct was wrong, and reverted to the original minimal class rather
than leave in an unnecessary override justified by a false claim. Recorded
here because a retracted "fix" is exactly the kind of thing PROJECT_STATE.md
exists to keep honest.

### Verified this level
- `npx vite build` succeeded (no new frontend behaviour, confirmed nothing broke)
- Structural check (brace/paren balance) on all new PHP
- Every `RoleName::`/`PermissionName::` usage confirmed to import the enum
- Confirmed by reading `vendor/spatie/laravel-permission` source that permissions
  register as Gate abilities automatically (`PermissionServiceProvider`) and
  that `team_id` defaults to `null` (`DefaultTeamResolver`) rather than assuming
  either

### Still unverified — no PHP interpreter available here
- The seeder has not been run against a real database
- **19 new tests** (11 RBAC feature tests + 6 UserPolicy unit tests, plus 2
  registration tests updated for the new role assignment) have not been executed
- `tests/Pest.php` now seeds roles/permissions before every Feature/Integration
  test — this is required for the LEVEL 3 registration tests to keep passing
  (registration now calls `assignRole()`) and has not been confirmed to work end
  to end

## LEVEL 2 — Public Marketing Website (this delivery)

### Pages
`/`, `/services`, `/pricing`, `/how-it-works`, `/why-growza`, `/faq`, `/contact`,
plus `/legal/terms`, `/legal/privacy`, `/legal/refund-policy`,
`/legal/acceptable-use`, `/legal/cookie-policy`.

### Approach
- **Content lives in `config/growza-marketing.php`, not in Blade.** Services,
  platforms, audiences, process steps, pricing tiers and FAQs are all data.
  LEVEL 6 replaces the `services` key with a DB query without touching a single
  template — which is what "do not hard-code services into frontend code" requires.
- `MarketingPageController` renders pages only; `ContactController` delegates to
  a `StoreContactMessage` Action in the Support domain, so LEVEL 12 can add an
  admin notification and LEVEL 13 can add ticket conversion without the
  controller changing.
- New `contact_messages` table, kept deliberately separate from LEVEL 13's
  `support_tickets` (anonymous enquiry vs. authenticated, assigned ticket).

### Contact form is genuinely functional
Validation via `ContactFormRequest`, honeypot field, `throttle:5,10` on the POST
route, persistence with `status`/`ip_address`/`user_agent` set outside
`$fillable` so they can't be mass-assigned. Not a dead button.

### SEO
Per-page titles/descriptions, canonical URLs, Open Graph + Twitter cards via an
`<x-seo>` component; Organization structured data site-wide and FAQPage
structured data generated from the same config the page renders (so markup and
visible content cannot drift). `sitemap.xml` is generated from an explicit route
list; `robots.txt` blocks everything unless `APP_ALLOW_INDEXING=true`, and even
then disallows `/dashboard`, `/admin`, `/dev`.

### Two deliberate judgement calls
1. **Testimonials ship empty.** The spec asks for a testimonials section and the
   markup exists, but publishing invented quotes from non-existent customers on a
   live commercial site is deceptive advertising. The section renders only once
   real, permissioned quotes are added to the config. The homepage test asserts
   this stays true.
2. **Legal pages are marked as drafts.** Each renders a visible
   "pending legal review — not binding" banner until
   `GROWZA_LEGAL_REVIEWED=true`. Sections requiring genuine legal judgement
   (limitation of liability, governing law, wallet-balance regulatory treatment
   under CBN rules, NDPA retention periods, statutory withdrawal rights) are left
   as explicit `REVIEW NOTE` markers rather than invented. **A lawyer in your
   jurisdiction must review these before you accept a single payment.**

### Bugs found and fixed during this level
- **No base `Controller` class existed.** Laravel 11's slim skeleton omits it and
  LEVEL 0 never added it; every new controller extended a non-existent class.
  Created with `AuthorizesRequests` so the mandatory Policy-check rule works.
- **Closure-based legal routes** would have broken `php artisan route:cache` in
  production. Rewritten as controller actions with route defaults.
- **`email:rfc,dns`** performs a live MX lookup — it would fail on test domains
  and reject valid addresses with unusual mail routing. Reduced to `email:rfc`.
- **Rate-limiter state leaked between tests** (array cache persists in-process),
  which would have caused spurious 429s. Added a `Cache::flush()` in `beforeEach`.
- **`route('register')`/`route('login')`** would have thrown
  `RouteNotFoundException` since LEVEL 3 does not exist. Changed to `url()`, which
  degrades to a 404 instead of a 500.

### Verified this level
- `npm install` + `npx vite build` succeeded; CSS grew 37.65 kB → 43.24 kB,
  confirming the new marketing utilities and `.prose-growza` styles compiled.
- Audited that every `<x-component>` referenced in any view resolves to a real
  file, and that every `route()` name used in a view is actually defined.
- Structural check (brace/paren balance, comment terminators) on all new PHP.

### Still unverified — no PHP interpreter in the authoring environment
- `php artisan migrate` has not run the `contact_messages` migration.
- **The 22 tests written this level have not been executed.** Run
  `php artisan test` and report failures.
- No page has been rendered by PHP or viewed in a browser; Alpine interactivity
  and the LEVEL 58 responsive breakpoints remain unchecked.

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

## ✅ LEVEL 0 Verification (confirmed by user after real deployment)

1. `composer install` — resolved successfully.
2. Migrations — ran successfully against real PostgreSQL.
3. `docker compose up` — full stack (app, nginx, postgres, redis, queue worker, scheduler) came up healthy.
4. `php artisan key:generate` — succeeded.
5. Storage permissions — required a manual fix on first boot (expected on some host/Docker UID setups; not a code defect, but worth a one-line note in DEPLOYMENT.md when that level is written: `chown -R www-data:www-data storage bootstrap/cache` if `APP_KEY`/log-write errors appear on first boot).
6. `http://localhost:8000` — responds and correctly renders the "Growza — Grow Smarter. Reach Further." placeholder homepage.

Pest test execution (`php artisan test`) was not explicitly reported — confirm this separately when convenient; not a blocker for starting LEVEL 1.

**LEVEL 0 is CLOSED.**
