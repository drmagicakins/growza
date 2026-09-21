# Apply LEVEL 5 (Customer Dashboard) from the unpacked archive.
#
# Precedent: this is the FOURTH level whose archive is NOT uniformly newer.
# Its own PROJECT_STATE.md marks LEVELS 1-4 "🟡 Partially verified" and its
# routes/web.php still carries the LEVEL 3 comment while the repo has moved
# past that (76 tests green, auth flows proven over HTTP). So, exactly as in
# storage/app/apply-level4.ps1, divergence needs an explicit per-file decision
# rather than a blind "archive wins".
#
# Classification was produced by storage/app/probe-level5-diff.ps1:
#   19 NEW files, 141 identical, 18 divergent.
#
# Every divergent file is listed below in exactly one bucket. If a file is
# divergent and appears in NO bucket the script refuses to run, so a future
# level cannot silently fall through this decision point.

$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level5\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

# --- Repo owns these entirely ------------------------------------------------
# Regenerate-from-scratch artefacts or files the repo has already specialised
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
    # history (including the LEVEL 4 pivot bug and the QA-harness finding).
    # The archive's copies are written from an unverified authoring tree and
    # would regress "Verified" back to "Partially verified" for LEVELS 1-4.
    "PROJECT_STATE.md", "CHANGELOG.md", "DESIGN_SYSTEM.md",
    "ARCHITECTURE.md", "DATABASE.md", "DEPLOYMENT.md", "README.md",
    "SECURITY.md", "API.md", "command-to-start-app.txt",
    # Local env template — the repo's is specialised for MySQL-on-127.0.0.1;
    # the archive's is the generic Docker template.
    ".env.example"
)

# --- Divergent but the ARCHIVE is behind the repo -> keep repo --------------
$repoWins = @(
    # config\session.php   : archive ships the 500-causing
    #                        'connection' => 'session' for the FOURTH time.
    #                        See PROJECT_STATE.md Known Issues.
    "config\session.php",
    # tests\Feature\AuthenticationTest.php : archive would delete the throttle
    #                        regression fix (the 429 assertion) and the
    #                        email+IP scoping test, and lose the referral-code
    #                        and terms tests added during LEVEL 3 integration.
    "tests\Feature\AuthenticationTest.php",
    # bootstrap\providers.php : differs only in comment wording.
    "bootstrap\providers.php"
)

# NOTE ON A MIS-FILED ENTRY (corrected 2026-09-21, after applying):
# `routes\web.php` was first listed here, in $repoWins, while its own comment
# said the archive was "genuinely AHEAD ... Apply it". The comment and the
# bucket contradicted each other, and the bucket won — so the repo kept the
# STALE version and the dead `// require __DIR__.'/dashboard.php';   // LEVEL 5`
# line (a require to a file that has never existed, because LEVEL 5 puts the
# dashboard routes in auth.php) survived. It belonged in $archiveWins. The
# bucket is a FALLBACK, so a mis-filed entry is silent: the run reports it as
# "REPO KEPT" and looks deliberate. Trust the entry, verify the comment.

# --- Divergent and the archive is genuinely AHEAD -> take it ----------------
$archiveWins = @(
    # Real LEVEL 5 wires: the dashboard routes + controller.
    "routes\auth.php",
    # Real bug fix: x-dropdown-item hardcoded type="button", which would make
    # the logout button inside a <form> a silent no-op (PROJECT_STATE LEVEL 5
    # "three real bugs caught"). Adds a `type` prop.
    "resources\views\components\dropdown-item.blade.php",
    # routes\web.php : genuinely ahead — drops the dead
    #                  `require __DIR__.'/dashboard.php';   // LEVEL 5` line (no
    #                  such file exists; LEVEL 5 registers the dashboard in
    #                  auth.php) and updates the auth.php comment from
    #                  "(LEVEL 3)" to "(LEVEL 3, extended at LEVEL 5)".
    #                  See the NOTE below on why this was mis-filed at first.
    "routes\web.php"
)

# --- Divergent, identical except route('name') -> url('/name') -------------- 
# The archive changed these to `url()` because `route()` would have thrown
# RouteNotFoundException on the tree it was authored against (LEVEL 3 routes
# absent there). In THIS repo those routes are defined and verified, so
# route() is the correct call: it breaks loudly at build time if a route name
# is ever renamed, instead of silently degrading to a 404. Keep the repo's
# route() calls.
$keptAsRouteCalls = @(
    "resources\views\components\nav-bar.blade.php",
    "resources\views\components\cta-band.blade.php",
    "resources\views\marketing\home.blade.php",
    "resources\views\marketing\pricing.blade.php",
    "resources\views\marketing\contact.blade.php"
)

$allDivergent = @(
    ".env.example", "_ide_helper.php", "bootstrap\cache\services.php",
    "bootstrap\providers.php", "CHANGELOG.md", "config\session.php",
    "DATABASE.md", "DESIGN_SYSTEM.md", "PROJECT_STATE.md",
    "resources\views\components\cta-band.blade.php",
    "resources\views\components\dropdown-item.blade.php",
    "resources\views\components\nav-bar.blade.php",
    "resources\views\marketing\contact.blade.php",
    "resources\views\marketing\home.blade.php",
    "resources\views\marketing\pricing.blade.php",
    "routes\auth.php", "routes\web.php",
    "tests\Feature\AuthenticationTest.php"
)

$decided = $neverTake + $repoWins + $archiveWins + $keptAsRouteCalls
$undecided = $allDivergent | Where-Object { $decided -notcontains $_ }
if ($undecided.Count -gt 0) {
    Write-Output "REFUSING TO RUN - divergent file(s) with no explicit decision:"
    foreach ($u in $undecided) { Write-Output "  $u" }
    exit 1
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
