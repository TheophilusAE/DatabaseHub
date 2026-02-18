# 📊 Data Import Dashboard

A full-stack application for managing data records, documents, and file imports/exports. Built with Go (Backend) and Laravel (Frontend).

## 🎉 NEW: Auto-Discovery Feature!

**Zero Configuration Required!** Administrators can now automatically discover and sync tables from databases:

- 🔍 **Automatic Table Detection**: Scans and identifies all tables in your database
- 📊 **Schema Introspection**: Automatically detects columns, types, and primary keys  
- ⚡ **One-Click Sync**: Configure dozens of tables in seconds
- 🔄 **Easy Re-sync**: Update configurations when schema changes
- 🔒 **Admin-Only**: Secure configuration management restricted to administrators

**Quick Start (Admins):** Add database → Select database → Discover Tables → Sync All → **Done!** 🎊

See [AUTO_DISCOVERY_QUICK_START.md](AUTO_DISCOVERY_QUICK_START.md) for complete guide.

---

## 🏗️ Architecture

```
┌─────────────────────┐         API Calls          ┌─────────────────────┐
│                     │    (http://localhost:8080)  │                     │
│  Laravel Frontend   │ ◄─────────────────────────► │   Go Backend API    │
│  (Port 8000)        │                             │   (Port 8080)       │
│                     │                             │                     │
│  - Views/UI         │                             │  - REST API         │
│  - Tailwind CSS     │                             │  - SQLite Database  │
│  - JavaScript       │                             │  - File Uploads     │
└─────────────────────┘                             └─────────────────────┘
```

## ✨ Features

### Data Records Management
- ✅ Create, Read, Update, Delete (CRUD) operations
- ✅ Pagination and filtering
- ✅ Category-based organization
- ✅ Search functionality
- ✅ Status management

### Document Management
- ✅ Upload any file type (PDF, images, videos, etc.)
- ✅ Download files
- ✅ File metadata tracking
- ✅ Drag-and-drop upload
- ✅ Category organization

### Data Import
- ✅ CSV file import
- ✅ JSON file import
- ✅ Real-time upload progress
- ✅ Import validation
- ✅ Error handling

### Data Export
- ✅ Export to CSV
- ✅ Export to JSON
- ✅ Export to Excel
- ✅ Category filtering
- ✅ Bulk export

### Import History
- ✅ Track all imports
- ✅ Success/failure statistics
- ✅ Detailed logging
- ✅ Pagination and search

### 🔐 Role-Based Access Control (RBAC)
- ✅ **User Registration**: New users default to 'user' role
- ✅ **Admin Management**: Full CRUD access to all features
- ✅ **User Management**: Admins can create, edit, and delete users
- ✅ **Role Management**: Admins can promote/demote users
- ✅ **Access Control**: Role-based permissions for all features
- ✅ **Secure by Default**: Users cannot self-promote to admin

#### Admin Permissions (👑 Administrator)
- Full CRUD access to users, data records, and documents
- Complete import/export capabilities
- User role management
- Access to admin dashboard

#### User Permissions (👤 Regular User)
- View-only access to data records
- View-only access to documents
- Can download documents
- Access to user dashboard

📖 **See [RBAC_GUIDE.md](RBAC_GUIDE.md) for complete documentation**

## 🚀 Quick Start

### Prerequisites

**Backend Requirements:**
- Go 1.21 or higher
- SQLite

**Frontend Requirements:**
- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- npm

### Installation Steps

#### 1. Start Backend (Go API)

```powershell
cd backend
.\start.bat
```

Wait for: `✓ Server is ready and running!`

The backend will be available at: `http://localhost:8080`

#### 2. Start Frontend (Laravel)

```powershell
cd frontend
.\start.bat
```

The frontend will open automatically at: `http://localhost:8000`

That's it! 🎉

#### 3. Create Your First Admin User

**Option A: Use the Helper Script (Recommended)**
```powershell
cd frontend
.\create-admin.bat
```
Follow the prompts to create your admin account.

**Option B: Register and Manually Promote**
1. Register a new account at `http://localhost:8000/register`
2. Login as that user (you'll have user role by default)
3. Then manually promote via database or have another admin promote you

**Option C: Manual Database Update**
```powershell
cd frontend
php artisan tinker
```
Then run:
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
$user->role = 'admin';
$user->save();
```

Once you have an admin account, you can manage all users through the web interface!

## 📁 Project Structure

```
DataImportDashboard/
│
├── backend/                    # Go Backend API
│   ├── main.go                # Application entry point
│   ├── config/                # Database & config
│   ├── handlers/              # API handlers
│   ├── models/                # Data models
│   ├── repository/            # Database layer
│   ├── routes/                # API routes
│   ├── uploads/               # Uploaded files
│   ├── start.bat              # Windows launcher
│   └── *.md                   # Documentation
│
└── frontend/                  # Laravel Frontend
    ├── app/
    │   └── Http/Controllers/  # Controllers
    ├── resources/
    │   ├── views/            # Blade templates
    │   ├── js/               # JavaScript
    │   └── css/              # Tailwind CSS
    ├── routes/web.php        # Routes
    ├── start.bat             # Windows launcher
    └── *.md                  # Documentation
```

## 🔌 API Endpoints

### Data Records
- `GET /data` - List all records
- `POST /data` - Create record
- `GET /data/:id` - Get specific record
- `PUT /data/:id` - Update record
- `DELETE /data/:id` - Delete record
- `GET /data/category/:category` - Filter by category

### Documents
- `GET /documents` - List all documents
- `POST /documents` - Upload document
- `GET /documents/:id` - Get document info
- `GET /documents/:id/download` - Download file
- `DELETE /documents/:id` - Delete document

### Import/Export
- `POST /upload/csv` - Import CSV
- `POST /upload/json` - Import JSON
- `GET /upload/history` - View import logs
- `GET /download/csv` - Export to CSV
- `GET /download/json` - Export to JSON
- `GET /download/excel` - Export to Excel

### System
- `GET /health` - Server health check

## 🎨 Tech Stack

### Backend
- **Language**: Go 1.21+
- **Framework**: Gin (Web Framework)
- **Database**: SQLite with GORM
- **Libraries**:
  - GORM (ORM)
  - Gin-CORS (CORS handling)
  - Excelize (Excel export)

### Frontend
- **Framework**: Laravel 11
- **CSS**: Tailwind CSS 4.0
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS with API fetch

## 📖 Documentation

Comprehensive documentation is available in each directory:

### Backend Documentation
- [`backend/README.md`](backend/README.md) - Full backend documentation
- [`backend/API_DOCUMENTATION.md`](backend/API_DOCUMENTATION.md) - Complete API reference
- [`backend/QUICK_START.md`](backend/QUICK_START.md) - Backend quick start
- [`backend/POSTMAN_TESTING_GUIDE.md`](backend/POSTMAN_TESTING_GUIDE.md) - API testing guide
- [`backend/JSON_IMPORT_GUIDE.md`](backend/JSON_IMPORT_GUIDE.md) - JSON import format
- [`backend/HUMAN_ROUTES_GUIDE.md`](backend/HUMAN_ROUTES_GUIDE.md) - Human-readable routes

### Frontend Documentation
- [`frontend/FRONTEND_README.md`](frontend/FRONTEND_README.md) - Full frontend documentation
- [`frontend/QUICK_START.md`](frontend/QUICK_START.md) - Frontend quick start

## 🧪 Testing

### Test Backend API with Postman

```powershell
cd backend
# Follow: POSTMAN_TESTING_GUIDE.md
```

### Test Frontend

1. Open browser: `http://localhost:8000`
2. Navigate through all pages
3. Try creating, editing, and deleting records
4. Test file uploads and downloads
5. Import sample CSV/JSON files
6. Export data in different formats

## 🔧 Configuration

### Backend Configuration

Edit `backend/config/config.go` for:
- Database path
- Server port
- Upload directory

Environment variables (optional):
```bash
PORT=8080
DB_PATH=./database.db
```

### Frontend Configuration

Edit `frontend/resources/js/app.js`:
```javascript
window.API_BASE_URL = 'http://localhost:8080';
```

Edit `frontend/.env`:
```env
APP_NAME="Data Import Dashboard"
APP_URL=http://localhost:8000
```

## 🛠️ Development

### Backend Development

```powershell
cd backend

# Run with auto-reload
go run main.go

# Build executable
go build -o dashboard.exe main.go

# Run tests
go test ./...
```

### Frontend Development

```powershell
cd frontend

# Install dependencies
composer install
npm install

# Start dev servers
php artisan serve        # Terminal 1
npm run dev             # Terminal 2

# Build for production
npm run build

# Clear caches
php artisan cache:clear
```

## 📊 Sample Data

Sample files are included in the backend directory:

- `backend/sample_data.csv` - Sample CSV data
- `backend/sample_data.json` - Sample JSON data
- `backend/sample_upload_test.json` - Test JSON structure

Use these files to test import functionality.

## 🐛 Troubleshooting

### Backend Issues

**Port 8080 already in use:**
```powershell
# Find and kill process using port 8080
netstat -ano | findstr :8080
taskkill /PID <PID> /F
```

**Database locked:**
```powershell
# Delete database file and restart
del backend\database.db
cd backend
.\start.bat
```

### Frontend Issues

**Backend connection failed:**
1. Ensure backend is running on port 8080
2. Check browser console for errors
3. Verify CORS is enabled in backend

**Styling not loading:**
```powershell
npm run build
php artisan view:clear
# Hard refresh: Ctrl + Shift + R
```

**Port 8000 already in use:**
```powershell
php artisan serve --port=8001
```

## 🚀 Deployment

### Backend Deployment

1. Build binary:
   ```bash
   go build -o dashboard main.go
   ```

2. Deploy with:
   - Binary file
   - config/ directory
   - .env file (if using)
   - Empty uploads/ directory

3. Run: `./dashboard`

### Frontend Deployment

1. Build assets:
   ```bash
   npm run build
   ```

2. Configure web server (Apache/Nginx)

3. Set environment:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

4. Optimize:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 📝 License

This project is part of the Data Import Dashboard system.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📧 Support

- Check documentation in `/backend` and `/frontend`
- Review API documentation
- Check browser console and server logs
- Test with Postman

## 🎯 Features Roadmap

- [ ] User authentication and authorization
- [ ] Advanced search and filtering
- [ ] Bulk operations
- [ ] Data visualization/charts
- [ ] Email notifications
- [ ] Scheduled imports/exports
- [ ] API rate limiting
- [ ] Audit logging

## ⚡ Performance Tips

- Backend handles up to 1000 concurrent requests
- Frontend uses pagination for large datasets
- File uploads limited to 10MB
- Database auto-optimizes with GORM
- Static assets cached with Vite

## 🔒 Security

- CORS configured for frontend origin
- File upload validation
- SQL injection prevention with GORM
- XSS protection with Laravel
- CSRF protection enabled

---

**Made with ❤️ using Go and Laravel**

**Project Status**: ✅ Production Ready

**Last Updated**: February 2026
