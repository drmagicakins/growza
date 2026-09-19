# LEVEL 2 — Doc Updates

Append the CHANGELOG block below to your `CHANGELOG.md`, and apply the PROJECT_STATE changes to your `PROJECT_STATE.md`. Kept as a separate patch file rather than shipping whole replacement docs, so your existing LEVEL 0/LEVEL 1 entries aren't overwritten.

---

## For CHANGELOG.md — append this

```markdown
## [0.3.0-foundation] — LEVEL 2: Public Marketing Website

### Added
- Eleven public routes: `/`, `/services`, `/pricing`, `/how-it-works`, `/why-growza`, `/faq`, `/contact`, `/terms`, `/privacy`, `/refund-policy`, `/acceptable-use`
- `routes/marketing.php`, required from `web.php`
- `PageController`, `ContactController`, `SitemapController` (`app/Http/Controllers/Web/`)
- `app/Http/Controllers/Controller.php` — base controller with `AuthorizesRequests`; Laravel 11's slim skeleton omits it and LEVEL 0 never created it
- `config/marketing.php` — all marketing content (services, platforms, pricing tiers, FAQs, process) kept out of Blade
- Working contact form: `ContactRequest` (server-side validation + honeypot), `ContactMessage` model, `contact_messages` migration, rate-limited POST route
- `sitemap.xml` and `robots.txt`, generated (not static) so robots can refuse indexing outside production
- `x-seo-meta` component: title, description, canonical, robots, Open Graph, Twitter/X, JSON-LD
- Organization structured data on the homepage; FAQPage structured data on `/faq`
- `x-site-header` (active nav states, mobile menu), `x-site-footer`, `x-layouts.marketing` (with skip link)
- Branded 404 / 500 / 503 error pages
- Four legal pages, each carrying a visible "pending legal review" notice
- 11 new feature tests across marketing pages, contact form, and SEO

### Changed
- `routes/web.php` — now requires `marketing.php`; the inline `/` closure moved to `PageController@home`
- `resources/views/marketing/home.blade.php` — LEVEL 0 placeholder replaced with the real homepage

### Verified
- `npm install` + `npx vite build` succeeded; CSS grew 37.65 kB → 42.90 kB, confirming new view classes compiled
- Confirmed `sr-only`, `max-w-prose`, `max-w-content`, `group-open`, `display-lg`, `text-ember-600`, `bg-ink-900` all present in compiled output
- Cross-checked every `route()` name used in views against defined route names — no broken references
- Cross-checked every `<x-component>` tag against component files — all resolve
- Checked `@foreach`/`@if`/`@php` directive balance across all new Blade files — all balanced

### Still unverified
- No PHP available in the authoring environment: Blade rendering, the contact form round-trip, migrations, and the Pest suite have **not** been executed
- Responsive breakpoints not checked in a browser
```

---

## For PROJECT_STATE.md — apply these

**Current Version:** change to `0.3.0-foundation` (pre-1.0 — LEVEL 2 delivered)

**Completed Levels table:** add this row

```markdown
| 2 | Public Marketing Website | ✅ Complete | 🟡 Partially verified — asset build and static cross-checks pass; no PHP execution available |
```

**Immediately next:** change to — **Batch A remainder**: LEVEL 3 (Authentication), then LEVEL 4 (RBAC).

**Add to Known Issues:**

```markdown
- **LEVEL 1 style-guide sample copy needs correcting.** `resources/views/dev/design-system.blade.php`
  uses sample cards reading "Instagram Growth — 1,000–10,000 followers · 24–48h delivery" and
  "TikTok Engagement — Views & shares · Instant start". That describes a follower/engagement
  delivery panel, which master prompt §1 explicitly forbids. The sample data should be changed to
  legitimate service names (e.g. "Managed Ad Campaign — Instagram", "Creator Partnership — TikTok",
  "Release Campaign — Spotify"). Not corrected automatically because the file was not present in the
  codebase supplied for this level; change it in place rather than replacing the file.
- **Contact enquiries are stored but no email notification is sent.** Deliberate: notifications are
  LEVEL 12, and dispatching mail from the controller now would violate ARCHITECTURE.md §2 and have to
  be unpicked. Submissions are durably persisted and logged, so nothing is lost. Until LEVEL 12,
  check `contact_messages` directly.
- **Legal pages are unreviewed drafts.** All four carry a visible draft notice. The highest-risk open
  item is whether Growza wallet balances constitute stored value under Nigerian financial regulation
  — that needs a professional answer before accepting live customer money.
```

**Add to Routes section:**

```markdown
LEVEL 2 added: `/`, `/services`, `/pricing`, `/how-it-works`, `/why-growza`, `/faq`,
`/contact` (GET + throttled POST), `/terms`, `/privacy`, `/refund-policy`,
`/acceptable-use`, `/sitemap.xml`, `/robots.txt`.
```

**Add to Environment Requirements:**

```markdown
Node 20+ is now genuinely required (not just "from LEVEL 1 onward") — the marketing site will not
render styled without `npm install && npm run build`.
```

**Add to Security Decisions:**

```markdown
- Public contact POST is rate limited (`throttle:6,1`) and honeypot-guarded; CSRF applies via the
  standard `web` group.
- `robots.txt` and the `noindex` meta tag both refuse indexing in any non-production environment,
  so a staging deployment cannot leak into search results.
- Legal pages ship with visible draft notices rather than presenting unreviewed text as binding.
```
