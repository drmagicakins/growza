# DEPLOYMENT.md

**Status: placeholder — full deployment documentation is LEVEL 33.**

## What exists now (LEVEL 0)

- `docker-compose.yml` — **local/staging development only.** Brings up app (PHP-FPM), nginx, postgres, redis, a queue worker, and a scheduler loop.
- `docker/supervisor/growza-worker.conf` — a production Supervisor unit file separating payment-queue workers from general workers. Not yet referenced by any deployment script — that automation is written at LEVEL 33.
- `.env.example` — every variable the app currently reads.

## Not yet written

- Production server provisioning steps (Ubuntu, Nginx TLS config, PHP-FPM tuning)
- Zero-downtime deploy script
- Backup/restore procedure (LEVEL 34)
- Monitoring/alerting setup (LEVEL 35)

Do not deploy this to production yet — LEVEL 0 through the end of Batch H ("v1.0 Hardening" in `ARCHITECTURE.md` §21) must ship first.
