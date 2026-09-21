param([string]$File)

$A = "c:\Users\DELL\growza\growza\storage\app\level5\growza"
$B = "c:\Users\DELL\growza\growza"

Write-Output ("#### " + $File)
foreach ($d in (Compare-Object (Get-Content (Join-Path $A $File)) (Get-Content (Join-Path $B $File)) -SyncWindow 2)) {
    $tag = if ($d.SideIndicator -eq "<=") { "ARCHIVE" } else { "REPO   " }
    Write-Output ("{0} | {1}" -f $tag, $d.InputObject)
}
