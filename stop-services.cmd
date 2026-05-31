@echo off
title DriveNow ERP - Stop Services
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0stop-services.ps1"
echo.
echo ------------------------------------------------------------
echo Press any key to close...
pause >nul
