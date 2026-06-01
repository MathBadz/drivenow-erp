# DriveNow ERP — generate a .env with cryptographically-random secrets.
# Usage:  pwsh ./scripts/generate-secrets.ps1   (run from the repo root)
# Writes ../.env relative to this script. Refuses to overwrite an existing .env
# unless -Force is passed.
param([switch]$Force)

$ErrorActionPreference = 'Stop'
$root   = Split-Path -Parent $PSScriptRoot
$envOut = Join-Path $root '.env'

if ((Test-Path $envOut) -and -not $Force) {
    Write-Host ".env already exists. Re-run with -Force to overwrite." -ForegroundColor Yellow
    exit 1
}

function New-Hex32 {
    $b = New-Object byte[] 32
    [System.Security.Cryptography.RandomNumberGenerator]::Fill($b)
    -join ($b | ForEach-Object { $_.ToString('x2') })
}
function New-AppKey {
    $b = New-Object byte[] 32
    [System.Security.Cryptography.RandomNumberGenerator]::Fill($b)
    'base64:' + [System.Convert]::ToBase64String($b)
}

$lines = @(
    "JWT_SECRET=$(New-Hex32)"
    "SERVICE_TOKEN=$(New-Hex32)"
    "APPKEY_AUTH=$(New-AppKey)"
    "APPKEY_FLEET=$(New-AppKey)"
    "APPKEY_RENTAL=$(New-AppKey)"
    "APPKEY_CRM=$(New-AppKey)"
    "APPKEY_BILLING=$(New-AppKey)"
    "APPKEY_MAINTENANCE=$(New-AppKey)"
    "APPKEY_ANALYTICS=$(New-AppKey)"
    "APPKEY_CLIENT=$(New-AppKey)"
    "APP_DEBUG=false"
    "APP_AUTO_SEED=false"
)

Set-Content -Path $envOut -Value $lines -Encoding ascii
Write-Host "Wrote $envOut with fresh secrets." -ForegroundColor Green
Write-Host "For the first launch:  (edit .env: APP_AUTO_SEED=true)  then  docker compose up -d --build"
