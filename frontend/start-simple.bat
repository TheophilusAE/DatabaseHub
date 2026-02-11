@echo off
echo ================================================
echo   Data Import Dashboard - Simple Launcher
echo ================================================
echo.

REM Check if .env exists
if not exist ".env" (
    echo [1/3] Creating .env file...
    copy .env.example .env >nul 2>&1
    if errorlevel 1 (
        echo ERROR: Failed to create .env file
        echo Please manually copy .env.example to .env
        pause
        exit /b 1
    )
    
    echo [2/3] Generating application key...
    php artisan key:generate
    if errorlevel 1 (
        echo ERROR: Failed to generate application key
        pause
        exit /b 1
    )
) else (
    echo [1/3] Environment file exists ✓
    echo [2/3] Application key already set ✓
)

echo.
echo [3/3] Checking dependencies...

REM Check if vendor exists
if not exist "vendor" (
    echo Installing PHP dependencies...
    call composer install
    if errorlevel 1 (
        echo ERROR: Composer install failed
        echo Make sure Composer is installed and try again
        pause
        exit /b 1
    )
) else (
    echo PHP dependencies installed ✓
)

REM Check if assets are built
if not exist "public\build" (
    echo.
    echo WARNING: Assets not built yet!
    echo Building assets now... (this may take a minute)
    echo.
    call cmd /c "npm install && npm run build"
    if errorlevel 1 (
        echo ERROR: Asset build failed
        echo The app may not display correctly
        pause
    )
) else (
    echo Assets already built ✓
)

echo.
echo ================================================
echo Backend API should be running at:
echo http://localhost:8080
echo.
echo Frontend will start at:
echo http://localhost:8000
echo ================================================
echo.
echo Starting Laravel Server...
echo Press Ctrl+C to stop the server
echo.

REM Start Laravel server directly (no Vite needed!)
php artisan serve

