@echo off
chcp 65001 >nul
title HelpDesk TI - Servidor
cd /d "%~dp0"

echo ============================================
echo   HelpDesk TI - Sistema de Chamados
echo ============================================
echo.

where php >nul 2>nul
if errorlevel 1 (
    echo [ERRO] PHP nao encontrado.
    echo Abra o Laragon, clique em Terminal e arraste este arquivo para la,
    echo ou instale o PHP 8.3 ou superior.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    where composer >nul 2>nul
    if errorlevel 1 (
        echo [ERRO] Composer nao encontrado. Instale em https://getcomposer.org
        pause
        exit /b 1
    )
    echo Primeira execucao: instalando dependencias, aguarde...
    call composer install --no-interaction
    if errorlevel 1 (
        echo [ERRO] Falha ao instalar dependencias.
        pause
        exit /b 1
    )
)

if not exist ".env" (
    echo Criando configuracao com banco SQLite...
    copy ".env.example" ".env" >nul
    powershell -NoProfile -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' -replace '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=','# $1=' | Set-Content .env"
    php artisan key:generate --no-interaction
)

if not exist "database\database.sqlite" (
    echo Criando banco de dados com dados de exemplo...
    type nul > "database\database.sqlite"
    php artisan migrate --seed --force --no-interaction
)

echo.
echo Sistema no ar em http://127.0.0.1:8000
echo Login: admin@helpdesk.test / senha123
echo NAO feche esta janela durante o uso. Para desligar, feche-a.
echo.
start "" cmd /c "timeout /t 3 >nul & start http://127.0.0.1:8000"
php artisan serve
pause
