# HANDOFF — integration notes for LEVEL 5 (Customer Dashboard)

Read this before judging anything that "failed" during the LEVEL 5 pass. Every
failure recorded below was traced to the **harness or the environment**, not to
the application. Written during LEVEL 5; companion to `storage/app/HANDOFF.md`
(the QA-harness script-inertness finding) and `storage/app/ENVIRONMENT.md`
(the machine setup traps).

---

## 1. How LEVEL 5 was integrated (the archive is not uniformly newer)

`growza-level5-dashboard.zip` was unpacked to `storage/app/level5/` and diffed
with `storage/app/probe-level5-diff.ps1`:

| Bucket                       | Count |
| ---------------------------- | ----- |
| New files (absent from repo) | 19    |
| Identical                    | 141   |
| Divergent                    | 18    |

**This is the fourth level in a row whose archive is behind the repo.** Its own
`PROJECT_STATE.md` marks LEVELS 1–4 "🟡 Partially verified", and 5 of the 18
divergent files differ _only_ because the archive was authored on a tree where
LEVEL 3 did not exist. Never apply an archive wholesale here.

`storage/app/apply-level5.ps1` encodes every decision and **refuses to run** if a
divergent file has no explicit bucket — so a future level cannot silently fall
through the decision point. Outcome of the first apply: **19 added, 3 taken from
the archive, 9 kept from the repo.**

The 3 taken were `/auth.php` (the real dashboard routes),
`components/dropdown-item.blade.php` (a real bug fix) and `routes/web.php`
(see the correction below). Note that re-running the script is idempotent — it
reports `ADDED: 0 / ARCHIVE WON: 0` — but its "REPO KEPT" list then also names
files that were edited _in the repo after_ the first apply (e.g.
`config/database.php`, `CustomerDashboardMetrics.php`). That is expected and is
not a second decision point.

### Correction: `routes/web.php` was mis-filed, and the guard did not catch it

The first run listed `routes/web.php` in `$repoWins` **while its own comment said
the archive was "genuinely AHEAD … Apply it"**. The bucket won, so the repo kept
the stale file and the dead `require __DIR__.'/dashboard.php';   // LEVEL 5` line
survived — a `require` of a file that has never existed, because LEVEL 5
registers the dashboard in `auth.php`. Re-filed into `$archiveWins` and applied;
the dead line is gone and the `auth.php` comment now reads "(LEVEL 3, extended at
LEVEL 5)". Route count unchanged at 62, suite still 100 green.

**The lesson generalises:** the `refuses to run` guard catches an _unclassified_
file, but it cannot catch a _mis-classified_ one. A wrong bucket is silent — the
run prints it as "REPO KEPT" and looks exactly like a deliberate decision.
**Trust the entry, but verify the comment attached to it agrees with the
bucket.** A comment that argues for the opposite action is the tell.

### The five `route('name')` → `url('/name')` files: keep the repo's version

`nav-bar`, `cta-band`, `marketing/home`, `marketing/pricing`,
`marketing/contact` differ only in this. The archive switched to `url()` because
`route()` threw `RouteNotFoundException` on the tree it was authored against.
**In this repo those routes are defined and verified**, so `route()` is correct:
it fails loudly at build time if a route is renamed, instead of silently
degrading to a 404. Do not "restore" the archive's `url()` calls.

## 2. The false alarms (all closed — do not re-investigate)

### 2a. "The dashboard pages are missing content" — entity encoding in the probe

`qa-level5-http.mjs` first reported `/dashboard/referrals`, `/dashboard/support`,
`/dashboard/wallet` and `/dashboard/profile` as missing expected strings. The
markup was correct the whole time: **Blade escapes text with
`htmlspecialchars()`, so `isn't` is served as `isn&#039;t`** and a raw needle
never matches. The probe now decodes entities before matching.

This is the same category of mistake as the Alpine finding in
`storage/app/HANDOFF.md` — the measuring instrument, not the thing measured.
**Rule: before reporting missing content, dump the served bytes around the
match and look at them.**

### 2b. "Login 302s to /login" and a cascade of 419s — the fixture was deleted

Mid-session, every HTTP probe started failing at login and returning 419 on
every write. The cause was **`php artisan test` wiping the real MySQL rows**.
`phpunit.xml` pins `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` and
`tests/Pest.php` applies `RefreshDatabase` to every Feature/Integration test —
on one run it emptied the `growza` MySQL database, taking the
`level5-verify@example.test` user the probes log in as. Nothing was wrong with
the dashboard; the harness had nobody to log in as.

**Fix when it recurs:** re-run the fixture script directly —
`php storage/app/verify-level5-mysql.php`. (It is a standalone script, not an
Artisan command; `php artisan verify-level5-mysql.php` fails with "Command not
found".) A 419 on a write is _never_ the app's fault until the session is
proven live — see `ENVIRONMENT.md` §3, which records the same lesson from
LEVEL 3.

### 2c. A probe that was wrong three ways before it was right

`qa-level5-profile-http.mjs` went through three harness bugs, each of which
produced output that looked like an application defect:

1. `/<form[^>]*action="([^"]+)"/` returned whichever form is **first in the
   document** — a logout form — not the profile form. Anchor on the stable
   substring (`user/profile-information`).
2. The opening `<form ...>` tag contains no `<input>`, so scraping a CSRF token
   from it finds nothing. Slice opening tag → `</form>`.
3. `csrf()` takes the first `_token` on the page; the layout renders two logout
   forms before the main content. Scope the scrape to the form.

Each of those produced a 419 and a wall of "nothing persisted" failures. The
app was correct throughout.

### 2d. `Access denied for user 'growza'@'localhost'`

`config/database.php`'s `mysql` connection defaulted `username` to `growza`,
but the actual local MySQL 8 is XAMPP's passwordless `root`. Fixed: the
empty-config fallback now follows the driver. An explicit `DB_USERNAME` still
wins. Environment error that reads like a code defect.

## 3. `config/session.php` — third level running

The LEVEL 5 archive ships a literal `'connection' => 'session'` again (the same
500-causing line as the level-2 and level-3 archives). It is listed under
`$repoWins` in `apply-level5.ps1`. **Keep the repo's version.**
`storage/app/apply-level4.ps1` has the same guard.

## 4. What is genuinely open after LEVEL 5

- **No visual browser pass.** The mobile drawer's Alpine transitions and the
  sidebar↔drawer switch at `md` are asserted structurally (markup + `md:`
  classes present in both) but have not been seen at any of the LEVEL 58 widths
  (1440/1280/1024/768/430/390/375px). The Alpine driver limitation in
  `storage/app/HANDOFF.md` still applies, so this needs a human eye or a driver
  that executes document script.
- **The drawer has no focus trap.** Same pre-existing gap LEVEL 29 owns for the
  modal. A keyboard user can Tab out of the open drawer into the page behind it.
- **The email-verification resend surface was not delivered.** `PROJECT_STATE.md`
  had recorded that it "lands with the real dashboard at LEVEL 5". It did not
  happen — the dashboard redirects unverified users away before they can see it.
  Nothing is broken (no unverified user can reach the dashboard), but the
  convenience is still missing. Recorded honestly in `PROJECT_STATE.md` rather
  than quietly dropped.
- **`/dashboard/orders/{order}` is deliberately not registered** until LEVEL 7
  provides an `Order` model. A test asserts it 404s. Do not "fix" the 404.

## 5. Run the LEVEL 5 verification again

```powershell
# 1. Serve (PHP is not on PATH — see ENVIRONMENT.md)
Start-Process -FilePath "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" `
  -ArgumentList "artisan","serve","--host=127.0.0.1","--port=8125" -WindowStyle Hidden

# 2. Create/refresh the real-MySQL fixture and render all 10 screens
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" storage/app/verify-level5-mysql.php

# 3. Drive it over real HTTP (both exit 0 on PASS)
node storage/app/qa-level5-http.mjs
node storage/app/qa-level5-profile-http.mjs

# 4. The suite
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" artisan test
```

Expected: `100 passed (222 assertions)`, both probes `"verdict": "PASS"` with
`"issues": []`.

**Order matters.** Run the Pest suite **last**, or re-run step 2 afterwards —
step 4 is what deletes the fixture the probes need (§2b).
