$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level4\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

# Files the repo owns - never take from a level archive.
$neverTake = @(
    ".env.example", ".gitignore", "artisan", "CHANGELOG.md", "PROJECT_STATE.md",
    "DESIGN_SYSTEM.md", "ARCHITECTURE.md", "DATABASE.md", "DEPLOYMENT.md",
    "README.md", "SECURITY.md", "API.md", "command-to-start-app.txt",
    "_ide_helper.php", "composer.json", "composer.lock", "package.json",
    "package-lock.json", "phpunit.xml", "vite.config.js", "tailwind.config.js",
    "postcss.config.js", "docker-compose.yml", "public\index.php",
    "bootstrap\cache\packages.php", "bootstrap\cache\services.php"
)

$new = New-Object System.Collections.ArrayList
$different = New-Object System.Collections.ArrayList
$identical = New-Object System.Collections.ArrayList
$skipped = New-Object System.Collections.ArrayList

foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $rel = $f.FullName.Substring($srcRoot.Length + 1)

    if ($neverTake -contains $rel) { [void]$skipped.Add($rel); continue }

    $target = Join-Path $dstRoot $rel

    if (-not (Test-Path -LiteralPath $target)) { [void]$new.Add($rel); continue }

    $h1 = (Get-FileHash -LiteralPath $f.FullName -Algorithm MD5).Hash
    $h2 = (Get-FileHash -LiteralPath $target -Algorithm MD5).Hash
    if ($h1 -eq $h2) { [void]$identical.Add($rel) } else { [void]$different.Add($rel) }
}

Write-Output "=== NEW: $($new.Count) ==="
foreach ($x in ($new | Sort-Object)) { Write-Output "  $x" }
Write-Output ""
Write-Output "=== DIVERGENT: $($different.Count) ==="
foreach ($x in ($different | Sort-Object)) {
    $sz = (Get-Item (Join-Path $dstRoot $x)).Length
    $asz = (Get-Item (Join-Path $srcRoot $x)).Length
    Write-Output "  $x  [archive $asz / repo $sz]"
}
Write-Output ""
Write-Output "=== IDENTICAL: $($identical.Count) ==="
