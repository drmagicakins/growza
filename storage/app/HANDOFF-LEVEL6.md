# HANDOFF — integration notes for LEVEL 6 (Service Catalogue)

Read this before judging anything that "failed" during the LEVEL 6 pass, and
before re-running the apply script. Companion to `storage/app/HANDOFF.md` (the
QA-harness script-inertness finding), `storage/app/HANDOFF-LEVEL5.md` and
`storage/app/ENVIRONMENT.md`.

---

## 1. How LEVEL 6 was integrated

`growza-level6-catalogue.zip` was expanded to `storage/app/level6/` and diffed
with `storage/app/probe-level6-diff.ps1`, then every divergent file was read
individually with `storage/app/probe-level6-detail.ps1`:

| Bucket                       | Count |
| ---------------------------- | ----- |
| New files (absent from repo) | 14    |
| Identical                    | 152   |
| Divergent                    | 26    |

**This is the fifth level in a row whose archive is behind the repo.** Its own
`PROJECT_STATE.md` marks LEVELS 1–5 "🟡 Partially verified".

`storage/app/apply-level6.ps1` encodes every decision and **refuses to run** if a
divergent file has no explicit bucket, **or if a file lands in two action
buckets** (a new guard — see §2). Outcome of the first apply: **14 added, 9 taken
from the archive, 10 kept from the repo** (11 repo-kept on re-run, because
`config/growza-marketing.php` was reclassified — §3).

## 2. New guard: a file may not be in two action buckets

LEVEL 5's post-mortem (`HANDOFF-LEVEL5.md` §1) recorded that `routes/web.php` was
listed in `$repoWins` while its own comment argued for `$archiveWins`, and the
bucket silently won. The refuse-to-run guard could not catch it because the file
_was_ classified — just wrongly.

`apply-level6.ps1` therefore also checks that every divergent file appears in at
most one of `$repoWins` / `$keptAsUrlCalls` / `$archiveWins` and aborts if not.
That catches a copy-paste duplicate but, as LEVEL 5 found, **it still cannot
catch a mis-classified file whose comment disagrees with its bucket.** Read the
comments.

## 3. The archive's config file had a FATAL PARSE ERROR

`config/growza-marketing.php` in the archive is syntactically invalid:

```
syntax error, unexpected token ",", expecting ";"
```

Its author deleted the `'audiences' => [` opening line (and left a duplicated
`/*`) while removing the `services`/`platforms` keys, leaving six orphaned array
entries. **Loading the file takes down the whole application** — it broke
`php artisan config:clear`, every route, and the homepage.

This is not latent: `resources/views/marketing/home.blade.php` still reads
`config('growza-marketing.audiences')` and iterates it, so an
`Undefined array key "audiences"` error would follow even if the syntax were
repaired by simply dropping the orphans.

**Resolution:** the LEVEL 6 change was applied to the repo's **own** copy by
hand (`services`/`platforms` removed, `audiences` restored and valid), and the
file was moved from `$archiveWins` to `$repoWins`.

### The lesson that generalises

The first re-run of `apply-level6.ps1` **re-copied the broken file over the
repaired one**, because a `$archiveWins` file is by definition re-taken on every
run. Any repo-side fix to a file in `$archiveWins` must be paired with
re-classifying that file into `$repoWins` — editing alone is not enough, and the
script will report the overwrite as a normal `ARCHIVE WON`. Re-run now reports
`ADDED: 0 / ARCHIVE WON: 0`.

Verified: `powershell -File` could not be used here (`powershell` is not on
PATH in this shell — invoke scripts with `& path\to\script.ps1` instead).

## 4. Two tests asserted the wrong thing about entity encoding

`CatalogueTest` and `DashboardCatalogueTest` both contained:

```php
->assertSee('Instagram & Facebook Ad Campaign', escape: false)
```

Blade escapes `&` to `&amp;`. `escape: false` searches the needle **raw**, so the
assertion could never match a correctly rendered page. Confirmed against the
served bytes before touching anything:

```
raw name: Instagram & Facebook Ad Campaign
escaped:  Instagram &amp; Facebook Ad Campaign
```

**The app was correct; the assertions were wrong.** Fixed by removing
`escape: false` on the two affected assertions, so Pest escapes the needle to
match the served bytes.

This is precisely the category of mistake recorded in `HANDOFF-LEVEL5.md` §2a
(the measuring instrument, not the thing measured) — and it is the same trap in
the opposite direction: LEVEL 5's probe under-decoded a response; LEVEL 6's
assertions over-`escape: false` a needle.

**Rule: when an `assertSee` involving `&`, `'`, `<` or `>` fails, dump the served
bytes around the expected match before concluding the view is wrong.**

## 5. The `url()` vs `route()` group — direction reversed from LEVEL 5

Four files (`cta-band`, `nav-bar`, `marketing/contact`, `marketing/pricing`)
differ from the archive **only** in `url('/login'|'/register')` vs
`route('login'|'register')`.

At LEVEL 5 the archive proposed `route()` for names that _did_ exist in the repo,
so `route()` was the better call. **Here the direction is reversed:** LEVEL 6 did
not set out to touch auth links, and the repo's `url()` form is what every
verified HTTP probe has been run against at 118 tests green. Kept the repo's
version. Changing these is a separate, deliberate task if it is ever wanted.

**Do not "restore" the archive's `route()` calls in those four files** without a
decision that explicitly covers them.

Note `marketing/home.blade.php` also flips `url()`→`route()`, but it carries a
**real** LEVEL 6 rewrite as well (platforms and services now come from the DB as
objects), so it belongs in `$archiveWins`. Classifying it only by its `url()`
diff would have silently dropped the catalogue from the homepage. That is
exactly the mis-filing class §2 warns about.

## 6. `config/marketing.php` is NOT dead code — do not delete it

`config/marketing.php` and `config/growza-marketing.php` both exist and **both
are live**:

- `growza-marketing` — read by the marketing views/controllers
  (`audiences`, `faqs`, `process`, `pricing`, `testimonials`). Its
  `services`/`platforms` keys were removed at LEVEL 6.
- `marketing` — read by `app/Http/Requests/ContactRequest.php`
  (`config('marketing.contact.subjects')`) and
  `resources/views/components/site-footer.blade.php`
  (`config('marketing.contact.email')`).
- `app/Http/Controllers/Web/PageController.php` reads `config('marketing.*')`
  heavily but **no route references it** — `routes/marketing.php` uses
  `MarketingPageController`. So `PageController` is dead code holding a second,
  divergent copy of the marketing content.

Consolidating the two config files is a real task; it is not a one-line delete.

## 7. Route count: 62, and `services.show` is genuinely registered

LEVEL 5 recorded 62 routes; LEVEL 6 adds `services.show`, and the count is still 62. The LEVEL 5 archive did **not** contain `services.show`, so the LEVEL 5
figure was itself off by one or counted something transient. Verified the
current state two ways rather than trusting the footer:

```
route:list --name=services.show   -> Showing [1] routes
in_array('services.show', getRoutes() names) -> YES
```

One transient gotcha: a single long `php artisan tinker --execute` one-liner
initially reported `no` for this, due to how PowerShell passes the quoting
through. Re-run in a shorter form it reported `YES`. If a tinker one-liner
contradicts `route:list`, suspect the shell quoting before the app.

## 8. What is genuinely open after LEVEL 6

- **No visual browser pass** at any of the LEVEL 58 widths
  (1440/1280/1024/768/430/390/375px). Same standing gap as LEVELS 1/5. The
  catalogue is asserted over HTTP, structurally — not seen.
- **No admin CRUD for the catalogue.** `manage_services` (LEVEL 4's permission)
  stays unused until LEVEL 16 gives someone a UI. Deliberate.
- **`PageController` is dead code** — see §6.
- **No quantity-based pricing column exists**, by design. See the `services`
  migration docblock. Do not "add a quantity field" without re-reading the
  Acceptable Use Policy constraint.
- **`/dashboard/orders/{order}` is still deliberately unregistered** until
  LEVEL 7 provides an `Order` model. A test asserts it 404s.

## 9. Re-running the LEVEL 6 verification

**Preferred — one command.** `serve-and-qa.ps1` starts the server if it is not
already up, _polls the port until it is genuinely listening_, runs the probe, and
exits with the probe's status so it still gates a build:

```powershell
& storage\app\serve-and-qa.ps1              # serve (if needed) + probe
& storage\app\serve-and-qa.ps1 -Seed        # also migrate --force + seed the catalogue
& storage\app\serve-and-qa.ps1 -Stop        # stop the server afterwards
```

This exists because the failure below is an **ordering/timing** fault, and a
document cannot enforce sequencing — three handoffs each carried a hand-rolled
`Start-Process` snippet, two without `-WorkingDirectory`, and the trap still
fired. A doc cannot poll a port; the script can.

Then run the suite **LAST** — it wipes the MySQL fixture (HANDOFF-LEVEL5.md §2b):

```powershell
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" artisan test
```

<details>
<summary>The individual steps, if you need to drive it by hand</summary>

```powershell
# 1. Serve (PHP is not on PATH — see ENVIRONMENT.md)
# -WorkingDirectory is MANDATORY: artisan must run with the repo as its CWD.
# Without it the process dies instantly and the next command reports
# "Unable to connect" — which looks exactly like "the server was never started".
Start-Process -FilePath "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" `
  -ArgumentList "artisan","serve","--host=127.0.0.1","--port=8125" `
  -WorkingDirectory "c:\Users\DELL\growza\growza" -WindowStyle Hidden

# 2. WAIT for the port before probing — do not just sleep a fixed number of
#    seconds. `artisan serve` spawns a CHILD php -S that actually holds the port.
#    Poll it instead:
while (-not (Get-NetTCPConnection -LocalPort 8125 -State Listen -ErrorAction SilentlyContinue)) {
  Start-Sleep -Milliseconds 250
}

# 3. Migrate + seed the catalogue into the real MySQL database
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" artisan migrate --force
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" artisan db:seed --class=CatalogueSeeder --force

# 4. Drive it over real HTTP (exits 0 on PASS)
node storage/app/qa-level6-http.mjs
```

</details>

Expected: `118 passed (268 assertions)`, probe `"verdict": "PASS"` with
`"issues": []`.

### If the probe prints `FAIL server not reachable` / `fetch failed`

The probe ran before the server was up, or the server died at startup. Confirm
the port is actually listening before touching anything in the app:

```powershell
Get-NetTCPConnection -LocalPort 8125 -State Listen -ErrorAction SilentlyContinue
```

No output plus `ECONNREFUSED` means nothing is bound to the port — an
environment/ordering fault, **not** a probe or application defect.

Two causes seen during the LEVEL 6 pass, both environmental:

1. **`-WorkingDirectory` omitted** from `Start-Process`. `artisan` then runs
   without the repo as CWD and exits immediately, and the next command reports
   `Unable to connect to the remote server` — indistinguishable from "the server
   was never started".
2. **Launched through a foreground/background tool wrapper** instead of a
   detached process. `artisan serve` is long-lived; a wrapper that kills it on
   timeout leaves the port closed. Use `Start-Process -WindowStyle Hidden`.

Order matters: **serve first, then probe.**

The apply script is safe to re-run and should report
`ADDED: 0 / ARCHIVE WON: 0`.
