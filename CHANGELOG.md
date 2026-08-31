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
