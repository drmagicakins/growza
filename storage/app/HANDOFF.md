# HANDOFF — QA harness investigation (Alpine "never initialises")

**Status: CLOSED. The application is not broken. The defect is in the QA harness.**
Last updated: same session as the LEVEL 1 real-browser QA. Newest finding first.

---

## 1. One-paragraph summary

For a full session, every real-browser QA probe reported that Alpine.js never
initialised: `window.Alpine === undefined`, all `x-` directives unprocessed, the
modal / mobile-nav / FAQ disclosure dead. That conclusion is **wrong**. The app's
bundle is valid, served correctly, and the FAQ disclosure opens when a human
clicks it. What is actually broken is the **browser-automation driver's page:
`<script>` elements in it never execute.** Every probe recorded its result
through `page.evaluate` on that same script-dead document, so all of them
inherited the same false negative. The bug was in the measuring instrument, not
the thing measured.

## 2. Confirmations that close the case

| Claim | How it was established |
|---|---|
| FAQ disclosure actually opens | **User clicked it in a real browser.** This is the primary evidence. |
| Bundle is valid and installs Alpine | Fetched the built bundle and executed its exact bytes by hand → `window.Alpine` = `object`. |
| Server emits the tag correctly | Served HTML read over `fetch` (outside the driver's DOM): one `<script type="module" src="/build/assets/app-D8MKG-Ji.js">`, 37991 bytes total. |
| The asset is genuinely served | HTTP 200, `content-type: application/javascript`, 55410 bytes. |
| Build is not stale | `public/build/manifest.json` hash matches the tag; server log shows both assets requested at sub-ms latency on every route. |
| Dev server is healthy | 0 console errors, 0 failed requests, `bodyChars 1464` on `/faq`. |

## 3. The proof that the harness is at fault

Two observed facts are **impossible in an ordinary document parse**. Together
they are the fingerprint of a DOM the driver reconstructed *after* parsing,
rather than one the browser built:

1. An init script (`page.addInitScript`) appended a marker node to `<body>` on
   `DOMContentLoaded` — **the node never materialised**, while
   `document.readyState` read `"complete"`.
2. `document.querySelectorAll("[x-cloak]")` returned **0**, while **59 `x-*`
   attributes** remained — with `x-cloak` still un-stripped in the class
   attribute of the source markup.

Note fact (1) also rules out the "page is paused by the debugger" theory: a
paused page cannot answer a timer probe. Timers and microtasks scheduled via
`page.evaluate` **do** fire, and DOM injection from `page.evaluate` **does**
work (`hasHtmlMarker: true`). Nothing is frozen — only document script
execution is inert.

## 4. Theories tested and falsified (do not re-test these)

- **Stale build / hash mismatch** — falsified. Manifest and tag agree.
- **Missing or blocked request** — falsified. 200 with a full body.
- **`data-navigate-track="reload"` on the tag stops the module** — falsified by
  `qa-module-test.mjs`. The attribute is present and the tag is well-formed; it
  simply is not executed.
- **The bundle throws** — falsified. Executing its bytes leaves `Alpine` as an
  object with no throw (`qa-final-verdict.mjs` → `bundleExecByHand`).
- **No script tag is served at all** — falsified. The served response *does*
  carry the module tag (this was the last open question, now answered).
- **`Alpine` is slow to initialise** — not applicable. `Alpine.start()` runs at
  module scope; if the script had executed at all the global would exist.
- **Debugger pause / frozen context** — falsified by the timer + microtask
  probes.

## 5. Rule for all future QA of this project

> `window.Alpine === undefined` and `"script DID NOT RUN"` in **any**
> `storage/app/qa-*.mjs` output are **not evidence about the application** until
> the harness is proven to execute document script. Assert on the served
> response body, or run the check outside this driver.

## 6. Probe inventory — what to keep, what to trust

| File | Use |
|---|---|
| `qa-final-verdict.mjs` | **The keeper.** Served-HTML vs live-DOM vs by-hand-execution in one context. Read its header comments for the verdict. |
| `qa-context-attrib.mjs` | **Keep.** The init-script + `[x-cloak]` contradiction that isolates harness vs app. |
| `qa-alpine-repro.mjs` | Kept as the record of the original repro; header comments now carry the verdict. |
| `qa-faq.mjs` | Logic is sound (asserts live `getComputedStyle`, not the `x-show` attribute) but **cannot pass under this driver** — clicks land on an inert document. Run it outside this harness, or keep only its `unprocessedDirectives` check. |
| `qa-alpine.mjs`, `qa-alpine-load.mjs`, `qa-script-exec.mjs`, `qa-module-test.mjs`, `qa-assets.mjs` | Stepping stones. Their *negative* Alpine results are superseded; `qa-assets.mjs` route/asset coverage is still useful on its own. |
| `qa-debugger-stall.mjs`, `qa-docwrite.mjs` | Stepping stones for the frozen-context and document-replaced theories, both falsified. Safe to delete. |

## 7. Docs that still contradict this finding (NOT yet corrected)

These were written during the investigation and record the wrong conclusion.
They are the reason this file exists — a future session would otherwise read them
and re-open a closed bug:

1. **`PROJECT_STATE.md` → "Known Issues"** — the first bullet, 🔴 OPEN,
   "Alpine.js never initialises … Do not treat LEVEL 1 as done until this is
   fixed." **This is a false defect report and should be removed or rewritten.**
2. **`DESIGN_SYSTEM.md` → "VERIFIED 2026-02-14 — real-browser QA"** — the whole
   FAIL section (the `data-navigate-track` suspicion, the "run `laravel new` and
   compare" next step, the manual-asset-tag workaround). These propose bisecting
   healthy code for a non-existent bug.
3. **`DESIGN_SYSTEM.md` → "VERIFIED 2026-02-14"** also states `x-cloak` drops to
   0 when the bundle is executed by hand *and* that it is 0 in the live page —
   consistent with this finding, but the surrounding FAIL narrative reads the
   opposite way.

## 8. Genuinely open items (real, unrelated to the above)

- **Modal focus trap** — a keyboard user can Tab out of an open modal into the
  page behind it. A real LEVEL 29 (accessibility) follow-up, flagged in
  `DESIGN_SYSTEM.md`, still open.
- **PHP 8.4 requirement** — `composer.json` needs `>= 8.4.1` (XAMPP's 8.2
  hard-fails); the bundled 8.4.12 CLI ships no `php.ini` and needs `mbstring`
  enabled before `php artisan` works.
- Nothing else from this investigation remains unresolved.

## 9. Run it again

```
php artisan serve --port=8125      # from repo root
node C:\Users\DELL\.codegpt\skills\browser-automation\browser.mjs \
  http://127.0.0.1:8125/faq --script ./storage/app/qa-final-verdict.mjs
```

Expected, and what it means: the script will *again* report
`alpine: "undefined"` in the live DOM while `bundleExecByHand` reports
`executed; Alpine=object`. That output is the harness signature, not a
regression. If the live-DOM `alpine` ever reads `object`, the driver has started
executing document script and the click-based probes become usable again.
