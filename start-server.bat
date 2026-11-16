@echo off
cd /d "%~dp0"
echo Servidor local en http://127.0.0.1:8000
echo Presiona Ctrl+C para detenerlo.
php -S 127.0.0.1:8000 -t public router.php
