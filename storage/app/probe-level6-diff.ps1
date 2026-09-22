# Read-only probe: classify every file in the unpacked LEVEL 6 archive.
#
#   NEW       - not present in the repo -> candidate to add
#   IDENTICAL - same bytes as the repo -> nothing to do
#   DIVERGENT - present in both, differs -> needs an explicit per-file decision
#
# Writes nothing. Prints to stdout only.

$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level6\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

$new = New-Object System.Collections.ArrayList
$same = New-Object System.Collections.ArrayList
$diff = New-Object System.Collections.ArrayList

foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $rel = $f.FullName.Substring($srcRoot.Length + 1)
    $target = Join-Path $dstRoot $rel

    if (-not (Test-Path -LiteralPath $target)) {
        [void]$new.Add($rel)
        continue
    }

    $h1 = (Get-FileHash -LiteralPath $f.FullName -Algorithm MD5).Hash
    $h2 = (Get-FileHash -LiteralPath $target -Algorithm MD5).Hash

    if ($h1 -eq $h2) { [void]$same.Add($rel) } else { [void]$diff.Add($rel) }
}

Write-Output "=== NEW (absent from repo): $($new.Count) ==="
foreach ($x in ($new | Sort-Object)) { Write-Output "  $x" }
Write-Output ""
Write-Output "=== IDENTICAL: $($same.Count) ==="
Write-Output ""
Write-Output "=== DIVERGENT: $($diff.Count) ==="
foreach ($x in ($diff | Sort-Object)) { Write-Output "  $x" }

# The repo may also contain level-6 work the archive never had (a file already
# integrated by hand, or a repo-side fix). Surface those too so a file cannot be
# silently absent from the decision.
Write-Output ""
Write-Output "=== REPO-ONLY CANDIDATES (in repo, not in archive) - informational ==="
$archiveAll = @{}
foreach ($f in (Get-ChildItem $srcRoot -Recurse -File)) {
    $archiveAll[$f.FullName.Substring($srcRoot.Length + 1)] = $true
}
$watched = @(
    "app\Domain\Catalogue", "resources\views\marketing\service-detail.blade.php",
    "database\migrations\2026_02_01_000000_create_platforms_table.php"
)
foreach ($w in $watched) {
    $p = Join-Path $dstRoot $w
    if (Test-Path $p) {
        foreach ($f in (Get-ChildItem $p -Recurse -File -ErrorAction SilentlyContinue)) {
            $rel = $f.FullName.Substring($dstRoot.Length + 1)
            if (-not $archiveAll.ContainsKey($rel)) { Write-Output "  $rel" }
        }
    }
}
