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
