@echo off
echo ========================================
echo Inserting Dummy Data into Database
echo ========================================
echo.

REM Check if PostgreSQL tools are in PATH
where psql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: psql not found in PATH
    echo Please install PostgreSQL or add it to your PATH
    echo Common location: C:\Program Files\PostgreSQL\[version]\bin
    pause
    exit /b 1
)

REM Connection parameters from .env file
set PGHOST=175.16.1.184
set PGPORT=5432
set PGDATABASE=hub
set PGUSER=postgres
set PGPASSWORD=postgres

echo Database: %PGDATABASE%
echo Host: %PGHOST%:%PGPORT%
echo User: %PGUSER%
echo.
echo Connecting to database...
echo.

REM Run the dummy data script
psql -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -f insert_dummy_data.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Dummy data inserted.
    echo ========================================
    echo.
    echo You can now log in with:
    echo   Email: admin@example.com
    echo   Password: password123
    echo.
    echo Other test users:
    echo   - john.smith@example.com
    echo   - sarah.johnson@example.com
    echo   - mike.davis@example.com
    echo   - emily.chen@example.com
    echo   All with password: password123
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to insert dummy data
    echo ========================================
    echo Please check:
    echo   1. Database exists and is running
    echo   2. Connection parameters are correct
    echo   3. Tables are created (run test_tables_setup.sql first)
    echo.
)

pause
