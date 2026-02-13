@echo off
echo ========================================
echo Creating Admin User
echo ========================================
echo.

cd /d "%~dp0"
go run create_admin.go

echo.
echo Press any key to exit...
pause >nul
