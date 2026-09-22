<#
    serve-and-qa.ps1 -- start the Growza dev server (if it is not already up) and
    run the real-HTTP QA probe against it.

        & storage\app\serve-and-qa.ps1              # serve + probe
        & storage\app\serve-and-qa.ps1 -Seed        # also migrate --force + seed
        & storage\app\serve-and-qa.ps1 -Stop        # kill the server afterwards

    WHY THIS EXISTS
    ---------------
    The LEVEL 6 pass failed with `fetch failed (ECONNREFUSED)` because the probe
    ran before the server was listening. That is an ORDERING/TIMING fault, and
    no amount of documentation can enforce sequencing -- three separate handoffs
    each carried a hand-rolled `Start-Process` snippet, two of them without
    `-WorkingDirectory`, and the trap still fired. A doc cannot poll a port.

    Two concrete failure modes this removes, both seen for real:

      1. `-WorkingDirectory` omitted. `artisan` then runs without the repo as its
         CWD and exits immediately; the next command reports "Unable to connect",
         which is indistinguishable from "the server was never started".
      2. The probe fired immediately after launch, before the port was bound.
         Fixed by POLLING the port until it is genuinely listening.

    Exits with the probe's exit code, so it still gates a build.

    The server is LEFT RUNNING by default (harmless, and saves a cold start on
    the next run). Pass -Stop to kill it when the probe finishes.
#>

[CmdletBinding()]
param(
    # Run `migrate --force` and seed the catalogue before probing. OFF by
    # default: it writes to the real MySQL database, which should never be a
    # side effect of a command you might run casually.
    [switch] $Seed,

    # Stop the server after the probe. OFF by default.
    [switch] $Stop,

    # Seconds to wait for the port to bind before giving up.
    [int] $TimeoutSeconds = 15
)

$ErrorActionPreference = 'Stop'

$RepoRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$Port     = 8125
$Base     = "http://127.0.0.1:$Port"

# PHP is NOT on PATH (ENVIRONMENT.md S1), and XAMPP's PHP 8.2 cannot run this
# project at all -- vendor/composer/platform_check.php hard-fails below 8.4.1.
$Php = 'C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe'

function Test-PortOpen {
    param([int] $P)
    $conn = Get-NetTCPConnection -LocalPort $P -State Listen -ErrorAction SilentlyContinue
    return [bool] $conn
}

if (-not (Test-Path $Php)) {
    Write-Host "FAIL  PHP not found at $Php" -ForegroundColor Red
    Write-Host "      See storage/app/ENVIRONMENT.md S1." -ForegroundColor Red
    exit 1
}

Write-Host "==> Repo root: $RepoRoot"

# ---------------------------------------------------------------- 1. the server
if (Test-PortOpen $Port) {
    Write-Host "==> Port $Port already serving -- reusing it (no duplicate server)."
    $startedHere = $false
}
else {
    Write-Host "==> Starting server on $Base ..."
    $proc = Start-Process -FilePath $Php `
        -ArgumentList 'artisan', 'serve', "--host=127.0.0.1", "--port=$Port" `
        -WorkingDirectory $RepoRoot `
        -WindowStyle Hidden -PassThru

    # POLL -- this is the whole point of the script. A fixed Start-Sleep is the
    # bug: the bind time varies, so any constant is either wasteful or too short.
    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    $bound    = $false
    while ((Get-Date) -lt $deadline) {
        if (Test-PortOpen $Port) { $bound = $true; break }
        Start-Sleep -Milliseconds 250
    }

    if (-not $bound) {
        Write-Host "FAIL  port $Port did not open within ${TimeoutSeconds}s." -ForegroundColor Red
        Write-Host "      The server died at startup. Its own output is below." -ForegroundColor Red
        if ($proc -and $proc.HasExited) {
            Write-Host "      process exited with code $($proc.ExitCode)" -ForegroundColor Red
        }
        Write-Host "      Common causes (ENVIRONMENT.md S2): stale compiled Blade," -ForegroundColor Red
        Write-Host "      a config file that fails to parse, or a DB connection refused." -ForegroundColor Red
        exit 1
    }

    Write-Host "==> Port $Port is listening."
    $startedHere = $true
}

# ------------------------------------------------------- 2. schema + fixture
# OPT-IN ONLY. This mutates the real MySQL database.
if ($Seed) {
    Write-Host "==> migrate --force"
    & $Php artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        Write-Host "FAIL  migrate exited $LASTEXITCODE" -ForegroundColor Red
        exit $LASTEXITCODE
    }

    Write-Host "==> db:seed --class=CatalogueSeeder"
    & $Php artisan db:seed --class=CatalogueSeeder --force
    if ($LASTEXITCODE -ne 0) {
        Write-Host "FAIL  seeder exited $LASTEXITCODE" -ForegroundColor Red
        exit $LASTEXITCODE
    }
}

# --------------------------------------------------------------- 3. the probe
Write-Host "==> Probing $Base"
Push-Location $RepoRoot
try {
    node storage/app/qa-level6-http.mjs
    $probeExit = $LASTEXITCODE
}
finally {
    Pop-Location
}

# --------------------------------------------------------------- 4. teardown
# NOTE: -Stop is only meaningful when THIS run started the server. When the port
# was already open we deliberately do not kill it -- you may have your own
# `php artisan serve` in another terminal, and killing it would be rude. Say so
# out loud rather than exiting silently, so the no-op is never mistaken for a
# teardown that ran.
if ($Stop -and -not $startedHere) {
    Write-Host "==> -Stop ignored: this run reused an already-running server." -ForegroundColor Yellow
    Write-Host "    Re-run with -Stop after starting from a closed port to stop it." -ForegroundColor Yellow
}

if ($Stop -and $startedHere) {
    Write-Host "==> Stopping the server we started (-Stop)."

    # `artisan serve` is a PARENT that spawns a child `php -S 127.0.0.1:8125`
    # which actually holds the port. Killing only the listening PID leaves the
    # parent alive, and it respawns -- so the port stays open and the next run
    # silently reuses a server that -Stop was supposed to have killed. Kill the
    # listening process AND walk up to its parent.
    $toKill = @{}
    $conn = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    foreach ($c in $conn) {
        $toKill[$c.OwningProcess] = $true
        $child = Get-CimInstance Win32_Process -Filter "ProcessId=$($c.OwningProcess)" -ErrorAction SilentlyContinue
        if ($child -and $child.ParentProcessId) { $toKill[$child.ParentProcessId] = $true }
    }
    foreach ($procId in $toKill.Keys) {
        Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue
    }

    # Confirm it is actually gone rather than assuming.
    Start-Sleep -Milliseconds 500
    if (Test-PortOpen $Port) {
        Write-Host "WARN  port $Port is STILL listening after -Stop." -ForegroundColor Yellow
    }
    else {
        Write-Host "==> Port $Port closed."
    }
}
elseif ($startedHere) {
    Write-Host "==> Server left running on $Base (pass -Stop to kill it)."
}

if ($probeExit -eq 0) {
    Write-Host "==> QA PASSED" -ForegroundColor Green
}
else {
    Write-Host "==> QA FAILED (probe exit $probeExit)" -ForegroundColor Red
}
exit $probeExit
