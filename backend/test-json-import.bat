@echo off
echo ========================================
echo Testing JSON Import Functionality
echo ========================================
echo.

REM Check if server is running
echo [1] Checking if server is running...
curl -s http://localhost:8080/health >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Server is not running!
    echo Please start the server first with: start.bat
    pause
    exit /b 1
)
echo ✓ Server is running
echo.

REM Test JSON import
echo [2] Testing JSON import with sample_data.json...
echo.
curl -X POST http://localhost:8080/upload/json ^
  -F "file=@sample_data.json" ^
  -H "Accept: application/json"

echo.
echo.
echo ========================================
echo Test completed!
echo ========================================
echo.
echo To view imported data, use:
echo   curl http://localhost:8080/data
echo.
echo To view import history, use:
echo   curl http://localhost:8080/upload/history
echo.
pause
