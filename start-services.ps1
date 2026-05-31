<#
    DriveNow ERP - start all microservices (PostgreSQL, no Docker).

    Easiest: double-click  start-services.cmd
    Or:      powershell -ExecutionPolicy Bypass -File .\start-services.ps1
    Rebuild assets first:   ... -File .\start-services.ps1 -Build
#>
param(
    [switch]$Build
)

$root = $PSScriptRoot
if (-not $root) { $root = Split-Path -Parent $MyInvocation.MyCommand.Path }
$logs = Join-Path $root '_serve_logs'
New-Item -ItemType Directory -Force -Path $logs | Out-Null

$php = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $php) {
    Write-Host '[FAILED] php was not found on PATH. Install PHP or add it to PATH.' -ForegroundColor Red
    exit 1
}

$services = [ordered]@{
    'auth-service'        = 8001
    'fleet-service'       = 8002
    'rental-service'      = 8003
    'crm-service'         = 8004
    'billing-service'     = 8005
    'maintenance-service' = 8006
    'analytics-service'   = 8007
    'client-service'      = 8008
}

function Test-Up([int]$port) {
    for ($i = 0; $i -lt 15; $i++) {
        try {
            $r = Invoke-WebRequest "http://127.0.0.1:$port/up" -UseBasicParsing -TimeoutSec 3
            if ($r.StatusCode -eq 200) { return $true }
        } catch {}
        Start-Sleep -Milliseconds 600
    }
    return $false
}

Write-Host 'Stopping any running DriveNow services...' -ForegroundColor DarkGray
# `php artisan serve` spawns a child `php -S` listener; clear both, plus
# whatever currently owns the ports, so the new servers bind cleanly.
$killPids = [System.Collections.Generic.HashSet[int]]::new()
Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.CommandLine -match 'artisan serve' -or $_.CommandLine -match '-S 127\.0\.0\.1:80' } |
    ForEach-Object { [void]$killPids.Add([int]$_.ProcessId) }
foreach ($p in $services.Values) {
    Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue |
        ForEach-Object { [void]$killPids.Add([int]$_.OwningProcess) }
}
foreach ($procId in $killPids) { Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue }
Start-Sleep -Milliseconds 900

# Multiple workers so cross-service HTTP calls (settings/fleet APIs) never stall.
$env:PHP_CLI_SERVER_WORKERS = '4'

Write-Host 'Launching services...' -ForegroundColor DarkGray
foreach ($svc in $services.Keys) {
    $port = $services[$svc]
    $wd = Join-Path $root $svc
    if (-not (Test-Path $wd)) {
        Write-Host ("  [SKIP]   {0} (folder missing)" -f $svc) -ForegroundColor Yellow
        continue
    }
    if ($Build) {
        Write-Host ("  building {0}..." -f $svc) -ForegroundColor DarkGray
        Push-Location $wd
        & npm run build *> (Join-Path $logs "$svc.build.log")
        Pop-Location
    }
    Start-Process -FilePath $php `
        -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', "--port=$port" `
        -WorkingDirectory $wd -WindowStyle Hidden `
        -RedirectStandardOutput (Join-Path $logs "$svc.out.log") `
        -RedirectStandardError  (Join-Path $logs "$svc.err.log")
}

Write-Host ''
Write-Host 'Verifying each service responds (this can take a few seconds)...' -ForegroundColor Cyan
$ok = 0
$fail = 0
foreach ($svc in $services.Keys) {
    $port = $services[$svc]
    if (-not (Test-Path (Join-Path $root $svc))) { continue }
    if (Test-Up $port) {
        Write-Host ("  [  OK  ] {0,-22} http://localhost:{1}" -f $svc, $port) -ForegroundColor Green
        $ok++
    } else {
        Write-Host ("  [FAILED] {0,-22} port {1} not responding -> see _serve_logs\{0}.err.log" -f $svc, $port) -ForegroundColor Red
        $fail++
    }
}

Write-Host ''
if ($fail -eq 0) {
    Write-Host ("SUCCESS - all {0} services are running." -f $ok) -ForegroundColor Green
} else {
    Write-Host ("INCOMPLETE - {0} running, {1} FAILED to start (check the logs above)." -f $ok, $fail) -ForegroundColor Red
}

Write-Host ''
Write-Host 'Open in a browser:' -ForegroundColor Cyan
Write-Host '  Operations Hub   http://localhost:8001   (admin@drivenow.test / password)'
Write-Host '  Client Portal    http://localhost:8008   (customer@drivenow.test / password)'
Write-Host '  Fleet 8002  Rentals 8003  CRM 8004  Billing 8005  Maintenance 8006  Analytics 8007'
Write-Host ''
Write-Host ("Logs: {0}    Stop: stop-services.cmd" -f $logs) -ForegroundColor DarkGray

exit $fail
