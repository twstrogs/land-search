@echo off
cd /d "%~dp0"
echo Starting Queue Worker for Land Search...
echo Press Ctrl+C to stop
echo.
php artisan queue:work --queue=imports,default --tries=1
