@echo off
echo Building Data Import Dashboard Backend...
echo.

go build -o data-import-api.exe main.go

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Build successful!
    echo Executable: data-import-api.exe
    echo ========================================
    echo.
    echo To run the application:
    echo   .\data-import-api.exe
    echo.
) else (
    echo.
    echo ========================================
    echo Build failed!
    echo ========================================
    echo.
)

pause
