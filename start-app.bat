@echo off
echo ========================================
echo  Data Import Dashboard - Starting...
echo ========================================
echo.

echo [1/2] Starting Go Backend Server...
start "Backend Server" cmd /k "cd /d %~dp0backend && .\start.bat"
timeout /t 3 /nobreak >nul

echo [2/2] Starting Laravel Frontend Server...
start "Frontend Server" cmd /k "cd /d %~dp0frontend && php artisan serve"
timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo  ✓ Both servers are starting!
echo ========================================
echo.
echo  Backend:  http://localhost:8080
echo  Frontend: http://localhost:8000
echo.
echo  Login to test:
echo  - Regular User: test@example.com / password123
echo  - Admin User:   admin@example.com / admin123
echo.
echo  Press any key to open the application...
pause >nul

start http://localhost:8000

echo.
echo  To stop servers, close their terminal windows.
echo.
