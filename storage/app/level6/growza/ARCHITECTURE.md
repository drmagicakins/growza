# GROWZA — MASTER ARCHITECTURE SPECIFICATION

**Version:** Architecture Baseline for v1.0 → v2.0
**Status:** Pre-implementation — no application code has been written yet
**Purpose:** This document is the single source of truth that LEVEL 0 and all subsequent levels will be built against. It must be reviewed and approved before any implementation begins.

---

## 0. How To Read This Document

This spec is organized so that each numbered section answers one architectural question team members would ask before writing code. Sections reference each other rather than repeating content. Anywhere a decision is deferred (e.g. "final provider list TBD"), it is called out explicitly rather than silently assumed.

---

## 1. System Architecture (High Level)

Growza is a single Laravel monolith at v1.0, deliberately **not** a microservices system, structured internally along domain boundaries so it can be decomposed later if scale demands it.

```
┌─────────────────────────────────────────────────────────┐
│                     Nginx (TLS, edge)                    │
└───────────────┬───────────────────────────┬─────────────┘
                │                            │
     ┌──────────▼──────────┐      ┌──────────▼──────────┐
     │  PHP-FPM (Laravel)   │      │  Static assets (Vite │
     │  Web + API           │      │  build output)       │
     └──────────┬───────────┘      └───────────────────────┘
                │
     ┌──────────▼───────────────────────────────────────────┐
     │ Application Layer (Controllers, Form Requests,        │
     │ Policies, Resources)                                   │
     └──────────┬───────────────────────────────────────────┘
                │
     ┌──────────▼───────────────────────────────────────────┐
     │ Domain Layer (Services, Actions, DTOs, Events)         │
     └──────────┬───────────────────────────────────────────┘
                │
     ┌──────────▼───────────┐   ┌────────────────────────────┐
     │ Persistence (Eloquent │   │ Queue Workers (Supervisor)  │
     │ + PostgreSQL)         │   │ + Redis                     │
     └───────────────────────┘   └──────────────┬─────────────┘
                                                  │
                                   ┌──────────────▼─────────────┐
                                   │ External Integrations:      │
                                   │ Paystack, Flutterwave,       │
                                   │ Service Providers, Mail/SMS  │
                                   └───────────────────────────────┘
```

Key properties:

- **Single deployable artifact** for v1.0 (web + API + queue workers share one codebase, different processes).
- **Redis** serves three separate purposes that must not collide: cache, queue driver, and (optionally later) session store. These will use separate Redis logical databases/prefixes from day one so v2.0 can split them onto separate instances without a migration.
- **PostgreSQL** as system of record. All money, orders, and audit data live here — never in Redis or cache.
- **Stateless app servers**: no local file state that isn't reproducible; uploaded files go to object-compatible storage (local disk in dev, S3-compatible in production) so the app layer can scale horizontally later.

---

## 2. Laravel Application Architecture

Standard Laravel entry points are kept thin. The custom architecture lives in `app/Domain/*` rather than growing inside `app/Http` or `app/Models`.

Request lifecycle for a typical write action (e.g. "create order"):

```
Route → FormRequest (validation) → Controller (orchestration only)
     → Policy (authorization check)
     → Action/Service class (business logic, in Domain layer)
     → Model(s) (persistence)
     → Event dispatch (e.g. OrderCreated)
     → Listener(s) / Queued Job(s)
     → JSON/Blade Response (via API Resource or view)
```

Rules enforced across the codebase:

- Controllers never contain conditionals about pricing, wallet balances, permissions logic, or provider selection — they call a Service/Action and translate the result into a response.
- Models contain relationships, casts, scopes, and simple accessors only. No business rules (e.g. a `Wallet` model does not decide whether a debit is allowed — `WalletService` does).
- Every state-changing use case gets a single-purpose **Action** class (`app/Domain/<Domain>/Actions/...`) that can be unit-tested without HTTP.
- Cross-cutting concerns (notifications, audit logging, referral commission crediting) are triggered via **Events**, not called directly from Actions, so new side effects can be added without touching existing business logic.

---

## 3. Domain Architecture (Bounded Contexts)

```
app/Domain/
├── Auth/                 (registration, verification, 2FA)
├── Identity/             (users, roles, permissions, teams/agencies)
├── Wallet/               (wallets, ledger entries)
├── Payments/             (gateways, payment transactions, webhooks)
├── Catalogue/            (platforms, categories, services, pricing tiers)
├── Orders/                (orders, order items, status history)
├── Campaigns/            (campaign metadata layered on orders, for v1.5+)
├── Providers/            (provider adapters, health, failover)
├── Referrals/             (referral codes, commissions)
├── Coupons/               (coupons, redemptions)
├── Support/               (tickets, replies)
├── Notifications/         (preferences, dispatch)
├── Reselling/              (reseller tiers, API credentials) [v2.0-facing]
├── Agencies/                (multi-client teams) [v2.0-facing]
├── Reporting/               (read-only aggregation services)
└── Audit/                   (audit log writer + query)
```

Each domain folder follows the same internal shape:

```
Domain/<Name>/
├── Models/
├── Actions/            (single-purpose business operations)
├── Services/           (stateful/coordinating logic, e.g. PaymentService)
├── Events/
├── Listeners/
├── DTOs/
├── Enums/
├── Policies/
├── Exceptions/
└── Contracts/           (interfaces — e.g. PaymentGatewayInterface)
```

`Contracts` are what make Payments and Providers swappable: controllers and services depend on `PaymentGatewayInterface` / `ServiceProviderInterface`, never on `PaystackGateway` or a specific provider class directly. Laravel's service container binds the concrete implementation.

---

## 4. Database Entity Map

Grouped by domain. Column-level detail belongs in `DATABASE.md` (produced at LEVEL 0); this is the entity-relationship shape.

**Identity & Access**
- `users`
- `roles`, `permissions`, `permission_role` (or a package such as spatie/laravel-permission, decision flagged in §20)
- `teams` (agencies), `team_user` (membership + team role)

**Wallet & Ledger**
- `wallets` (one per user; balance is a *derived/cached* value, never the source of truth)
- `wallet_transactions` (append-only ledger: every credit/debit is a row; `wallets.balance` is recalculated/reconciled from this table, not the reverse)

**Payments**
- `payment_transactions` (gateway-agnostic record of an attempted payment)
- `payment_webhook_events` (raw inbound webhook log, keyed by gateway event ID for idempotency)

**Catalogue**
- `platforms`
- `service_categories`
- `services` (belongs to platform + category)
- `service_price_tiers` (retail/reseller/agency/enterprise pricing per service — see LEVEL 20)

**Orders**
- `orders`
- `order_status_histories`
- `order_provider_dispatches` (which provider fulfilled which order, for failover traceability)

**Providers**
- `providers`
- `provider_services` (maps a Growza `service` to a provider's external service ID)
- `provider_health_logs`

**Referrals**
- `referral_codes`
- `referral_conversions`
- `referral_commissions`

**Coupons**
- `coupons`
- `coupon_redemptions`

**Support**
- `support_tickets`
- `support_ticket_replies`

**Notifications**
- `notifications` (Laravel default table)
- `notification_preferences`

**Audit**
- `audit_logs` (polymorphic actor + polymorphic subject, before/after JSON snapshots)

**Reselling / Agencies (v2.0-facing, scaffolded early)**
- `reseller_profiles`
- `api_credentials` (scoped keys, not reused from user auth)
- `agency_clients`

This list intentionally does **not** yet include exact columns, indexes, or enum values — those are finalized in `DATABASE.md` during LEVEL 0 so they can be reviewed on their own before any migration is written.

---

## 5. Relationship Map (Core Entities)

```
User 1───1 Wallet 1───* WalletTransaction
User 1───* Order  ────* OrderStatusHistory
User 1───* PaymentTransaction
User 1───1 ReferralCode
User 1───* SupportTicket ────* SupportTicketReply
User *───* Team  (via team_user, with a per-team role)

Order *───1 Service ───1 Platform
Order *───1 Service ───1 ServiceCategory
Order 1───* OrderProviderDispatch ───1 Provider
Order 1───1 Coupon (nullable, via coupon_redemptions)
Order 1───1 PaymentTransaction (nullable — wallet-funded orders may have none)

Service 1───* ServicePriceTier
Service 1───* ProviderService ───1 Provider

ReferralCode 1───* ReferralConversion ───1 ReferralCommission
```

Foreign key discipline: every child row cascades or restricts deliberately (e.g. `orders.user_id` restricts delete — a user with orders cannot be hard-deleted, only soft-deleted/suspended). This is finalized per-table in `DATABASE.md`.

---

## 6. Authentication Architecture

- Session-based auth for the web dashboard (Laravel's default, via Breeze/Fortify-style scaffolding — package choice finalized at LEVEL 0, not assumed here).
- **Laravel Sanctum** for:
  - First-party SPA/mobile-adjacent needs if a JS frontend ever calls the API directly.
  - Third-party/API-key access for resellers (LEVEL 20) via Sanctum's token abilities, scoped per credential.
- Email verification required before order placement (not before browsing/dashboard read access, to avoid blocking legitimate onboarding friction unnecessarily — configurable).
- Password reset via signed, time-limited tokens (Laravel default), rate-limited per email and per IP.
- Login throttling via Laravel's built-in `RateLimiter` on the login route, separate from general API rate limiting.
- 2FA is **optional per user** at v1.0 (TOTP-based), required-by-role at v2.0 for Admin/Finance roles (flagged as a v2.0 dependency in §22).
- Every security-relevant auth event (password change, 2FA enabled/disabled, new-device login) fires a domain event consumed by both the Notification system (LEVEL 12) and Audit system (LEVEL 18).

---

## 7. RBAC Architecture

Two-layer permission model, both enforced **server-side** in Policies/Gates — never inferred from hidden UI:

1. **Role** — a coarse label (`super_admin`, `administrator`, `finance_manager`, `order_manager`, `provider_manager`, `support_agent`, `content_manager`, `reseller`, `customer`) stored on `users` (or `team_user` for team-scoped roles under Agencies).
2. **Permission** — a fine-grained capability (`view_users`, `manage_refunds`, etc.) attached to roles, checked via Laravel `Gate::authorize()` / Policy methods, not via `if ($user->role === 'admin')` string comparisons scattered through controllers.

Implementation decision to make explicit at LEVEL 0 (not assumed here): whether to hand-roll `roles`/`permissions` tables or adopt `spatie/laravel-permission`. Recommendation: adopt the package — it is battle-tested, supports Policy integration cleanly, and avoids reinventing caching of permission checks. This will be confirmed in `ARCHITECTURE.md`.

Every controller action that mutates state must call an explicit Policy check (`$this->authorize(...)`) — this is part of the LEVEL 59 code review checklist, not optional per-developer discretion.

---

## 8. Wallet Architecture

Core principle: **the ledger is the truth; the balance is a projection.**

```
WalletService::credit($wallet, $amount, $type, $reference, $meta)
WalletService::debit($wallet, $amount, $type, $reference, $meta)
```

Both operations, internally:

1. Open a DB transaction.
2. Lock the wallet row (`lockForUpdate()`).
3. Insert a `wallet_transactions` row (immutable, includes a unique `reference` and `idempotency_key`).
4. Recompute/update `wallets.balance` from that same transaction.
5. Commit. On any failure, roll back — no partial credit ever persists.

Idempotency: every crediting event (wallet funding webhook, referral commission, refund) carries an idempotency key derived from its source (e.g. gateway event ID). A unique constraint on `wallet_transactions.idempotency_key` makes double-processing a webhook a no-op rather than a double-credit — this is what LEVEL 8 and LEVEL 9 depend on jointly.

Currency handling: amounts stored as integer minor units (kobo, not naira-as-float) to eliminate float rounding bugs. Display formatting happens only at the presentation layer.

---

## 9. Payment Architecture

```
                 ┌────────────────────────┐
Controller ────▶ │     PaymentService      │
                 └───────────┬────────────┘
                             │ depends on
                             ▼
                 PaymentGatewayInterface
                    ▲                 ▲
                    │                 │
           PaystackGateway    FlutterwaveGateway
```

Interface shape (conceptual, finalized in code at LEVEL 9):

- `initialize(PaymentRequestDTO): PaymentInitResult` — returns a redirect/checkout URL.
- `verify(string $reference): PaymentVerificationResult` — server-to-server verification call, never trusts the browser redirect alone.
- `handleWebhook(Request): PaymentWebhookResult` — validates signature, extracts the gateway's event, and hands off to `PaymentService` for reconciliation.

Flow:

```
User funds wallet
 → PaymentService::initialize() picks gateway (user choice or default)
 → redirect to gateway checkout
 → gateway sends webhook (server-to-server, signed)
 → PaymentService verifies signature + calls gateway::verify() independently
 → on confirmed success: WalletService::credit() with idempotency key = gateway event ID
 → PaymentTransaction status updated
 → OrderNotification / WalletFundedNotification dispatched
```

Webhook endpoints are:
- Excluded from CSRF (Laravel convention for webhook routes) but signature-verified instead.
- Rate-limited and logged to `payment_webhook_events` **before** processing, so a crash mid-processing doesn't lose the event — it can be replayed.
- Never used as the sole basis for crediting a wallet without independent `verify()` confirmation against the gateway's API.

Secrets (`PAYSTACK_SECRET_KEY`, `FLUTTERWAVE_SECRET_KEY`) live only in `.env` / the deployment secret store — never in version control, never logged.

---

## 10. Provider Architecture

```
ServiceProviderInterface
   submitOrder(ProviderOrderDTO): ProviderOrderResult
   checkStatus(string $externalOrderId): ProviderStatusResult
   cancel(string $externalOrderId): ProviderCancelResult   // where supported
   refund(string $externalOrderId): ProviderRefundResult    // where supported
   balance(): ProviderBalanceResult
   healthCheck(): ProviderHealthResult
```

- Each concrete provider adapter isolates that provider's API quirks (auth style, request/response shape, error codes) entirely inside its own class — controllers and `OrderService` only ever talk to the interface.
- `provider_services` mapping table translates a Growza `service_id` into whatever ID/slug the provider expects, so providers can be swapped or added without touching `Order` logic.
- Failover (LEVEL 24) is implemented as a `ProviderRouter` that consults `provider_health_logs` and `providers.priority` to pick the next eligible provider for a given service when the primary is unavailable — never hard-coded per-controller `if` statements.
- Since Growza is explicitly scoped to **legitimate** marketing/growth services, provider integrations at v1.0 will be scaffolded as an isolated adapter interface with a documented mock/sandbox implementation for development; connecting a specific named third-party provider is a decision for the person operating Growza to make and configure via credentials — this document does not select or endorse a specific vendor.

---

## 11. Order Architecture

State machine (LEVEL 7), enforced only through `OrderService`/`Order` transitions — never via direct `$order->status = ...` assignment in controllers:

```
Draft → Pending Payment → Paid → Queued → Processing
                                     ├──▶ Completed
                                     ├──▶ Partially Completed
                                     ├──▶ Failed
                                     └──▶ Cancelled → Refunded / Partially Refunded
```

Every transition writes an `order_status_histories` row (who/what triggered it, previous/new state, timestamp) — this feeds both the customer-facing order timeline (LEVEL 5) and the Audit system (LEVEL 18).

Order reference format: `GZ-{year}-{zero_padded_sequence}` (e.g. `GZ-2026-000001`), generated atomically (DB sequence or locked counter row) to avoid collisions under concurrent order creation.

---

## 12. Queue Architecture

Redis-backed Laravel Queues, with named queues so a burst in one area (e.g. provider status polling) can't starve another (e.g. payment verification):

```
queue: payments     → VerifyPaymentJob
queue: orders        → ProcessOrderJob, CheckOrderStatusJob
queue: providers      → SyncProviderServicesJob, SyncProviderBalanceJob
queue: refunds         → ProcessRefundJob
queue: notifications    → SendNotificationJob
queue: reports            → GenerateReportJob
queue: maintenance          → CleanExpiredDataJob
```

Standards applied to every job:
- Implements `ShouldBeUnique` (Laravel) where duplicate dispatch would cause harm (e.g. `ProcessOrderJob` for the same order).
- Defines explicit `$tries` and `backoff()` rather than relying on defaults.
- `failed()` method records to `failed_jobs` *and* fires a notification to Order/Finance managers for anything touching money.
- Idempotent by design — re-running a job after a partial failure must not double-charge, double-credit, or double-submit to a provider.

Supervisor manages worker processes in production, one pool per queue group so payment-related jobs get dedicated worker capacity.

---

## 13. Notification Architecture

Laravel's notification system, multi-channel per notification class:

```
Notification (e.g. OrderCompletedNotification)
   → database channel (always — powers /dashboard/notifications)
   → mail channel (configurable per user)
   → sms channel (architecture only at v1.0; provider integration is a v1.1+ decision)
```

`notification_preferences` lets a user opt in/out per event category (payments, orders, security, marketing) — security notifications (password changes, new-device logins) are **not** user-suppressible, everything else is.

---

## 14. API Architecture

```
/api/v1/
  GET  /services
  GET  /balance
  POST /orders
  GET  /orders
  GET  /orders/{id}
  POST /orders/{id}/cancel
```

- Auth: Sanctum tokens with explicit **abilities/scopes** (e.g. `orders:create`, `orders:read`) — a reseller's key can be scoped to only what their tier permits.
- Rate limiting per token (not just per IP), using Laravel's `throttle` middleware with a custom limiter keyed on the token ID.
- Every response follows the standard envelope defined in the master prompt (§52): `{success, message, data}` / `{success, message, errors}` — implemented via a base `ApiResponse` helper/trait so no controller hand-rolls its own shape.
- Versioning via URL prefix (`/api/v1`) so `/api/v2` can introduce breaking changes later without disrupting existing reseller integrations — this is the specific mechanism behind the "extensible API" requirement in §61.
- OpenAPI/Swagger-style documentation generated from route + FormRequest definitions where practical, maintained in `API.md`.

---

## 15. Admin Architecture

Admin panel is a separate route group (`/admin/*`) under its own middleware stack (`auth`, `verified`, plus a permission gate per module), sharing the same Laravel app (not a separate application) but its own layout/component set so it can evolve UI-independently from the customer dashboard.

Each admin module (Users, Services, Orders, Payments, Wallets, Refunds, Providers, Coupons, Referrals, Support, Notifications, Reports, Audit Logs, Settings) maps 1:1 to a domain from §3, so admin screens are thin presentation layers over the same Actions/Services customer-facing code uses — e.g. an admin-initiated refund goes through the *same* `RefundService` a system-triggered refund would, just with a different actor and an additional confirmation step (LEVEL 45).

---

## 16. Security Architecture

Baseline (detailed pass happens at LEVEL 25, but these are load-bearing from LEVEL 0 onward):

- All mass-assignable models use explicit `$fillable` (never `$guarded = []`).
- Form Requests validate and authorize every input; controllers never read `$request->all()` into a model directly for anything financial.
- CSRF protection on all stateful web routes (Laravel default); webhook routes excluded but signature-verified instead (§9).
- IDOR prevention via Policies checked on every resource access (`$this->authorize('view', $order)`), not just route-model-binding presence.
- File uploads (support ticket attachments) validated by MIME type and size, stored outside the public webroot, served through a controller that re-checks authorization on every download.
- Secrets management via `.env` + deployment secret store, never committed, never logged (log scrubbing for known secret patterns as a safety net).
- Sensitive settings (payment keys, provider credentials) encrypted at rest using Laravel's `encrypted` cast where stored in the database (e.g. per-provider credentials in the `providers` table).

---

## 17. Testing Architecture

- **Pest** (recommended over raw PHPUnit for readability; confirmed at LEVEL 0) for Unit, Feature, and Integration suites.
- Unit tests target pure business logic in Actions/Services (pricing, wallet math, commission calculation, discount application, refund calculation, order state transitions) with no HTTP/DB-heavy setup where avoidable.
- Feature tests drive real HTTP requests through the full stack (registration → verification → wallet funding → order placement) against a test database.
- Integration tests against Paystack/Flutterwave/provider APIs run against their sandbox/test modes, never production credentials, and are separated into their own suite so they can be skipped in environments without sandbox credentials configured.
- Security-focused tests explicitly assert: unauthorized users get 403 on protected routes, IDOR attempts fail, duplicate webhook delivery does not double-credit a wallet, and rate limits engage.
- CI gate: no level is marked complete in `PROJECT_STATE.md` without its corresponding tests existing and passing in the sandbox/dev environment — per §5 of the master prompt, anything not actually executable in this environment will be explicitly flagged as unverified rather than assumed passing.

---

## 18. Deployment Architecture

```
Ubuntu (LTS) ── Nginx (TLS termination, reverse proxy)
             ── PHP-FPM (Laravel app)
             ── PostgreSQL (primary datastore)
             ── Redis (cache, queue, rate limiting)
             ── Supervisor (queue workers, per queue group)
             ── Laravel Scheduler (cron → `schedule:run` every minute)
```

- `.env.example` enumerates every required variable with no real secrets.
- A `/up` or `/health` endpoint (Laravel's built-in health-check route or a custom one) reports app, DB, Redis, and queue-worker liveness for uptime monitoring.
- Zero-downtime-oriented deploy sequence: pull code → install deps → run migrations (backward-compatible, never destructive within a release) → `php artisan config:cache`/`route:cache`/`view:cache` → restart PHP-FPM and queue workers gracefully (`queue:restart` rather than a hard kill, so in-flight jobs finish).
- Backups (LEVEL 34): scheduled PostgreSQL dumps with off-server retention, and storage/ backups for uploaded files, both documented with a tested restore procedure — not just a backup script that's never been proven to restore.

---

## 19. Folder Structure (Top Level)

```
growza/
├── app/
│   ├── Domain/              (see §3)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Web/
│   │   │   ├── Api/V1/
│   │   │   └── Admin/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Providers/            (Laravel service providers, not Growza "Providers" domain)
│   └── Support/                (helpers, shared value objects)
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/                  (Blade)
│   ├── js/                       (Alpine/Livewire glue)
│   └── css/                        (Tailwind entry)
├── routes/
│   ├── web.php
│   ├── api.php
│   └── admin.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── Integration/
├── PROJECT_STATE.md
├── ARCHITECTURE.md
├── DATABASE.md
├── API.md
├── DEPLOYMENT.md
├── SECURITY.md
└── CHANGELOG.md
```

---

## 20. Package / Dependency Recommendations

To be confirmed (not silently assumed) at LEVEL 0, but the working recommendation set is:

| Concern | Recommendation | Why |
|---|---|---|
| RBAC | `spatie/laravel-permission` | Battle-tested, Policy-friendly, avoids reinventing permission caching |
| Auth scaffolding | Laravel Fortify (headless) + custom Blade/Livewire views | Gives 2FA, email verification, password reset out of the box without imposing a UI kit |
| API tokens | Laravel Sanctum | First-party for Sanctum-issued reseller keys with abilities/scopes |
| Testing | Pest | Readable syntax, wraps PHPUnit, first-class Laravel plugin |
| Auditing | Custom `AuditLogger` service (not a package) | Needed shape (before/after diff, polymorphic actor) is specific enough that a hand-rolled service stays simpler than adapting a generic package |
| Money/pricing | Integer minor units + a small internal `Money` value object | Avoids float rounding bugs without pulling in a heavyweight currency library for a single-currency (NGN, initially) product |
| Frontend interactivity | Livewire + Alpine.js | Matches "Blade-first, minimal JS build complexity" while still supporting rich dashboard interactions |
| Styling | Tailwind CSS via Vite | Matches the design-system requirement in LEVEL 1 |

None of these are irreversible — they're documented here specifically so a deviation is a visible decision, not a silent drift.

---

## 21. Development Sequence

Levels are grouped into delivery batches; each batch is independently demoable and testable before moving on. This sequencing is what `PROJECT_STATE.md` will track against.

**Batch A — Foundation (must exist before anything else)**
LEVEL 0 (foundation), LEVEL 1 (design system), LEVEL 3 (auth), LEVEL 4 (RBAC)

**Batch B — Core Money Path (the riskiest part, built early and hardened)**
LEVEL 8 (wallet/ledger), LEVEL 9 (payments), LEVEL 36 (data integrity), a first pass of LEVEL 25 (security) scoped to the money path only

**Batch C — Core Product Loop**
LEVEL 6 (catalogue), LEVEL 7 (orders), LEVEL 10 (providers), LEVEL 11 (queues), LEVEL 5 (customer dashboard)

**Batch D — Trust & Support Surfaces**
LEVEL 12 (notifications), LEVEL 13 (support), LEVEL 18 (audit logging)

**Batch E — Growth Mechanics**
LEVEL 14 (referrals), LEVEL 15 (coupons)

**Batch F — Public Surface & SEO**
LEVEL 2 (marketing site), LEVEL 31 (SEO)

**Batch G — Admin & Reporting**
LEVEL 16 (admin panel), LEVEL 17 (reporting)

**Batch H — v1.0 Hardening**
LEVEL 25 (full security pass), LEVEL 26 (testing), LEVEL 27 (error handling), LEVEL 28 (performance), LEVEL 29 (accessibility), LEVEL 30 (mobile-first), LEVEL 32 (legal pages), LEVEL 33–35 (DevOps/backup/observability)

**v1.0 ships at the end of Batch H.**

**Batch I — v2.0 Expansion**
LEVEL 19 (public API), LEVEL 20 (reseller), LEVEL 21–22 (agencies/teams), LEVEL 23 (advanced campaigns), LEVEL 24 (provider failover)

---

## 22. Version 1.0 → 2.0 Dependency Map

This is the section that prevents a v2.0 rebuild. Each v2.0 feature's v1.0 prerequisite is named explicitly:

| v2.0 Feature | Depends on this v1.0 decision | Why it must be decided now |
|---|---|---|
| Public API (LEVEL 19) | Sanctum abilities/scopes chosen at LEVEL 3, `payment_transactions`/`orders` schema stable | Retrofitting scoped tokens onto an auth system built without them means reissuing every credential |
| Reseller tiers (LEVEL 20) | `service_price_tiers` table exists from LEVEL 6, even if only "retail" is populated at v1.0 | Adding a pricing-tier column later means backfilling every historic order's effective price |
| Agencies/Teams (LEVEL 21–22) | `teams`/`team_user` tables exist from LEVEL 4, even if unused by any UI at v1.0 | Bolting multi-tenancy onto a single-owner `users` table after orders exist requires a painful data migration |
| Provider failover (LEVEL 24) | `providers`/`provider_health_logs`/`provider_services` modeled as first-class tables from LEVEL 10, not a single hard-coded provider | Failover is impossible to retrofit onto code that assumes exactly one provider |
| 2FA required for staff (v2.0) | 2FA support built (optional) into LEVEL 3 | Making it mandatory later is a config flip, not new plumbing |
| Advanced campaign objects (LEVEL 23) | `orders` kept intentionally free of campaign-specific columns; a `campaigns` domain wraps orders rather than orders growing campaign fields | Keeps the order state machine (LEVEL 11) stable while campaign metadata evolves independently |

---

## Explicit Open Decisions (Not Assumed)

These are flagged rather than silently resolved, per the "do not fabricate/assume" instruction in the master prompt:

1. **RBAC package vs hand-rolled** — recommendation given in §20, final call at LEVEL 0.
2. **Auth scaffolding package** (Fortify vs Breeze vs custom) — recommendation given in §20.
3. **Named third-party service providers** — this document defines the *adapter interface* only (§10); which specific provider(s) to integrate, and their compliance with the third-party platforms' own terms of service, is an operational/legal decision for whoever operates Growza, not something this architecture selects on your behalf.
4. **SMS provider** for notifications (LEVEL 12) — architecture only until a provider is chosen.
5. **Object storage** for uploads (local disk vs S3-compatible) — local disk assumed for dev/staging; production choice depends on your hosting environment.

---

## Next Step

Per the workflow in the master prompt, the next message begins **LEVEL 0 — Project Foundation**: initializing the Laravel project skeleton, `PROJECT_STATE.md`, `ARCHITECTURE.md`, `DATABASE.md`, environment configuration, and the coding/git/testing standards that every subsequent level will be checked against — before any feature-level code (auth, wallet, orders, etc.) is written.
