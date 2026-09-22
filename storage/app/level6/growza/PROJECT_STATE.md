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

`0.7.0-foundation` (pre-1.0 — LEVEL 6 delivered)

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
| 6 | Service Catalogue | ✅ Complete | 🟡 Partially verified — see LEVEL 6 section |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: LEVEL 7 (Order/Campaign Engine)** — the `services` table
this level created is exactly what LEVEL 7 needed to exist first. The
"Ordering opens soon" badges/disabled buttons on the marketing service-detail
page and both services listings (marketing + dashboard) are what LEVEL 7
replaces with a real checkout flow.

## LEVEL 6 — Service Catalogue (this delivery)

### Delivered
- 4 new tables: `platforms`, `service_categories`, `services`,
  `service_price_tiers` — matching exactly what `DATABASE.md` committed to
  back at LEVEL 0
- `Platform`, `ServiceCategory`, `Service`, `ServicePriceTier` models in
  `App\Domain\Catalogue`, plus a `PricingModel` enum
- `CatalogueSeeder` — idempotent, seeds the 9 real platforms and 10 real
  services across 6 categories (a test asserts running it twice does not
  duplicate anything)
- Public service detail pages at `/services/{slug}` with real pricing,
  delivery estimates and requirements
- `config('growza-marketing.services')` and `.platforms` **removed** —
  fully superseded by the database, exactly as LEVEL 6 requires ("do not
  hard-code services into frontend code")

### No quantity-based pricing — by design, not oversight
The `services` table has no "quantity" column at all (no follower count,
like count, or stream count) — Growza's Acceptable Use Policy (LEVEL 2)
already ruled that out structurally, so the schema doesn't offer a column
that would only make sense for a service the platform refuses to sell.
Instead there are two pricing shapes:
- **`fixed`** — a flat package price (content strategy, SEO setup, etc.)
- **`budget_range`** — the customer picks an ad-spend budget within a min/max
  range plus a management fee (paid social campaigns) — this is what
  "minimum/maximum quantity or budget" in the master prompt maps to for a
  legitimate advertising service

### The two-template change this was always going to need
LEVEL 2's own notes flagged that moving services to the database would
require converting the two templates that iterate them from array access
(`$service['name']`) to object access (`$service->name`) — not a surprise,
exactly the anticipated, minimal-footprint change. `MarketingPageController`
now queries Eloquent instead of reading config; the homepage, services
listing, and dashboard services page were updated accordingly.

### Deliberately not built
**No admin CRUD for the catalogue.** `manage_services` (LEVEL 4's permission)
exists unused until LEVEL 16's admin panel actually gives someone a UI to
create/edit services — building that now would be building ahead of its level.

### Verified this level
- `npx vite build` succeeded (no CSS regression: 46.17 → 46.19 kB, essentially
  unchanged since no new utility classes were introduced)
- Every `<x-component>` and `route()` reference in new/updated views resolves
- Confirmed the seeder is genuinely idempotent (`Service::count()` unchanged
  after running it twice) rather than assuming `updateOrCreate` makes that true
- Confirmed no leftover reference to the removed config keys anywhere in
  `resources/` or `app/` via a full grep, not a partial check

### Still unverified — no PHP interpreter available here
- **21 new tests** (14 catalogue feature tests, 1 dashboard catalogue test,
  6 Service-model unit tests) have not been executed
- Migrations have not run against a real database

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
