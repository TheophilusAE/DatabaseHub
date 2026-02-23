@echo off
setlocal

echo ========================================
echo Seed All Dummy Data (Dashboard Listings)
echo ========================================
echo.

where psql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: psql not found in PATH
    echo Please install PostgreSQL client tools or add psql to PATH.
    pause
    exit /b 1
)

set PGHOST=175.16.1.184
set PGPORT=5432
set PGDATABASE=hub
set PGUSER=postgres
set PGPASSWORD=postgres

echo Target: %PGUSER%@%PGHOST%:%PGPORT%/%PGDATABASE%
echo Script: seed_all_dummy_data.sql
echo.

psql -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -f seed_all_dummy_data.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS: Dummy data seeded.
    echo ========================================
) else (
    echo.
    echo ========================================
    echo ERROR: Seeding failed.
    echo ========================================
)

pause
endlocal
