<#
    DriveNow ERP - stop all running microservice dev servers.

    Easiest: double-click  stop-services.cmd
    Or:      powershell -ExecutionPolicy Bypass -File .\stop-services.ps1

    `php artisan serve` spawns a child `php -S` listener, so we stop by port
    owner (plus any matching php processes) to guarantee the ports are freed.
#>

$ports = 8001..8008

function Get-DriveNowPids([int[]]$ports) {
    $set = [System.Collections.Generic.HashSet[int]]::new()
    Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -match 'artisan serve' -or $_.CommandLine -match '-S 127\.0\.0\.1:80' } |
        ForEach-Object { [void]$set.Add([int]$_.ProcessId) }
    foreach ($port in $ports) {
        Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue |
            ForEach-Object { [void]$set.Add([int]$_.OwningProcess) }
    }
    return $set
}

Write-Host 'Stopping DriveNow services...' -ForegroundColor Cyan

$pids = Get-DriveNowPids $ports
$stopped = 0
foreach ($procId in $pids) {
    try {
        Stop-Process -Id $procId -Force -ErrorAction Stop
        $stopped++
    } catch {
        Write-Host ("  [FAILED ] could not stop pid {0}: {1}" -f $procId, $_.Exception.Message) -ForegroundColor Red
    }
}
Start-Sleep -Milliseconds 700

# Per-port confirmation.
Write-Host ''
Write-Host 'Port status:' -ForegroundColor DarkGray
$stillUp = @()
foreach ($port in $ports) {
    $busy = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
    if ($busy) {
        $stillUp += $port
        Write-Host ("  [IN USE] {0}" -f $port) -ForegroundColor Red
    } else {
        Write-Host ("  [FREE]   {0}" -f $port) -ForegroundColor Green
    }
}

Write-Host ''
if ($stopped -eq 0 -and $stillUp.Count -eq 0) {
    Write-Host 'No DriveNow services were running.' -ForegroundColor DarkGray
} elseif ($stillUp.Count -eq 0) {
    Write-Host ("SUCCESS - stopped {0} process(es); ports 8001-8008 are all free." -f $stopped) -ForegroundColor Green
} else {
    Write-Host ("FAILED - these ports are still in use: {0}" -f ($stillUp -join ', ')) -ForegroundColor Red
}

exit $stillUp.Count
