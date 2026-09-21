# DESIGN_SYSTEM.md

Written specification for LEVEL 1. The living, clickable version of everything below is at `/dev/design-system` (registered only outside `production`). If this document and that page ever disagree, the page is out of date — fix the page, not this doc, since the page is real rendered output.

---

## Why these specific choices

The master prompt is explicit that Growza must not look like a generic AI-generated SaaS site. Concretely, that means avoiding: purple/blue gradient hero sections, glassmorphism, oversized rounded "blob" cards, stock illustrations, and Inter-everywhere typography with no hierarchy. Every decision below is a direct countermeasure to one of those defaults.

## Colors

Two-color system, not a rainbow: **ink** (a warm near-black/near-white neutral scale that does almost all the work) and **ember** (a single restrained amber/copper accent used sparingly — CTAs, active states, highlights). This is deliberately *not* Tailwind's default `slate`/`blue` — a warm neutral reads as considered rather than templated.

| Token | Hex | Use |
|---|---|---|
| `ink-950` → `ink-50` | `#0D0D08` → `#F7F7F5` | Text, borders, backgrounds, structure |
| `ember-900` → `ember-50` | `#402710` → `#FDF6EC` | Primary CTAs, active/selected states, brand accent — never more than one ember element per view unless it's a state (badge) |

Semantic colors (`success`, `warning`, `danger`, `info`) are intentionally **muted**, not saturated alert-red/green — they should read as calm and professional even when reporting a failure. Full swatches: `tailwind.config.js`.

**Rule:** no gradient text, no gradient backgrounds anywhere in the product. If a section needs visual separation, use a background color shift (`bg-ink-50` vs `bg-white`) or a border, not a gradient.

## Typography

**Fraunces** (a display serif with some personality) for headings and hero copy; **Public Sans** (a plain, highly legible grotesque) for UI and body text. This pairing — editorial serif for voice, plain sans for function — is the single biggest lever against the generic-SaaS look, since almost every AI-generated site uses one geometric sans (Inter/Poppins/Manrope) for everything.

| Class | Font | Use |
|---|---|---|
| `font-display` | Fraunces | `<h1>`–`<h4>`, hero headlines, section titles |
| `font-sans` (default) | Public Sans | Body copy, buttons, labels, table content |
| `text-display-xl/lg/md/sm` | — | Hero and section heading sizes (see `tailwind.config.js`) |

Body text base size is **15px** (`text-[15px]` on `body`), not the more cramped 14px common in AI-generated dashboards — slightly larger body text reads as more considered, especially for a product handling money.

## Spacing

No new spacing tokens — Tailwind's default scale is used, but with a documented **rhythm convention** so pages don't feel randomly spaced:

| Context | Convention |
|---|---|
| Marketing section vertical padding | `py-24` desktop, `py-16` mobile |
| Card internal padding | `p-6` (`<x-card>`'s default) |
| Form field vertical gap | `space-y-6` within a form, `gap-6` in a grid |
| Page container | `max-w-content` (1180px), `px-6` |

## Borders & Radius

Restrained on purpose — `borderRadius.DEFAULT` is 8px, `lg` is 14px. Nothing in this system uses the 20–24px "everything is a soft blob" radius common in AI-generated UI. See `tailwind.config.js` → `borderRadius`.

## Shadows

Three low-elevation levels only (`shadow-resting`, `shadow-raised`, `shadow-floating`), all low-opacity dark shadows — no colored glows, no glassmorphism (`backdrop-blur` + translucent panels are not used anywhere in this system).

## Components

All components live in `resources/views/components/*.blade.php` and are demoed live at `/dev/design-system`. LEVEL 1 scope is intentionally limited to what the master prompt names explicitly — a broader reusable component library (Tabs, ConfirmDialog, StatCard, Toast) is LEVEL 40's job, not built ahead of schedule here.

| Component | File | Notes |
|---|---|---|
| Button | `button.blade.php` | Variants: primary, secondary, ghost, accent, danger. Sizes: sm/md/lg. Renders as `<button>` or `<a>` via `as` prop. |
| Input / Textarea / Select | `input.blade.php`, `textarea.blade.php`, `select.blade.php` | All support `label`, `error`, `help` props — error state always wins visually over help text. |
| Card | `card.blade.php` | `padded` prop toggles the default `p-6`. |
| Badge | `badge.blade.php` | Variants map 1:1 to semantic colors plus `neutral`/`accent`. |
| Alert | `alert.blade.php` | Variants: success/warning/danger/info. `dismissible` uses Alpine `x-data`. Satisfies the LEVEL 42 "explain what happened and what to do" requirement — always pass a `title` for anything actionable. |
| Modal | `modal.blade.php` | Alpine-based, opened via `$dispatch('open-modal-{name}')`. **Known gap, flagged not silently fixed:** no focus trap yet — a keyboard user can currently Tab out of an open modal into the page behind it. This is a real LEVEL 29 (accessibility) follow-up, not resolved in this delivery. |
| Dropdown | `dropdown.blade.php` + `dropdown-item.blade.php` | Alpine `x-on:click.outside` to close. |
| Nav bar | `nav-bar.blade.php` | **Marketing site only** — scoped to LEVEL 2's public pages. Dashboard/admin nav is a separate component built at LEVEL 5/16. |
| Breadcrumbs | `breadcrumbs.blade.php` | Takes an `items` array; last item renders as plain text (current page), not a link. |
| Pagination | `resources/views/vendor/pagination/growza.blade.php` | Overrides Laravel's default paginator view — call `->links('vendor.pagination.growza')` or set as the app default in a future `AppServiceProvider::boot()` once real paginated lists exist (LEVEL 5+). |
| Empty state | `empty-state.blade.php` | Requires `title`; strongly encourage `description` + an `action` slot with a real `<x-button>` — never ships as a bare "No data." |
| Loading state | `loading-state.blade.php` | Spinner + label, `role="status"` for screen readers. |

## Explicit Known Gaps (flagged, not silently shipped as complete)

1. **Modal has no focus trap.** Needs a small Alpine focus-trap directive or the `@alpinejs/focus` plugin — deferred to LEVEL 29.
2. **No dark mode.** Not requested by the master prompt; not built. If ever requested, `ink`/`ember` scales already read reasonably in both directions since they're not baked into arbitrary color guesses, but no `dark:` variants exist in any component today.
3. **Dashboard/admin-specific components** (StatCard, Tabs, ConfirmDialog, Toast) are LEVEL 40, not LEVEL 1 — don't expect them yet.
4. **Do not interpolate utility classes** (e.g. `class="bg-ink-{{ $variable }}"`) anywhere outside the one documented exception in `/dev/design-system` (which requires a Tailwind `safelist` entry to work at all, see `tailwind.config.js`). Tailwind's JIT compiler only detects literal class strings in source — dynamic class names silently fail to generate CSS. Every real component above uses a PHP array lookup (`$variants[$variant] ?? ...`) specifically to avoid this trap.
