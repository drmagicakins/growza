# Apply LEVEL 6 (Service Catalogue) from the unpacked archive.
#
# Precedent: this is the FIFTH level whose archive is NOT uniformly newer.
# Its own PROJECT_STATE.md marks LEVELS 1-5 "🟡 Partially verified" and it ships
# stale PROJECT_STATE/CHANGELOG copies that would regress the repo's verified
# history. So, exactly as apply-level4/5.ps1, divergence needs an explicit
# per-file decision rather than a blind "archive wins".
#
# Classification produced by storage/app/probe-level6-diff.ps1 + -detail.ps1:
#   14 NEW files, 152 identical, 26 divergent.
#
# Every divergent file is listed below in exactly one bucket. If a divergent
# file appears in NO bucket the script REFUSES TO RUN, so a future level cannot
# silently fall through this decision point.
#
# LESSON CARRIED FROM LEVEL 5 (see HANDOFF-LEVEL5.md §1): the refuse-to-run
# guard catches an *unclassified* file but cannot catch a *mis-classified* one.
# So every entry below states the evidence that justifies its bucket, and the
# comment must agree with the bucket it sits in.

$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level6\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

# --- Repo owns these entirely ------------------------------------------------
# Regenerate-from-scratch artefacts, or files the repo has already specialised
# beyond the archive. Never overwrite from a level archive.
$neverTake = @(
    # Web-server / framework entry points
    "public\index.php",
    "artisan",
    # Dependency + build manifests (repo has a real composer install behind it)
    "composer.json", "composer.lock", "package.json", "package-lock.json",
    "vite.config.js", "tailwind.config.js", "postcss.config.js",
    "phpunit.xml", "docker-compose.yml", ".gitignore",
    # Cached/derived
    "bootstrap\cache\packages.php", "bootstrap\cache\services.php",
    "_ide_helper.php",
    # Narrative documents — the repo's versions record the real, verified
    # history. The archive's copies are written from an unverified authoring
    # tree (its own PROJECT_STATE marks LEVELS 1-5 "🟡 Partially verified") and
    # would regress them. LEVEL 6's narrative is written into the repo docs by
    # hand at the end of this integration, from what actually ran.
    "PROJECT_STATE.md", "CHANGELOG.md", "DESIGN_SYSTEM.md",
    "ARCHITECTURE.md", "DATABASE.md", "DEPLOYMENT.md", "README.md",
    "SECURITY.md", "API.md", "command-to-start-app.txt",
    # Local env template — the repo's is specialised for MySQL-on-127.0.0.1
    # with a real APP_KEY; the archive's is the generic Docker template with
    # DB_HOST=postgres.
    ".env.example"
)

# --- Divergent but the ARCHIVE is behind the repo -> keep repo --------------
$repoWins = @(
    # config\session.php : archive ships the 500-causing
    #   'connection' => 'session' for the FIFTH level running. See
    #   PROJECT_STATE.md Known Issues.
    "config\session.php",
    # config\database.php : repo fixed the MySQL username fallback ('root' for
    #   mysql, 'growza' elsewhere). Archive still says 'growza', which produces
    #   "Access denied for user 'growza'@'localhost'" on this machine's XAMPP
    #   MySQL. Verified fix, documented in HANDOFF-LEVEL5.md §2d.
    "config\database.php",
    # tests\Feature\AuthenticationTest.php : archive would delete the throttle
    #   regression fix (the correct 429 assertion) and the email+IP scoping
    #   test added during LEVEL 3 integration. 36 repo-only lines.
    "tests\Feature\AuthenticationTest.php",
    # bootstrap\providers.php : differs only in comment wording (4 repo-only
    #   lines, 0 archive-only substantive lines). No functional difference.
    "bootstrap\providers.php",
    # app\Domain\Reporting\DTOs\CustomerDashboardMetrics.php : archive reverts
    #   `) {}` (pint-clean single-line empty constructor body) back to the
    #   multi-line `) {\n}` that vendor/bin/pint --test FLAGGED during LEVEL 5.
    #   Archive-only 2 lines, both whitespace.
    "app\Domain\Reporting\DTOs\CustomerDashboardMetrics.php",
    # tests\Feature\CustomerDashboardTest.php : archive drops the
    #   `use App\Domain\Identity\Enums\RoleName;` import and reverts to a fully
    #   qualified inline reference, and changes the put() import style. Cosmetic
    #   only — repo version is the one pint passes and the suite runs.
    "tests\Feature\CustomerDashboardTest.php",
    # config\growza-marketing.php : the archive's copy has a FATAL PARSE ERROR.
    #   Its author deleted the `'audiences' => [` opening line (and left a
    #   duplicated `/*`) while removing the `services`/`platforms` keys, so the
    #   six audience entries at lines 41-47 are orphaned array values and the
    #   file dies with `syntax error, unexpected token ",", expecting ";"`.
    #   The homepage view still reads config('growza-marketing.audiences'), so
    #   taking the archive blindly breaks `php artisan config:clear`, every
    #   route, and the homepage.
    #
    #   The repo's copy HAS still taken the LEVEL 6 change — `services` and
    #   `platforms` are gone, and `audiences` is present and valid. Keeping it
    #   here also makes the script idempotent: a second run cannot re-introduce
    #   the parse error (which it did, on the first re-run).
    "config\growza-marketing.php"
)

# --- Divergent, identical except the repo uses url('/x') and archive route('x') --
# These 4 files differ ONLY in `url('/login'|'/register')` vs
# `route('login'|'register')`. The archive changed them because route() would
# have thrown RouteNotFoundException on the tree it was authored against.
#
# IMPORTANT — this is NOT the LEVEL 5 case. In LEVEL 5 the archive proposed
# route() for names that DID exist in the repo, so route() was the better call
# and the repo's url() looked like the regression. Here the direction is
# reversed: the repo deliberately uses url() for `login`/`register`/`home`.
# Fortify registers those names at runtime, and the repo's url() form is the one
# every verified HTTP probe (qa-level5-http.mjs, qa-login-http.mjs,
# qa-register-http.mjs) has been run against, at 100 tests / 62 routes green.
# Changing them is NOT part of LEVEL 6's scope (catalogue only) and would touch
# auth links the level did not set out to touch. Keep the repo's version.
$keptAsUrlCalls = @(
    "resources\views\components\cta-band.blade.php",
    "resources\views\components\nav-bar.blade.php",
    "resources\views\marketing\contact.blade.php",
    "resources\views\marketing\pricing.blade.php"
)

# --- Divergent and the archive is genuinely AHEAD -> take it ----------------
# NOTE: `resources\views\marketing\home.blade.php` is deliberately NOT in the
# group above, even though it also flips url()->route(). It carries a real
# LEVEL 6 rewrite as well (platforms and services now come from the DB as
# objects), so it belongs here. Classifying it only by its url() diff would
# have silently dropped the catalogue wiring from the homepage.
$archiveWins = @(
    # THE LEVEL 6 WIRING — the whole point of this level.

    # routes\marketing.php : adds
    #   Route::get('/services/{service:slug}', ...)->name('services.show')
    # Routes the catalogue to a real service detail page. Archive-only 1 line.
    "routes\marketing.php",

    # app\Http\Controllers\Web\MarketingPageController.php : replaces the
    #   config('growza-marketing.services') reads with Eloquent queries
    #   (Platform::active(), ServiceCategory::active()->with('services'),
    #   Service::active()->take(6)) and adds serviceShow(). Archive-only 34.
    "app\Http\Controllers\Web\MarketingPageController.php",

    # app\Http\Controllers\Web\SitemapController.php : appends active service
    #   detail URLs to the sitemap from the database instead of a stale
    #   hand-maintained list. Archive-only 13.
    "app\Http\Controllers\Web\SitemapController.php",

    # app\Http\Controllers\Web\Dashboard\DashboardController.php : services()
    #   now passes the real catalogue to the view instead of reading config.
    #   Archive-only 8. (8 archive vs 5 repo lines = real controller rewrite,
    #   not a comment tweak.)
    "app\Http\Controllers\Web\Dashboard\DashboardController.php",

    # config\growza-marketing.php : REMOVES the `services` and `platforms`
    #   keys — verified by grep, 0 matches for either key in the archive's copy
    #   (vs 8 in the repo's). This is LEVEL 6's explicit requirement: the DB is
    #   now the single source, not the config array.
    #
    #   NOTE (moved 2026-09-22): this file is NOT taken from the archive. Its
    #   `services`/`platforms` removal was applied to the repo's own copy by
    #   hand, because the archive's copy is syntactically invalid (see the entry
    #   in $repoWins). Taking it here would break the app AND make this script
    #   non-idempotent. Left recorded here so the decision is not re-litigated.
    # "config\growza-marketing.php",

    # database\seeders\DatabaseSeeder.php : adds CatalogueSeeder::class.
    #   Without this the catalogue tables are empty after migrate --seed.
    "database\seeders\DatabaseSeeder.php",

    # resources\views\marketing\services.blade.php : rewritten to iterate
    #   $categories -> $category->services as objects ($service->name) instead
    #   of the removed config array. Archive-only 33.
    "resources\views\marketing\services.blade.php",

    # resources\views\marketing\home.blade.php : $platforms from the DB, and
    #   $services as objects with a link to the new detail page. Archive-only 14.
    "resources\views\marketing\home.blade.php",

    # resources\views\dashboard\services.blade.php : @forelse ($services ...)
    #   with object access + category badge, replacing
    #   @foreach (config('growza-marketing.services') as $service). Archive-only 12.
    "resources\views\dashboard\services.blade.php"
)

$allDivergent = @(
    ".env.example", "_ide_helper.php",
    "app\Domain\Reporting\DTOs\CustomerDashboardMetrics.php",
    "app\Http\Controllers\Web\Dashboard\DashboardController.php",
    "app\Http\Controllers\Web\MarketingPageController.php",
    "app\Http\Controllers\Web\SitemapController.php",
    "bootstrap\cache\services.php", "bootstrap\providers.php",
    "CHANGELOG.md", "config\database.php", "config\growza-marketing.php",
    "config\session.php", "DATABASE.md", "database\seeders\DatabaseSeeder.php",
    "DESIGN_SYSTEM.md", "PROJECT_STATE.md",
    "resources\views\components\cta-band.blade.php",
    "resources\views\components\nav-bar.blade.php",
    "resources\views\dashboard\services.blade.php",
    "resources\views\marketing\contact.blade.php",
    "resources\views\marketing\home.blade.php",
    "resources\views\marketing\pricing.blade.php",
    "resources\views\marketing\services.blade.php",
    "routes\marketing.php",
    "tests\Feature\AuthenticationTest.php",
    "tests\Feature\CustomerDashboardTest.php"
)

$decided = $neverTake + $repoWins + $keptAsUrlCalls + $archiveWins
$undecided = $allDivergent | Where-Object { $decided -notcontains $_ }
if ($undecided.Count -gt 0) {
    Write-Output "REFUSING TO RUN - divergent file(s) with no explicit decision:"
    foreach ($u in $undecided) { Write-Output "  $u" }
    exit 1
}

# Guard against a file landing in two buckets (the LEVEL 5 mis-filing class of
# bug: a file in $repoWins whose comment argued for $archiveWins went silently
# the wrong way). A file must be in exactly one action bucket.
$actionBuckets = @{
    'repoWins' = $repoWins
    'keptAsUrlCalls' = $keptAsUrlCalls
    'archiveWins' = $archiveWins
}
foreach ($rel in $allDivergent) {
    $in = @()
    foreach ($name in $actionBuckets.Keys) {
        if ($actionBuckets[$name] -contains $rel) { $in += $name }
    }
    if ($in.Count -gt 1) {
        Write-Output "REFUSING TO RUN - '$rel' is in MULTIPLE action buckets: $($in -join ', ')"
        exit 1
    }
}

$added = New-Object System.Collections.ArrayList
$took = New-Object System.Collections.ArrayList
$kept = New-Object System.Collections.ArrayList

foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $rel = $f.FullName.Substring($srcRoot.Length + 1)

    if ($neverTake -contains $rel) { continue }

    $target = Join-Path $dstRoot $rel

    if (Test-Path -LiteralPath $target) {
        $h1 = (Get-FileHash -LiteralPath $f.FullName -Algorithm MD5).Hash
        $h2 = (Get-FileHash -LiteralPath $target -Algorithm MD5).Hash
        if ($h1 -eq $h2) { continue }

        if ($archiveWins -contains $rel) {
            $dir = Split-Path $target -Parent
            if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
            Copy-Item -LiteralPath $f.FullName -Destination $target -Force
            [void]$took.Add($rel)
            continue
        }

        [void]$kept.Add($rel)
        continue
    }

    $dir = Split-Path $target -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Copy-Item -LiteralPath $f.FullName -Destination $target -Force
    [void]$added.Add($rel)
}

Write-Output "=== ADDED (new): $($added.Count) ==="
foreach ($x in ($added | Sort-Object)) { Write-Output "  $x" }
Write-Output ""
Write-Output "=== ARCHIVE WON: $($took.Count) ==="
foreach ($x in ($took | Sort-Object)) { Write-Output "  $x" }
Write-Output ""
Write-Output "=== REPO KEPT: $($kept.Count) ==="
foreach ($x in ($kept | Sort-Object)) { Write-Output "  $x" }