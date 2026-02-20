# 🚀 Quick Start Guide - RBAC System

##   System Status
Your Data Import Dashboard is now fully configured with API-based authentication!

### What We Built
-   Go Backend authentication API (login, register, logout)
-   User management API (CRUD operations for admins)
-   Laravel frontend integrated with Go API
-   Session-based authentication (no Laravel database needed)
-   RBAC with 'user' and 'admin' roles
-   Automatic 'user' role assignment on registration
-   Admin-only user management interface

## 🎯 Quick Start

### 1. Start Both Servers (Already Running!)
```bash
# Backend: http://localhost:8080   RUNNING
# Frontend: http://localhost:8000   RUNNING
```

### 2. Test User Accounts Created
```
Regular User:
- Email: test@example.com
- Password: password123
- Role: user

Admin User:
- Email: admin@example.com
- Password: admin123
- Role: admin
```

### 3. Test the System

#### Login as Regular User
1. Go to http://localhost:8000/login
2. Login with test@example.com / password123
3. You'll see the dashboard
4. Try accessing http://localhost:8000/admin/users - Should get 403 Forbidden

#### Login as Admin
1. Logout (if logged in)
2. Login with admin@example.com / admin123
3. Navigate to http://localhost:8000/admin/users
4. You can now:
   - View all users
   - Create new users
   - Edit user roles
   - Delete users
   - View statistics

## 📋 Available Routes

### Public
- `/login` - Login page
- `/register` - Registration page (creates 'user' role)

### User Access (Authenticated)
- `/dashboard` - Main dashboard
- `/import` - Data import
- `/export` - Data export

### Admin Only
- `/admin/users` - User management interface

## 🔧 API Endpoints

### Authentication
```
POST http://localhost:8080/auth/register
POST http://localhost:8080/auth/login
POST http://localhost:8080/auth/logout
GET  http://localhost:8080/auth/verify
```

### User Management
```
GET    http://localhost:8080/users        - List users
GET    http://localhost:8080/users/stats  - User statistics
GET    http://localhost:8080/users/:id    - Get user
POST   http://localhost:8080/users        - Create user (can set role)
PUT    http://localhost:8080/users/:id    - Update user/role
DELETE http://localhost:8080/users/:id    - Delete user
```

## 🧪 Test Commands

### Check User Statistics
```powershell
Invoke-RestMethod -Uri http://localhost:8080/users/stats -Method Get
```

### Create Admin User
```powershell
$body = @{
    name = 'New Admin'
    email = 'newadmin@example.com'
    password = 'secure123'
    role = 'admin'
} | ConvertTo-Json

Invoke-RestMethod -Uri http://localhost:8080/users -Method Post -Body $body -ContentType 'application/json'
```

### Promote User to Admin
```powershell
# Update user with ID 1
$body = @{role = 'admin'} | ConvertTo-Json
Invoke-RestMethod -Uri http://localhost:8080/users/1 -Method Put -Body $body -ContentType 'application/json'
```

## 🔐 Security Features

1. **Bcrypt Password Hashing** - All passwords securely hashed in Go backend
2. **Default User Role** - Registration always creates 'user' role (safe)
3. **Admin-Only Management** - Only admins can modify roles
4. **Session-Based Auth** - File sessions, no database dependency
5. **Role Verification** - Middleware checks role on every admin request

## 📁 Key Files

### Backend (Go)
- `backend/models/user.go` - User model with role field
- `backend/handlers/auth_handler.go` - Login, register, logout
- `backend/handlers/user_handler.go` - User CRUD operations
- `backend/repository/user_repository.go` - Database queries
- `backend/routes/routes.go` - API routes

### Frontend (Laravel)
- `frontend/app/Http/Controllers/AuthController.php` - Auth UI controller
- `frontend/app/Http/Controllers/UserController.php` - User management UI
- `frontend/app/Http/Middleware/AdminMiddleware.php` - Admin route protection
- `frontend/resources/views/admin/users/` - User management views
- `frontend/.env` - API_BASE_URL configuration

## 🐛 Troubleshooting

### Can't Login
- Verify backend is running on port 8080
- Check `frontend/.env` has `API_BASE_URL=http://localhost:8080`
- Try registering a new user

### 403 Forbidden on Admin Pages
- Check user role: `Invoke-RestMethod -Uri http://localhost:8080/users/1 -Method Get`
- Logout and login again to refresh session
- Promote user to admin using API command above

### API Not Responding
- Check backend terminal for errors
- Verify PostgreSQL is running
- Test health endpoint: http://localhost:8080/health

## 📚 Documentation

For more details, see:
- `RBAC_GUIDE.md` - Complete RBAC documentation
- `backend/API_DOCUMENTATION.md` - API endpoint reference
- `AUTHENTICATION_GUIDE.md` - Authentication flow details

## 🎉 Next Steps

1. **Test the system** - Try both user and admin logins
2. **Customize roles** - Add more roles if needed
3. **Add features** - Email verification, password reset, etc.
4. **Production setup** - Configure proper CORS, API tokens, HTTPS

---

**System Architecture**: Go backend (data + auth) → PostgreSQL (remote Docker) → Laravel frontend (UI only)
