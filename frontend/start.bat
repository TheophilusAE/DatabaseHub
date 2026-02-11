@echo off
echo ================================================
echo   Data Import Dashboard - Frontend Launcher
echo ================================================
echo.

REM Check if .env exists
if not exist ".env" (
    echo [1/4] Creating .env file...
    copy .env.example .env >nul 2>&1
    if errorlevel 1 (
        echo ERROR: Failed to create .env file
        echo Please manually copy .env.example to .env
        pause
        exit /b 1
    )
    
    echo [2/4] Generating application key...
    php artisan key:generate
    if errorlevel 1 (
        echo ERROR: Failed to generate application key
        pause
        exit /b 1
    )
) else (
    echo [1/4] Environment file exists ✓
    echo [2/4] Application key already set ✓
)

echo.
echo [3/4] Checking dependencies...

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

REM Check if node_modules exists
if not exist "node_modules" (
    echo Installing Node dependencies...
    call npm install
    if errorlevel 1 (
        echo ERROR: npm install failed
        echo Make sure Node.js and npm are installed
        pause
        exit /b 1
    )
) else (
    echo Node dependencies installed ✓
)

echo.
echo [4/4] Starting servers...
echo.
echo ================================================
echo Backend API should be running at:
echo http://localhost:8080
echo.
echo Frontend will be available at:
echo http://localhost:8000
echo ================================================
echo.
echo Opening 2 terminals:
echo   Terminal 1: Laravel Server (php artisan serve)
echo   Terminal 2: Vite Dev Server (npm run dev)
echo.
echo Press Ctrl+C in each terminal to stop
echo ================================================
echo.

REM Start Laravel in new terminal
start "Laravel Server" cmd /k "echo Starting Laravel Server... && php artisan serve"

REM Wait a moment
timeout /t 2 /nobreak >nul

REM Start Vite in new terminal
start "Vite Dev Server" cmd /k "echo Starting Vite Dev Server... && npm run dev"

echo.
echo ✓ Servers starting...
echo ✓ Wait a few seconds for servers to be ready
echo ✓ Then open: http://localhost:8000
echo.
echo To stop servers: Close the terminal windows
echo.

REM Wait 3 seconds then open browser
timeout /t 3 /nobreak >nul
start http://localhost:8000

echo Done! Application should open in your browser.
echo.
pause
