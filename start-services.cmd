@echo off
title DriveNow ERP - Start Services
echo Starting DriveNow ERP services...
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-services.ps1" %*
echo.
echo ------------------------------------------------------------
echo Done. This window stays open so you can read the results.
echo Press any key to close...
pause >nul
