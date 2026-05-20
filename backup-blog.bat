@echo off
REM Genera un backup de las tablas del blog (categorias_blog + articulos).
REM Ejecutar antes de operaciones arriesgadas o al final de cada sesión de trabajo.

set MYSQL=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe
set DB=www_eventify
set DIR=%~dp0storage\backups
set FILE=%DIR%\blog_content_%date:~6,4%-%date:~3,2%-%date:~0,2%.sql

if not exist "%DIR%" mkdir "%DIR%"

"%MYSQL%" -u root --no-tablespaces --skip-triggers --single-transaction --extended-insert %DB% categorias_blog articulos > "%FILE%"

if %errorlevel%==0 (
    echo [OK] Backup guardado en: %FILE%
) else (
    echo [ERROR] Fallo al generar el backup
)
