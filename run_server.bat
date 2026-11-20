@echo off
title Institute Placement & Alumni Tracking Portal - Server Runner
cd /D %~dp0

echo ======================================================================
echo   Institute Placement ^& Alumni Tracking Portal
echo ======================================================================
echo.

set PHP_EXE=php
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE=C:\xampp\php\php.exe
)

echo [1/3] Checking MySQL database service...
if exist "C:\xampp\mysql\bin\mysqladmin.exe" (
    "C:\xampp\mysql\bin\mysqladmin.exe" -u root ping >nul 2>&1
    if errorlevel 1 (
        echo [INFO] Starting MySQL daemon...
        start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 2 >nul
    ) else (
        echo [SUCCESS] MySQL is running.
    )
)

echo [2/3] Checking Database Installation...
%PHP_EXE% install.php

echo.
echo [3/3] Starting PHP Built-in Web Server at http://127.0.0.1:8000 ...
echo Press Ctrl+C anytime to stop the server.
echo.
echo Opening browser...
start http://127.0.0.1:8000

%PHP_EXE% -S 127.0.0.1:8000
pause
