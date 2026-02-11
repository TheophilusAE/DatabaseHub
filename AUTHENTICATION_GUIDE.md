# Authentication System Documentation

## Overview
The Data Import Dashboard now includes a complete authentication system with role-based access control (RBAC). There are two user types:

- **Admin** (👑): Full CRUD access and system management
- **User** (👤): Read-only access to view data and documents

---

## Default Test Accounts

### Admin Account
- **Email**: `admin@example.com`
- **Password**: `admin123`
- **Capabilities**: Full CRUD operations, Import/Export, Dashboard management

### User Account
- **Email**: `user@example.com`
- **Password**: `user123`
- **Capabilities**: View data records and documents only (read-only)

---

## Features

### 🔐 Authentication
- **Login**: Secure email/password authentication
- **Register**: Create new accounts with role selection
- **Logout**: Secure session termination
- **Session Management**: Automatic session handling with "Remember Me" option

### 👑 Admin Capabilities
Admins have full access to:
- ✅ **Full CRUD Operations**: Create, Read, Update, Delete all records
- ✅ **Document Management**: Upload, download, and manage all documents
- ✅ **Data Import/Export**: Import CSV/JSON files and export data in multiple formats
- ✅ **Analytics Dashboard**: Comprehensive statistics and category breakdowns
- ✅ **System Management**: Full administrative control over the platform
- ✅ **Dedicated Admin Dashboard**: Separate dashboard with enhanced features

### 👤 User Capabilities
Regular users have limited access:
- ✅ **View Records**: Browse and search all data records
- ✅ **View Documents**: Access and download available documents
- ✅ **Dedicated User Dashboard**: Personalized dashboard showing their permissions
- ❌ **No CRUD Operations**: Cannot create, edit, or delete records
- ❌ **No Import/Export**: Cannot import or export data
- ❌ **No System Management**: Cannot access admin features

---

## Route Structure

### Public Routes
```
/login         - Login page
/register      - Registration page
```

### Admin Routes (Prefix: /admin)
```
/admin/dashboard              - Admin dashboard
/admin/data-records           - Full CRUD data records
/admin/data-records/create    - Create new record
/admin/data-records/{id}/edit - Edit record
/admin/documents              - Full document management
/admin/documents/create       - Upload documents
/admin/import                 - Import data (CSV/JSON)
/admin/import/history         - Import history
/admin/export                 - Export data (CSV/JSON/Excel)
```

### User Routes (Prefix: /user)
```
/user/dashboard      - User dashboard
/user/data-records   - View data records (read-only)
/user/documents      - View documents (read-only)
```

---

## How to Use

### 1. First Time Setup
The system is already set up. Default users have been created:

```bash
# Admin: admin@example.com / admin123
# User: user@example.com / user123
```

### 2. Accessing the System

**Step 1**: Navigate to `http://localhost:8000`

**Step 2**: You'll be redirected to the login page

**Step 3**: Login with one of the test accounts:
- Use **admin@example.com** for full admin access
- Use **user@example.com** for limited user access

### 3. Creating New Users

**Option 1: Self Registration**
1. Click "Register" on the login page
2. Fill in the registration form
3. Select account type (Admin or User)
4. Submit to create account

**Option 2: Via Seeder (for testing)**
```bash
php artisan db:seed --class=UserSeeder
```

### 4. Admin Workflow
1. Login as admin (`admin@example.com`)
2. Access the **Admin Dashboard** with full statistics
3. Use navigation to access:
   - **Data Records**: Create/Edit/Delete records
   - **Documents**: Upload/Download/Delete documents
   - **Import**: Import CSV/JSON data files
   - **Export**: Export data in multiple formats
   - **History**: View import history

### 5. User Workflow
1. Login as user (`user@example.com`)
2. Access the **User Dashboard** (limited view)
3. Can only:
   - View data records (no editing)
   - Browse and download documents
   - See statistics for available data

---

## Security Features

### 🔒 Password Security
- Passwords are hashed using Laravel's bcrypt
- Minimum 8 characters required
- Confirm password validation

### 🛡️ Session Security
- CSRF protection on all forms
- Secure session management
- Automatic session regeneration on login
- Session invalidation on logout

### 🚪 Access Control
- Middleware protection on all routes
- Role-based route access
- Automatic redirection for unauthorized access
- Admin and User middleware enforcing permissions

### 🚫 Unauthorized Access
- 403 errors for role violations
- Automatic redirect to login for unauthenticated users
- Clear error messages for failed authentication

---

## UI Features

### Modern Design
- **Gradient Headers**: Beautiful blue-to-purple gradients for dashboards
- **Role Badges**: Clear visual indicators (👑 Admin / 👤 User)
- **User Dropdown**: Avatar with name, email, and logout button
- **Responsive Navigation**: Shows/hides menu items based on role

### Dashboards
- **Admin Dashboard**:
  - Purple gradient header with crown icon
  - Full statistics with category breakdown
  - Quick action cards for all operations
  - List of admin capabilities
  
- **User Dashboard**:
  - Blue gradient header with user icon
  - Basic statistics (records & documents)
  - Quick access to view-only features
  - Clear list of permissions and limitations

---

## Troubleshooting

### Can't Login
- Verify credentials (default: admin@example.com / admin123)
- Check if database migrations have run
- Ensure UserSeeder has been executed

### 403 Forbidden Error
- You're trying to access admin routes as a regular user
- Login as admin to access those features
- Users can only access /user/* routes

### Routes Not Working
- Verify middleware is registered in `bootstrap/app.php`
- Clear route cache: `php artisan route:clear`
- Check if user role is correctly set in database

### Session Issues
- Clear browser cookies
- Restart Laravel server
- Check session configuration in `config/session.php`

---

## Database Schema

### Users Table
```sql
- id: Primary Key
- name: User's full name
- email: Unique email address
- password: Hashed password
- role: enum('admin', 'user') - Default: 'user'
- remember_token: For "Remember Me" functionality
- email_verified_at: Email verification timestamp
- created_at: Account creation date
- updated_at: Last update date
```

---

## API Endpoints (Backend Integration)

The authentication system works seamlessly with your existing Go backend:

```
Backend API: http://localhost:8080
Frontend: http://localhost:8000
Vite Dev Server: http://localhost:5173
```

All API calls from the frontend include Laravel's session management, ensuring secure data access.

---

## Development

### Adding New Routes
1. Define route in `routes/web.php`
2. Apply appropriate middleware:
   - `->middleware(['auth', 'admin'])` for admin
   - `->middleware(['auth', 'user'])` for user
   - `->middleware(['auth'])` for both

### Creating New Controllers
```bash
php artisan make:controller YourController
```

### Creating New Middleware
```bash
php artisan make:middleware YourMiddleware
```
Then register in `bootstrap/app.php`

### Running Migrations
```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```

---

## Testing

### Test Admin Features
1. Login as `admin@example.com`
2. Create a new data record
3. Upload a document
4. Import CSV/JSON file
5. Export data in different formats
6. Verify full CRUD access

### Test User Features
1. Login as `user@example.com`
2. Try to access `/admin/*` routes (should get 403)
3. Verify can view data records
4. Verify can view/download documents
5. Verify cannot create/edit/delete

### Test Authentication
1. Try accessing protected routes without login (should redirect to login)
2. Test "Remember Me" functionality
3. Test logout (should redirect to login)
4. Test registration with both roles

---

## Production Deployment

Before deploying to production:

1. **Change Default Passwords**
   ```bash
   php artisan tinker
   $user = User::where('email', 'admin@example.com')->first();
   $user->password = Hash::make('your-secure-password');
   $user->save();
   ```

2. **Update Environment Variables**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   SESSION_DRIVER=database
   ```

3. **Secure Session Configuration**
   - Set strong session encryption
   - Use HTTPS only cookies
   - Configure proper CORS settings

4. **Consider Email Verification**
   - Implement email verification for new users
   - Add password reset functionality

---

## Support

For issues or questions:
- Check error logs in `storage/logs/laravel.log`
- Verify route list: `php artisan route:list`
- Check middleware: `php artisan route:list --middleware=admin`

---

## Summary

✅ Complete authentication system with login/register
✅ Role-based access control (Admin & User)
✅ Separate dashboards for each role
✅ Admin has full CRUD and system management
✅ User has read-only access
✅ Modern, gradient-based UI design
✅ Secure session management
✅ Default test accounts created
✅ Protected routes with middleware
✅ Beautiful navigation with user dropdown

**Start the servers and test the authentication:**
```bash
# Terminal 1 - Backend
cd D:\DataImportDashboard\backend
.\start.bat

# Terminal 2 - Frontend
cd D:\DataImportDashboard\frontend
cmd /c "php artisan serve"

# Terminal 3 - Vite
cd D:\DataImportDashboard\frontend
cmd /c "npm run dev"
```

Now visit: **http://localhost:8000** and login! 🚀
