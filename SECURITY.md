# SECURITY.md

**Status: placeholder — full security review is LEVEL 25.**

This file will document, once each relevant level ships:
- Threat model per domain (auth, wallet, payments, providers, API)
- Mitigations for the LEVEL 25 checklist (SQL injection, XSS, CSRF, IDOR, mass assignment, privilege escalation, session attacks, brute force, rate limiting, insecure uploads, webhook spoofing, payment replay, duplicate transactions, API abuse, data leakage)
- Secrets-handling policy (confirmed at LEVEL 0: `.env` + deployment secret store only, never committed, never logged; sensitive DB columns use Laravel's `encrypted` cast)
- Responsible disclosure process

## Decisions already locked in at LEVEL 0

- No model uses `$guarded = []`; every model declares explicit `$fillable`.
- Webhook routes are excluded from CSRF but must independently verify gateway signatures (LEVEL 9) — this is a hard requirement, not a suggestion.
- Redis cache/queue/session use separate logical databases so a cache-clear operation can never touch queued financial jobs or active sessions.
- `APP_ALLOW_INDEXING` gates whether any route is eligible for search indexing at all — dashboard/admin routes will never set this true.

Everything else is deferred to LEVEL 25 rather than fabricated here.
