# Read-only: for each DIVERGENT file, show the actual diff so the bucket can be
# justified by evidence rather than by filename. Writes nothing.

$srcRoot = "c:\Users\DELL\growza\growza\storage\app\level6\growza"
$dstRoot = "c:\Users\DELL\growza\growza"

$divergent = @(
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

foreach ($rel in $divergent) {
    $a = Join-Path $srcRoot $rel
    $b = Join-Path $dstRoot $rel
    $d = Compare-Object (Get-Content -LiteralPath $a) (Get-Content -LiteralPath $b)
    $archLines = ($d | Where-Object { $_.SideIndicator -eq '<=' }).Count
    $repoLines = ($d | Where-Object { $_.SideIndicator -eq '=>' }).Count
    Write-Output "### $rel   [archive-only: $archLines | repo-only: $repoLines]"
    $d | Select-Object -First 12 | ForEach-Object {
        $side = if ($_.SideIndicator -eq '<=') { "ARCH" } else { "REPO" }
        Write-Output ("    {0} | {1}" -f $side, $_.InputObject)
    }
    Write-Output ""
}