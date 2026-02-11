@echo off
echo ========================================
echo   Create First Admin User
echo ========================================
echo.

REM Check if we're in the frontend directory
if not exist "artisan" (
    echo Error: This script must be run from the frontend directory
    echo Please navigate to the frontend folder first
    pause
    exit /b 1
)

echo This script will help you create your first admin user.
echo.

REM Prompt for user details
set /p name="Enter admin name: "
set /p email="Enter admin email: "
set /p password="Enter admin password (min 8 characters): "

echo.
echo Creating admin user...
echo.

REM Create the user using artisan tinker
php artisan tinker --execute="$user = \App\Models\User::create(['name' => '%name%', 'email' => '%email%', 'password' => \Illuminate\Support\Facades\Hash::make('%password%'), 'role' => 'admin']); echo 'Admin user created successfully!';"

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   SUCCESS!
    echo ========================================
    echo.
    echo Admin user has been created:
    echo   Name: %name%
    echo   Email: %email%
    echo   Role: Administrator
    echo.
    echo You can now login at: http://localhost:8000/login
    echo.
) else (
    echo.
    echo ========================================
    echo   ERROR
    echo ========================================
    echo.
    echo Failed to create admin user.
    echo Please check if the email already exists or try manually.
    echo.
)

pause
