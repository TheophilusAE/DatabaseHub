@echo off
echo ========================================
echo Backend Verification Script
echo ========================================
echo.

REM Check Go installation
echo [1/5] Checking Go installation...
go version >nul 2>&1
if %errorlevel% neq 0 (
    echo ✗ FAILED: Go is not installed
    echo Please install Go from https://golang.org/dl/
    goto :end
)
go version
echo ✓ PASSED
echo.

REM Check if go.mod exists
echo [2/5] Checking project files...
if not exist go.mod (
    echo ✗ FAILED: go.mod not found
    goto :end
)
if not exist main.go (
    echo ✗ FAILED: main.go not found
    goto :end
)
echo ✓ PASSED
echo.

REM Check if .env exists
echo [3/5] Checking configuration...
if not exist .env (
    echo ! WARNING: .env file not found
    echo Creating .env from .env.example...
    copy .env.example .env >nul
    echo ✓ Created .env file
    echo.
    echo IMPORTANT: Please edit .env with your database credentials!
    echo.
) else (
    echo ✓ PASSED - .env file exists
)
echo.

REM Install dependencies
echo [4/5] Installing dependencies...
go mod download
if %errorlevel% neq 0 (
    echo ✗ FAILED: Could not download dependencies
    goto :end
)
echo ✓ PASSED
echo.

REM Build test
echo [5/5] Testing build...
go build -o test-backend.exe main.go
if %errorlevel% neq 0 (
    echo ✗ FAILED: Build failed
    goto :end
)
echo ✓ PASSED - Build successful
echo.

REM Clean up test build
if exist test-backend.exe (
    del test-backend.exe
)

echo ========================================
echo ✓ ALL CHECKS PASSED!
echo ========================================
echo.
echo Your backend is ready to run!
echo.
echo Next steps:
echo   1. Edit .env file with your database credentials
echo   2. Make sure your database server is running
echo   3. Create the database: data_import_db
echo   4. Run: start.bat  or  go run main.go
echo.
echo For detailed instructions, see QUICK_START.md
echo.

:end
pause
