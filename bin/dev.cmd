@echo off
echo Starting KPI Tratama at http://127.0.0.1:8000
pushd "%~dp0..\public"
php -S 127.0.0.1:8000 ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
popd
