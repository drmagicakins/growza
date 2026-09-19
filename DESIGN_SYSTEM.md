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

## VERIFIED 2026-02-14 — real-browser QA (Level 1 closeout)

> **⚠️ THE FAIL VERDICT BELOW IS WRONG — SEE `storage/app/HANDOFF.md`.**
> The "Alpine.js never initialises" result was an artefact of the QA harness:
> under the browser-automation driver, `<script>` elements in the page never
> execute, and every probe read its verdict back through that same script-inert
> document. The user has since clicked the FAQ disclosure in a real browser and
> **it opens**. The bundle is valid, served correctly, and executing its bytes by
> hand sets `window.Alpine` to an object.
>
> Therefore these parts of the section below are **falsified and must not be
> acted on**: the `data-navigate-track="reload"` suspicion, the "run
> `laravel new` and compare" next step, and the workaround of hand-rendering the
> asset tags. Do not bisect `resources/js/app.js` or `bootstrap/app.php` for
> this. The section is preserved only as the record of how the wrong conclusion
> was reached.
>
> Still valid below: the note that a console-only check reports a clean page
> while the front end appears inert — that observation is exactly why an
> instrument-level false negative went unnoticed for so long.

Everything below was checked in a real headless Chromium against
`php artisan serve` on `127.0.0.1:8125`, not reviewed by eye.

**PASS — all 8 routes:** `/`, `/services`, `/pricing`, `/how-it-works`, `/faq`,
`/contact`, `/dev/design-system`, `/up`. Every one returns HTTP 200, renders a
non-empty body, loads both `app-*.css` and `app-*.js` from `/build/assets/`, and
produces **zero console errors and zero failed network requests**.

**PASS — color-swatch safelist (gap #4 above).** `bg-ink-50`, `bg-ink-500`,
`bg-ink-950`, `bg-ember-500`, `bg-ember-900` all resolve to real rules in the
compiled CSS and render on `/dev/design-system`. The Level 1 safelist fix holds.

**FAIL — Alpine.js never initialises. Every interactive component is dead.**
This is the one real defect and it invalidates the "partially verified" status
of the component library. Confirmed symptoms on `/faq`:

- `window.Alpine` is `undefined` on every page;
- the raw directives are still in the live DOM — 19× `x-data`, 19× `x-on:click`,
  21× `x-show`, 18× `:class`, 19× `:aria-expanded`;
- FAQ disclosures do not open (clicking a question changes no visible text);
- the modal does not open on `/dev/design-system`;
- the nav-bar mobile toggle does not toggle;
- the dismissible alert does not dismiss.

Only the **dropdown** appeared to work in the first pass, and that was a false
positive: `x-on:click.stop`'s `.stop` is honoured by the browser as a native
listener, and `x-show`'s raw attribute happens to satisfy my selector. Alpine was
not involved. Do not treat "the dropdown worked" as evidence Alpine runs.

**Important: this is NOT reported by the console.** Zero errors, zero failed
requests, and `performance.getEntriesByType('resource')` shows the JS was fetched
successfully (`transferSize` 55710, `initiatorType: "other"`). An uncaught error
inside a `<script type="module">` fires `window.onerror`, not a Console API
message, so a console-only check reports a perfectly clean page while the entire
front end is inert. Any future QA of this codebase must assert on `window.Alpine`
or on live `getComputedStyle` values, never on console output alone.

**Observed chain (exact, reproduced):** the page emits the correct tags —
`<link rel="modulepreload" href=".../app-D8MKG-Ji.js">` followed by
`<script type="module" src=".../app-D8MKG-Ji.js" data-navigate-track="reload">`
— and the browser **does** fetch the file: `request` fires with
`resourceType: script`, the response is `200`, and `response.body()` yields the
full **55410 bytes**. It is fetched in full and then simply never executed: no
`pageerror`, no console message, `window.Alpine` still `undefined` after 3s.

**The bundle itself is provably fine.** Executing that exact fetched text
manually — in the page's own context — sets `window.Alpine` to an object *and*
runs `Alpine.start()`, which strips `x-cloak` and processes the directives
(`x-cloak` count drops to 0, `hasXCloakAttr` false). A real dynamic
`import()` of the same URL also succeeds ("IMPORT OK") and likewise leaves
`window.Alpine` as an object. So: valid bundle, successful fetch, correct URL,
correct MIME (`application/javascript`), correct byte count — and no execution.

**Falsified en route:** stale hash (served filename matches `manifest.json`
exactly, both `model` and `file`); a missing/blocked request (it returns 200 with
a full body); a `preventDefault()`ed module handler (no click or pointerdown
precedes load — the page is loaded directly); third-party interference (the only
attributes on the tag are `type`, `src` and Laravel's own
`data-navigate-track="reload"`; no CSP header and no analytics scripts).

**The standing suspicion is `data-navigate-track="reload"`.** Laravel's `@vite`
emits it when Vite's `refresh: true` is set (as it is in `vite.config.js`) so the
client can hot-reload on asset change. Under a plain top-level `GET /faq` it has
exactly one effect — it adds a `<link rel="modulepreload">` — and no client-side
navigation is involved, so there is no obvious mechanism for it to stop the
module running. That makes it **suspected but not proven**, and it is recorded as
such rather than as the answer. A second, structural point: `Alpine.start()` is
called at module scope inside the bundle, so there is no "Alpine is slow"
explanation for the observed result — if the script ran at all, "object", not
"undefined".

**Next step (cheap, do this first):** `git stash` this repo, run `laravel new`
per `README.md`, and load that in the same headless browser. If a stock
Laravel 11 + Vite page initialises Alpine normally in this environment, the fault
is in this project's `bootstrap/app.php` / middleware stack or in the built
assets, **not** in the sandbox. Only after that is it worth bisecting
`resources/js/app.js`, since `import Alpine from 'alpinejs'; Alpine.start();` is
the textbook pattern and should not need bisecting.

**Workaround if it needs unsticking before the cause is known:** render the two
asset tags manually in `layouts/app.blade.php` instead of via `@vite` (dropping
`data-navigate-track`), and confirm with the same oracle — `window.Alpine`
becomes `object` and the FAQ answers open.

### Separate, unrelated machine issue (not a code defect)

`php artisan serve` with PHP 8.4.12 (as downloaded at
`C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64`) crashed on
`Call to undefined function Illuminate\Support\mb_split()` until `php.ini` was
created from `php.ini-development` with `extension_dir = "ext"` and
`extension=mbstring` enabled. The directory ships **no** `php.ini`, so a fresh
copy is unusable until this is done. The web app itself is unaffected (XAMPP's
Apache has its own ini) — this only hits CLI commands like `route:list`.

### Sandbox notes, so the next run is faster

The bundled PHP 8.2 (XAMPP) **cannot run this project at all**: `composer.json`
now requires PHP `>= 8.4.1` and `vendor/composer/platform_check.php` hard-fails on
anything older. The repo's `DB_CONNECTION=mysql` is served by a real MySQL on
`127.0.0.1:3306` — Docker is **not** installed in this sandbox, so the
`docker compose` instructions in `command-to-start-app.txt` do not apply here.

The `browser-automation` skill resolves `patchright` from the **highest-versioned**
`danielsanmedium.dscodegpt-*` extension directory (currently `3.24.71`) and needs
`chromium-1243` specifically; installing Chromium via a lower-numbered extension
leaves it looking for the wrong build. Install with the newest extension's own
`cli.js`. The 183–195 MB download also reliably outlives the 120s command
timeout, so it must be detached (`Start-Process`) and then polled.
