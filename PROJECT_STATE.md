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

`0.4.0-foundation` (pre-1.0 — LEVEL 3 delivered)

## Completed Levels

| Level | Name | Status | Verified? |
|---|---|---|---|
| — | Master Architecture Specification | ✅ Complete | Reviewed with stakeholder |
| 0 | Project Foundation | ✅ Complete | ✅ Verified — confirmed running via Docker Compose (Laravel, Nginx, PostgreSQL, Redis, queue worker, scheduler all up; `/` serves Growza homepage) |
| 1 | Brand & Design System | ✅ Complete | ✅ Verified — the reported "Alpine never initialises" defect was a **QA harness artefact, not an app bug**; closed with user confirmation. See `storage/app/HANDOFF.md`. |
| 2 | Public Marketing Website | ✅ Complete | ✅ Verified — all 16 routes 200, contact form round-trip persisted to DB. |
| 3 | Authentication | ✅ Complete | ✅ Verified — **59 Pest tests pass (137 assertions)**, 51 routes, registration/login/logout/verification/2FA driven over real HTTP, audit trail populated. |

## Pending Levels

Levels 1 through 47+ per the master prompt, sequenced into batches — see `ARCHITECTURE.md` §21 for the full batch plan (Batch A: Foundation → Batch I: v2.0 Expansion).

**Immediately next: Batch A remainder** — LEVEL 4 (RBAC implementation on top of the schema LEVEL 0 created).

**LEVEL 4 readiness note:** the RBAC *schema* already exists from LEVEL 0 (`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`, plus `teams`/`team_user`, all team-scoped) and `config/permission.php` is in place with `teams => true`. The `permission` and `role` middleware aliases are registered in `bootstrap/app.php` and their implementations exist but are unseeded. LEVEL 4 is therefore mostly seeders + gates + a permissions UI, not schema work. `spatie/laravel-permission` is installed in `vendor/`.

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

Also present: `/dev/design-system` (LEVEL 1, non-production only) and the framework `/up` health check. Unknown routes return a branded 404. `php artisan route:list` reports **51 routes**.

The only remaining commented-out `require` is `dashboard.php` (LEVEL 5), which will hold the real customer dashboard once it exists.

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
- **⚠️ `config/session.php` MUST NOT be overwritten from a level archive.** The level-2 **and** level-3 archives both ship a literal `'connection' => 'session'`, which names a DB connection that does not exist and makes every request 500 under `SESSION_DRIVER=database`. It has been reverted twice. If a future level archive contains this file, keep the repo's version.
- **Local dev needs `MAIL_MAILER=log`.** The `.env` default (`smtp` on port 2525) assumes a Mailpit/Mailhog instance that is not running, so registration fails with a connection error when sending the verification email. Tests are unaffected (`phpunit.xml` sets `MAIL_MAILER=array`).
- **Registration needs HTTPS egress** for Laravel's `uncompromised()` breach check against HaveIBeenPwned. The CLI PHP at `C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\` had `curl.cainfo` and `openssl.cafile` unset, so every registration threw a cURL 60 error. Fixed by downloading `cacert.pem` into `storage/app/` and pointing `php.ini` at it. **Production on Linux needs none of this** (system CA store). If registration 500s after a fresh PHP install, this is why.
- **`EnsureUserIsActive` is registered but not yet applied to any route group.** It only matters once an admin can suspend a user (LEVEL 16/17). The suspension itself is already audited and the middleware is tested; it just has nothing to guard yet.
- **Email verification is enforced by the `verified` middleware on `dashboard`, but nothing in the UI resends the link from a failure screen yet** — Fortify's `verification.send` route exists; the marketing/dashboard surface for it lands with the real dashboard at LEVEL 5.
- `config/permission.php` is included by hand (matching spatie/laravel-permission's published defaults, with `teams => true`) since `vendor:publish` cannot run here. Worth diffing against the package's actual published version after `composer install`, in case the installed package version's default config has shifted since this was written.

## Tests
**59 tests, 137 assertions — all passing** (`php artisan test`, PHP 8.4.12, MySQL).

- `tests/Unit/ExampleTest.php`
- `tests/Feature/HealthCheckTest.php` — `/up`
- `tests/Feature/HomepageTest.php`
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
