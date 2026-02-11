@echo off
cls
echo ========================================
echo  Starting Data Import Dashboard
echo ========================================
echo.

REM Kill any existing PHP processes on port 8000
echo [1/4] Stopping any existing frontend server...
FOR /F "tokens=5" %%P IN ('netstat -ano ^| findstr :8000') DO TaskKill /PID %%P /F 2>nul
timeout /t 1 /nobreak >nul

REM Clear Laravel caches
echo [2/4] Clearing Laravel caches...
cd /d "%~dp0frontend"
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo    - Configuration cache cleared
echo    - Application cache cleared
echo    - Route cache cleared
echo    - View cache cleared

REM Start Laravel frontend
echo [3/4] Starting Laravel frontend server...
start "Laravel Frontend" cmd /k "cd /d %~dp0frontend && php artisan serve"
timeout /t 3 /nobreak >nul

REM Start Go backend (if not already running)
echo [4/4] Ensuring Go backend is running...
netstat -ano | findstr :8080 >nul
if errorlevel 1 (
    start "Go Backend" cmd /k "cd /d %~dp0backend && .\start.bat"
    timeout /t 5 /nobreak >nul
) else (
    echo    - Backend already running on port 8080
)

echo.
echo ========================================
echo  ✓ Servers Started Successfully!
echo ========================================
echo.
echo  Frontend: http://localhost:8000
echo  Backend:  http://localhost:8080
echo.
echo  Test Accounts:
echo  - Admin: admin@example.com / admin123
echo  - User:  test@example.com / password123
echo.
echo  Opening application in browser...
echo.

timeout /t 2 /nobreak >nul
start http://localhost:8000/login

echo  Servers are running in separate windows.
echo  Close those windows to stop the servers.
echo.
pause
