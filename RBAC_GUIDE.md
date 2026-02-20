# 🔐 Role-Based Access Control (RBAC) System

## Overview

The Data Import Dashboard now features a complete **Role-Based Access Control** system where:

- **New users** are automatically assigned the `user` role when they register
- **Only Administrators** can manage user roles and access all features
- **Regular Users** have view-only access to data and documents

---

## 🎭 User Roles

### 👑 Administrator (admin)
**Full access to everything in the system**

#### Permissions:
-   **User Management**: Full CRUD access
  - Create new users with any role
  - Edit user information and roles
  - Delete users (except themselves)
  - View all users
  - Search and filter users

-   **Data Records**: Full CRUD access
  - Create new records
  - Edit existing records
  - Delete records
  - View all records
  - Import/Export data

-   **Documents**: Full CRUD access
  - Upload documents
  - Edit document metadata
  - Delete documents
  - Download documents
  - View all documents

-   **Import/Export**: Full access
  - Import CSV/JSON files
  - Export data in multiple formats
  - View import history
  - Manage failed imports

-   **Dashboard**: Admin dashboard with full statistics

---

### 👤 Regular User (user)
**View-only access to data**

#### Permissions:
-   View data records (read-only)
-   View documents (read-only)
-   Download documents
-   User dashboard with limited statistics
- ❌ Cannot create, edit, or delete anything
- ❌ Cannot import/export data
- ❌ Cannot manage users
- ❌ Cannot change roles

---

## 📋 User Registration Flow

### New User Registration

1. User visits `/register`
2. Fills out registration form:
   - Name
   - Email
   - Password
   - Password Confirmation
3. **Role is automatically set to `user`**
4. User is logged in and redirected to User Dashboard

**Important**: Users CANNOT choose their role during registration for security reasons.

---

## 👥 User Management (Admin Only)

### Access User Management
Navigate to: **Admin Dashboard → Users**

Or directly: `http://localhost:8000/admin/users`

### Create New User

1. Click **"Add New User"** button
2. Fill out the form:
   - Full Name
   - Email Address
   - **Role** (Admin can choose `admin` or `user`)
   - Password
   - Confirm Password
3. Click **"Create User"**

### Edit Existing User

1. Go to Users page
2. Click **"Edit"** next to the user
3. Update:
   - Name
   - Email
   - **Role** (change between admin/user)
   - Optionally change password
4. Click **"Save Changes"**

### Delete User

1. Go to Users page
2. Click **"Delete"** next to the user
3. Confirm deletion

**Note**: Admins cannot delete their own account.

### Search & Filter Users

- **Search**: Type name or email in search box
- **Filter by Role**: Select "All Roles", "Admin", or "User"
- **Reset**: Clear all filters

---

## 🔒 Security Features

### 1. Default Role Protection
```php
// In AuthController::register()
$user = User::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'password' => Hash::make($validated['password']),
    'role' => 'user', // Always default to 'user'
]);
```

### 2. Middleware Protection
All admin routes are protected by the `admin` middleware:
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin-only routes
});
```

### 3. Self-Deletion Prevention
Admins cannot delete their own account to prevent lockout.

### 4. Role Validation
All role changes are validated to ensure only `admin` or `user` values.

---

## 🛣️ Route Structure

### Admin Routes (Protected)
```
/admin/dashboard          - Admin Dashboard
/admin/users              - User Management (List)
/admin/users/create       - Create User
/admin/users/{id}/edit    - Edit User
/admin/data-records       - Full CRUD on Data
/admin/documents          - Full CRUD on Documents
/admin/import             - Import Data
/admin/export             - Export Data
/admin/import/history     - Import History
```

### User Routes (Protected)
```
/user/dashboard           - User Dashboard (Limited)
/user/data-records        - View Data Records
/user/documents           - View Documents
```

### Public Routes
```
/login                    - Login Page
/register                 - Registration Page
/logout                   - Logout Action
```

---

## 📊 User Statistics

The User Management page displays:

- **Total Users**: Count of all users in system
- **Administrators**: Count of users with admin role
- **Regular Users**: Count of users with user role

---

## 🎯 Use Cases

### Use Case 1: Create First Admin
When setting up the system:
1. Register a regular account
2. Manually update database role to 'admin':
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
   ```
3. Login and access admin features

### Use Case 2: Promote User to Admin
1. Login as admin
2. Go to Users → Edit user
3. Change role from "User" to "Administrator"
4. Save changes
5. User now has full admin access

### Use Case 3: Create Admin Accounts
1. Login as admin
2. Go to Users → Add New User
3. Select "👑 Administrator" role
4. Fill in details and create
5. New admin can now login with full access

### Use Case 4: Downgrade Admin to User
1. Login as admin
2. Go to Users → Edit admin user
3. Change role from "Administrator" to "User"
4. Save changes
5. User now has view-only access

---

## 🚨 Important Notes

1. **First Admin Setup**: The system doesn't create a default admin. You must manually set the first admin via database or create one through the user interface after registering.

2. **Role Changes Apply Immediately**: When an admin changes a user's role, it takes effect on their next request.

3. **Self-Protection**: Admins cannot change their own role or delete themselves through the UI.

4. **Permission Checks**: All protected actions verify the user's role before allowing access.

---

## 🔧 Technical Implementation

### Database Schema
```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['admin', 'user'])->default('user');
});
```

### User Model Methods
```php
public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isUser(): bool
{
    return $this->role === 'user';
}
```

### Middleware Check
```php
// In middleware
if (!auth()->user()->isAdmin()) {
    abort(403, 'Unauthorized access');
}
```

---

## 📝 Testing

### Test Admin Access
1. Create user with admin role
2. Login as admin
3. Verify access to:
   - User Management
   - Data CRUD
   - Document CRUD
   - Import/Export
   - Admin Dashboard

### Test Regular User Access
1. Register new account (defaults to user)
2. Login as regular user
3. Verify:
   - Can view data (read-only)
   - Can view documents (read-only)
   - Cannot create/edit/delete
   - Cannot access /admin routes
   - Redirected to user dashboard

---

## 🎨 UI Indicators

- Admin users see: **👑 Admin** badge
- Regular users see: **👤 User** badge
- Navigation shows different menu items based on role
- "Users" menu only visible to admins

---

## 🔄 Migration Path

If you have existing users without roles:
```bash
php artisan migrate
```

This will add the `role` column with default value of `user`.

---

##   Summary

The RBAC system provides:
-   Secure registration with default user role
-   Complete user management interface for admins
-   Role-based navigation and permissions
-   Protection against unauthorized access
-   Self-protection for admin accounts
-   Clean separation between admin and user capabilities

**Result**: A secure, user-friendly system where admins have complete control while regular users have safe, read-only access.
