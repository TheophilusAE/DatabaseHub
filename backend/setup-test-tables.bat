@echo off
REM Quick script to create test tables in PostgreSQL
echo ========================================
echo Creating Test Tables for Data Import Dashboard
echo ========================================
echo.

REM Check if PostgreSQL is in PATH
where psql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: psql command not found!
    echo Please add PostgreSQL bin directory to your PATH
    echo Example: C:\Program Files\PostgreSQL\15\bin
    echo.
    pause
    exit /b 1
)

REM Set default values (you can modify these)
set DB_NAME=data_import_db
set DB_USER=postgres
set DB_HOST=localhost
set DB_PORT=5432

echo Database: %DB_NAME%
echo User: %DB_USER%
echo Host: %DB_HOST%
echo Port: %DB_PORT%
echo.
echo Running SQL setup script...
echo.

REM Run the SQL script
psql -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_NAME% -f test_tables_setup.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Test tables created successfully!
    echo ========================================
    echo.
    echo The following tables are now ready:
    echo   - data_records
    echo   - customers
    echo   - products
    echo   - orders
    echo   - employees
    echo   - sales
    echo   - inventory
    echo.
    echo You can now test the import function with:
    echo   - sample_customers.csv
    echo   - sample_products.csv
    echo   - sample_employees.csv
    echo   - sample_sales.csv
    echo   - sample_inventory.csv
    echo   - sample_customers.json
    echo   - sample_products.json
    echo.
    echo See TEST_DATA_README.md for detailed testing instructions.
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to create tables
    echo ========================================
    echo.
    echo Possible issues:
    echo   1. Database does not exist - create it first:
    echo      psql -U postgres -c "CREATE DATABASE %DB_NAME%;"
    echo   2. Wrong password - you will be prompted for password
    echo   3. PostgreSQL service not running
    echo.
)

pause
