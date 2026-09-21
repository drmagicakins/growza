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
| 1 | Brand & Design System | ✅ Complete | 🟡 Partially verified — Vite/Tailwind build verified; browser render not |
| 2 | Public Marketing Website | ✅ Complete | 🟡 Partially verified — see LEVEL 2 section |
| 3 | Authentication | ✅ Complete | 🟡 Partially verified — see LEVEL 3 section |
| 4 | User & Role System (RBAC) | ✅ Complete | 🟡 Partially verified — see LEVEL 4 section |
| 5 | Customer Dashboard | ✅ Complete | 🟡 Partially verified — see LEVEL 5 section |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: LEVEL 6 (Service Catalogue)** — database-driven
platforms/categories/services, replacing `config('growza-marketing.services')`
as the source both the marketing site and the dashboard services page read
from. This is the first level that gives LEVEL 7 (orders) something real to
reference.

## LEVEL 5 — Customer Dashboard (this delivery)

### Delivered
- Real dashboard layout: desktop sidebar + a genuinely distinct mobile pattern
  (full-height off-canvas drawer with larger touch targets), not the sidebar
  resized — see `components/layouts/dashboard.blade.php`
- All 10 routes from the spec: `/dashboard`, `/dashboard/profile`,
  `/services`, `/orders`, `/wallet`, `/transactions`, `/referrals`,
  `/support`, `/notifications`, `/settings`
- The six required metrics (wallet balance, total/active/completed orders,
  total spent, referral earnings) via a `CustomerDashboardMetricsService` —
  the seam LEVEL 7/8/14 fill in with real queries without the view or route
  changing, same pattern as `config/growza-marketing.php`
- A genuinely functional profile edit page, wired to the Fortify endpoint
  `CreateNewUser`'s sibling action `UpdateUserProfileInformation` already
  provided from LEVEL 3 — no new controller logic needed for the update itself
- `App\Support\Money` — minor-units currency formatter, presentation-only

### Every metric is honestly zero, not faked
Wallet balance, orders, and referral earnings all show `₦0.00` / `0` because
none of those things have happened yet — there is no wallet, no order, no
referral commission in existence. A test asserts the dashboard shows `₦0.00`
and the real LEVEL 41-style empty state ("No campaigns yet"), not a fabricated
number. Orders, wallet, transactions, referrals, support and notifications
pages all render honest "not live yet" empty states rather than fake lists.

### Deliberate scope limit
**`/dashboard/orders/{order}` is NOT registered.** There is no `Order` model to
bind a route parameter to until LEVEL 7 — registering it now would be exactly
the "fake dashboard where buttons do nothing" the project rules prohibit. A
test asserts requesting `/dashboard/orders/1` 404s. LEVEL 7 adds this route
alongside the `Order` model and `OrderPolicy` together.

### Three real bugs caught while building, not shipped
1. **`x-dropdown-item as="button"` hardcoded `type="button"`.** Wrapping it in
   the logout `<form>` would have silently done nothing on click — the button
   would never submit the form. Fixed the component itself (added a `type`
   prop) rather than working around it in one call site, since any future use
   inside a form would hit the same trap.
2. **`<x-card as="a" href="...">` does not work** — the card component only
   ever renders a `<div>` and has no `as`/`href` prop, so the Settings hub's
   first tile would have looked clickable and done nothing. Rewritten to wrap
   the card in a real `<a>`, matching the pattern already used for the second
   tile.
3. **Route naming collision avoided before it shipped**: naming the dashboard
   index route `dashboard.index` inside the `dashboard.` group would have
   broken every existing `route('dashboard')` reference from LEVEL 3 (Fortify's
   post-login redirect target, tests, view links). Registered `/dashboard`
   under the plain name `dashboard` outside the prefixed group instead, and
   put every sub-page under `dashboard.*`.

### Verified this level
- `npx vite build` succeeded (CSS 44.43 → 46.17 kB)
- Every `<x-component>` reference resolves to a real file
- Every literal `route()` call in dashboard views, and every dynamic route name
  in the shared nav array, cross-checked against what `routes/auth.php` actually
  registers
- Confirmed via Fortify's own source (`ProfileInformationController` and the
  `PROFILE_INFORMATION_UPDATED` constant) that the profile form's session-status
  check matches what Fortify actually flashes, rather than assumed
- Cleaned a leftover dead comment in `routes/web.php` from LEVEL 3's edit

### Still unverified — no PHP interpreter available here
- **32 new tests** (dashboard access/rendering, profile update, zero-metrics,
  referral code display, the deliberate 404 on `/dashboard/orders/1`, plus 2
  unit tests) have not been executed
- No page rendered in a browser — the off-canvas drawer's Alpine transitions
  and the sidebar/drawer breakpoint switch at `md` are unverified visually

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
