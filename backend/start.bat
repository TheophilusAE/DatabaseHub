@echo off
echo ========================================
echo Data Import Dashboard - Backend Setup
echo ========================================
echo.

REM Check if Go is installed
go version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Go is not installed or not in PATH
    echo Please install Go from https://golang.org/dl/
    pause
    exit /b 1
)

echo [1/4] Checking Go installation...
go version
echo.

REM Check if .env file exists
if not exist .env (
    echo [2/4] Creating .env file from .env.example...
    copy .env.example .env
    echo.
    echo IMPORTANT: Please edit .env file with your database configuration!
    echo Press any key after editing .env file...
    pause >nul
) else (
    echo [2/4] .env file already exists
)
echo.

echo [3/4] Installing dependencies...
go mod download
if %errorlevel% neq 0 (
    echo Error: Failed to download dependencies
    pause
    exit /b 1
)
echo Dependencies installed successfully
echo.

echo [4/4] Starting the server...
echo.
echo ========================================
echo Server will start on http://localhost:8080
echo Press Ctrl+C to stop the server
echo ========================================
echo.

go run main.go
