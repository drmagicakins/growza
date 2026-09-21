# Verify the LEVEL 4 apply: show added files, archive-won diffs, repo-kept diffs.
$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level4\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

$neverTake = @(
    ".env.example", ".gitignore", "artisan", "CHANGELOG.md", "PROJECT_STATE.md",
    "DESIGN_SYSTEM.md", "ARCHITECTURE.md", "DATABASE.md", "DEPLOYMENT.md",
    "README.md", "SECURITY.md", "API.md", "command-to-start-app.txt",
    "_ide_helper.php", "composer.json", "composer.lock", "package.json",
    "package-lock.json", "phpunit.xml", "vite.config.js", "tailwind.config.js",
    "postcss.config.js", "docker-compose.yml", "public\index.php",
    "bootstrap\cache\packages.php", "bootstrap\cache\services.php"
)

$diff = New-Object System.Collections.ArrayList

foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $rel = $f.FullName.Substring($srcRoot.Length + 1)
    if ($neverTake -contains $rel) { continue }
    $target = Join-Path $dstRoot $rel
    if (-not (Test-Path -LiteralPath $target)) { continue }
    $h1 = (Get-FileHash -LiteralPath $f.FullName -Algorithm MD5).Hash
    $h2 = (Get-FileHash -LiteralPath $target -Algorithm MD5).Hash
    if ($h1 -ne $h2) { [void]$diff.Add($rel) }
}

Write-Output "=== STILL-DIVERGENT FILES: $($diff.Count) ==="
foreach ($x in ($diff | Sort-Object)) {
    Write-Output "  --- $x ---"
    $a = (Get-Content (Join-Path $srcRoot $x)) -join "`n"
    $b = (Get-Content (Join-Path $dstRoot $x)) -join "`n"
    Compare-Object ($a -split "`n") ($b -split "`n") | ForEach-Object {
        Write-Output "    $($_.SideIndicator) $($_.InputObject)"
    }
}
