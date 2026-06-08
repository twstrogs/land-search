@echo off
cd /d "%~dp0"
start "Queue Worker" cmd /k "php artisan queue:work --queue=imports,default"
start "Laravel Dev" cmd /k "php artisan serve"
