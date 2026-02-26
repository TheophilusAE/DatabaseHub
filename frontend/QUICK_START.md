# DataBridge - Quick Start Guide

Get the Laravel frontend up and running in minutes!

## Prerequisites Check

Before starting, ensure you have:

- âœ… PHP 8.2+ installed (`php -v`)
- âœ… Composer installed (`composer -V`)
- âœ… Node.js 18+ installed (`node -v`)
- âœ… npm installed (`npm -v`)
- âœ… Backend API running on http://localhost:8080

## Quick Start (5 Minutes)

### Step 1: Install Dependencies

Open PowerShell in the frontend directory and run:

```powershell
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 2: Configure Environment

```powershell
# Copy environment file
Copy-Item .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Start Development Servers

**Terminal 1** - Start Laravel:
```powershell
php artisan serve
```

**Terminal 2** - Start Vite (Asset Compiler):
```powershell
npm run dev
```

### Step 4: Open Application

Open your browser and go to:
```
http://localhost:8000
```

You should see the Dashboard!

## Verify Setup

### 1. Check Backend Connection

On the dashboard, look at the **Server Status** card:
- ðŸŸ¢ **Green "Online"** = Backend connected âœ…
- ðŸ”´ **Red "Offline"** = Backend not running âŒ

If offline, ensure the Go backend is running:
```powershell
cd ..\backend
.\start.bat
```

### 2. Test Features

Click through the navigation:
- **Dashboard** - Should load statistics
- **Data Records** - Should show empty table or existing records
- **Documents** - Should show empty grid or existing documents
- **Import** - Ready to import CSV/JSON files
- **Export** - Ready to export data
- **History** - Should show import logs (if any)

## Common Issues & Solutions

### âŒ Issue: "composer: command not found"

**Solution**: Install Composer
```powershell
# Download and install from:
https://getcomposer.org/download/
```

### âŒ Issue: "php: command not found"

**Solution**: Install PHP
```powershell
# Download from:
https://windows.php.net/download/
# Or use Laragon/XAMPP which includes PHP
```

### âŒ Issue: Backend Connection Failed

**Symptoms**: Dashboard shows "Offline" or "Error"

**Solution**:
1. Check if backend is running:
   ```powershell
   cd ..\backend
   .\start.bat
   ```
2. Wait for "Server is ready and running!"
3. Refresh dashboard

### âŒ Issue: Styling Not Working

**Symptoms**: Page looks unstyled, no colors

**Solution**:
1. Ensure Vite is running: `npm run dev`
2. Hard refresh browser: `Ctrl + Shift + R`
3. Check terminal for Vite errors

### âŒ Issue: Port 8000 Already in Use

**Solution**: Use different port
```powershell
php artisan serve --port=8001
```
Then access at: `http://localhost:8001`

### âŒ Issue: Permission Denied Errors

**Solution**: Run as Administrator
1. Right-click PowerShell
2. Select "Run as Administrator"
3. Navigate to project and retry

## Development Workflow

### Making Changes

1. **Edit Views**: Modify files in `resources/views/`
   - Changes auto-refresh with Vite
   
2. **Edit Styles**: Update Tailwind classes in views
   - Uses Tailwind CSS 4.0
   - Changes compile automatically
   
3. **Edit JavaScript**: Modify `resources/js/app.js`
   - Auto-reloads with Vite

### Adding New Pages

1. **Create Controller**:
   ```php
   // app/Http/Controllers/MyController.php
   php artisan make:controller MyController
   ```

2. **Add Route**:
   ```php
   // routes/web.php
   Route::get('/my-page', [MyController::class, 'index']);
   ```

3. **Create View**:
   ```php
   // resources/views/my-page.blade.php
   @extends('layouts.app')
   @section('content')
       <h1>My Page</h1>
   @endsection
   ```

## Testing the Application

### Test Data Records

1. Go to **Data Records** â†’ **Add New Record**
2. Fill in:
   - Name: `Test Product`
   - Category: `electronics`
   - Value: `99.99`
   - Status: `active`
3. Click **Create Record**
4. Record should appear in list

### Test File Upload

1. Go to **Documents** â†’ **Upload Document**
2. Select any file (PDF, image, etc.)
3. Choose category
4. Click **Upload Document**
5. File should appear in documents grid

### Test CSV Import

1. Go to **Import**
2. Use sample file: `..\backend\sample_data.csv`
3. Click **Import CSV**
4. Check success message
5. Go to **Data Records** to see imported data

### Test Export

1. Go to **Export**
2. Click **Download All Records** under CSV
3. CSV file should download
4. Open in Excel to verify data

## Production Deployment

### Build for Production

```powershell
# Compile assets for production
npm run build

# Set environment to production
# In .env file:
APP_ENV=production
APP_DEBUG=false
```

### Optimize Laravel

```powershell
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

## File Structure

```
frontend/
â”œâ”€â”€ app/
â”‚   â””â”€â”€ Http/Controllers/    # Your controllers
â”œâ”€â”€ resources/
â”‚   â”œâ”€â”€ views/              # Blade templates (HTML)
â”‚   â”œâ”€â”€ js/                 # JavaScript files
â”‚   â””â”€â”€ css/                # Stylesheets
â”œâ”€â”€ routes/
â”‚   â””â”€â”€ web.php             # Route definitions
â”œâ”€â”€ public/                 # Public assets
â””â”€â”€ storage/                # App storage
```

## Useful Commands

```powershell
# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# List all routes
php artisan route:list

# Check Laravel version
php artisan --version

# Run database migrations (if needed)
php artisan migrate
```

## Next Steps

âœ… **You're all set!** The frontend is running.

**Try these next:**

1. ðŸŽ¨ **Customize**: Edit views to match your brand
2. ðŸ“Š **Import Data**: Upload CSV/JSON files
3. ðŸ“ **Upload Files**: Test document management
4. ðŸš€ **Deploy**: Move to production server

## Getting Help

**Documentation**:
- Laravel: https://laravel.com/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Backend API: See `../backend/API_DOCUMENTATION.md`

**Check Logs**:
- Laravel logs: `storage/logs/laravel.log`
- Browser console: `F12` â†’ Console tab
- Backend logs: Check backend terminal

**Test Backend**:
```powershell
# Open in browser:
http://localhost:8080/health

# Should return:
{"status":"ok","message":"Server is running"}
```

## Tips & Tricks

ðŸ’¡ **Hot Tip**: Keep both terminals open (Laravel + Vite) for best development experience

ðŸ’¡ **Debug Tip**: Check browser console (F12) for JavaScript errors

ðŸ’¡ **Speed Tip**: Use `--host` flag to access from other devices on your network:
```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

ðŸ’¡ **API Tip**: All API calls go to `http://localhost:8080` - edit in `resources/js/app.js` if needed

---

**Happy Coding! ðŸš€**

Need help? Check the root `README.md` and `USER_GUIDE.md` for full documentation.

