# Growza

**Grow Smarter. Reach Further.**

A digital-growth and marketing platform for creators, musicians, businesses, brands, agencies, and digital marketers — built as a Laravel 11 monolith with domain-oriented architecture. See `ARCHITECTURE.md` for the full system design and `PROJECT_STATE.md` for exactly what has and hasn't been built yet.

This codebase was authored in an environment without PHP/Composer available, so the steps below **have not been executed** — follow them in a real PHP 8.3+ environment and report anything that fails.

## Requirements

- PHP 8.3+
- Composer 2
- PostgreSQL 16 (or MySQL 8, compatibility mode)
- Redis 7
- Node 20+ (needed starting at LEVEL 1, once frontend build assets exist)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`: set `DB_*` and `REDIS_*` to match your local Postgres/Redis, or skip straight to Docker below.

```bash
php artisan migrate
php artisan test
php artisan serve
```

Visit `http://localhost:8000` — you should see the LEVEL 0 placeholder homepage. `http://localhost:8000/up` should return a 200 health check.

## Or: Docker

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan test
```

App is served via nginx on `http://localhost:8000`.

## What's actually implemented right now

Only the foundation: project skeleton, domain folder structure, framework/RBAC/audit/settings/teams tables, service provider registration, middleware aliases, route skeleton. **No feature (auth, wallet, orders, payments, etc.) is implemented yet** — see `PROJECT_STATE.md` for the batch-by-batch build sequence and exactly what's next.

## Project documentation

| File | Contents |
|---|---|
| `ARCHITECTURE.md` | Full system/domain/database architecture, approved before any code was written |
| `PROJECT_STATE.md` | Living tracker — current version, completed/pending levels, known issues |
| `DATABASE.md` | Schema reference and rationale, kept in sync with migrations |
| `SECURITY.md` | Security decisions and the LEVEL 25 hardening checklist (placeholder until then) |
| `DEPLOYMENT.md` | Deployment steps (placeholder until LEVEL 33) |
| `API.md` | API reference (placeholder until LEVEL 19) |
| `CHANGELOG.md` | Level-by-level change log |

## Report issues here, don't guess around them

If `composer install`, migrations, or tests fail in your environment, that's expected to surface real issues this authoring environment couldn't catch (no PHP interpreter was available). Report the exact error and it'll be fixed via the project's Error Protocol (reproduce → root cause → fix → re-verify) rather than patched around blindly.
