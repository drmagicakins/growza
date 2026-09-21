# Apply LEVEL 4 (RBAC implementation).
#
# Third level in a row where the archive is NOT uniformly newer. Its own
# PROJECT_STATE.md marks every prior level "partially verified" and its
# routes/web.php has `require auth.php` COMMENTED OUT -- i.e. the archive was
# authored on a tree where LEVEL 3 was still incomplete. This repo has since
# passed beyond that (59 tests, verified auth flows), so several of the
# "divergent" files are simply OLDER here and must not win.
#
# Per-file decisions, all explicit:

$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level4\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

# Repo owns these entirely.
$neverTake = @(
    ".env.example", ".gitignore", "artisan", "CHANGELOG.md", "PROJECT_STATE.md",
    "DESIGN_SYSTEM.md", "ARCHITECTURE.md", "DATABASE.md", "DEPLOYMENT.md",
    "README.md", "SECURITY.md", "API.md", "command-to-start-app.txt",
    "_ide_helper.php", "composer.json", "composer.lock", "package.json",
    "package-lock.json", "phpunit.xml", "vite.config.js", "tailwind.config.js",
    "postcss.config.js", "docker-compose.yml", "public\index.php",
    "bootstrap\cache\packages.php", "bootstrap\cache\services.php"
)

# Divergent, but the ARCHIVE is behind the repo -> keep repo.
#   config\session.php                  : archive ships the 500-causing
#                                         'connection' => 'session' AGAIN (3rd time)
#   tests\Feature\AuthenticationTest.php: archive would DELETE the throttle
#                                         regression fix + the email/IP scoping test
#   bootstrap\providers.php             : differs only in comment wording
#   routes\web.php                      : archive has require auth.php COMMENTED OUT
$repoWins = @(
    "config\session.php",
    "tests\Feature\AuthenticationTest.php",
    "bootstrap\providers.php",
    "routes\web.php"
)

# Divergent and archive genuinely ahead -> take it.
#   app\Providers\AuthServiceProvider.php : real LEVEL 4 (policies registration)
#   routes\admin.php                      : the gated /admin route + full chain
#   app\Domain\Auth\Actions\CreateNewUser.php : assigns the Customer role
#   tests\Pest.php                        : seeds roles before every Feature test
#   bootstrap\app.php                     : needs review after copy
$archiveWins = @(
    "app\Providers\AuthServiceProvider.php",
    "routes\admin.php",
    "app\Domain\Auth\Actions\CreateNewUser.php",
    "tests\Pest.php",
    "bootstrap\app.php"
)

$added = New-Object System.Collections.ArrayList
$took = New-Object System.Collections.ArrayList
$kept = New-Object System.Collections.ArrayList
$undecided = New-Object System.Collections.ArrayList

foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $rel = $f.FullName.Substring($srcRoot.Length + 1)

    if ($neverTake -contains $rel) { continue }

    $target = Join-Path $dstRoot $rel

    if (Test-Path -LiteralPath $target) {
        $h1 = (Get-FileHash -LiteralPath $f.FullName -Algorithm MD5).Hash
        $h2 = (Get-FileHash -LiteralPath $target -Algorithm MD5).Hash
        if ($h1 -eq $h2) { continue }

        if ($repoWins -contains $rel) { [void]$kept.Add($rel); continue }
        if ($archiveWins -contains $rel) {
            $dir = Split-Path $target -Parent
            if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
            Copy-Item -LiteralPath $f.FullName -Destination $target -Force
            [void]$took.Add($rel)
            continue
        }
        [void]$undecided.Add($rel)
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
Write-Output "=== REPO KEPT (archive would regress): $($kept.Count) ==="
foreach ($x in ($kept | Sort-Object)) { Write-Output "  $x" }
Write-Output ""
Write-Output "=== UNDECIDED, LEFT ALONE: $($undecided.Count) ==="
foreach ($x in ($undecided | Sort-Object)) { Write-Output "  $x" }
