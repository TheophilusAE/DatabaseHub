# DataBridge (Data Import Dashboard)

DataBridge is a full-stack dashboard for importing, exporting, viewing, and managing business data and uploaded files.

- Backend: Go + Gin + GORM (`backend/`)
- Frontend: Laravel + Blade + Vite (`frontend/`)
- Supported database engines: MySQL and PostgreSQL

---

## What this app does

- Manage tabular records (create, read, update, delete)
- Import data from CSV and JSON files
- Export data to CSV, JSON, and Excel
- Upload and manage documents/files
- Track import history and result status
- Enforce role-based access control (admin/user)
- Auto-discover and sync table configuration from connected databases

---

## Architecture

- Frontend runs at `http://localhost:8000`
- Backend API runs at `http://localhost:8080`
- Frontend calls backend REST endpoints

Typical local flow:

1. Start backend server (`backend/start.bat`)
2. Start frontend server (`frontend/start.bat`)
3. Open browser and use UI on port 8000

---

## Requirements

### Backend

- Go 1.21+
- MySQL 8+ or PostgreSQL 12+

### Frontend

- PHP 8.2+
- Composer
- Node.js 18+
- npm

---

## Quick start (Windows)

### Option A: Start both services from project root

Run one of these scripts from the root folder:

- `START_APPLICATION.bat`
- `start-app.bat`

This starts backend and frontend in separate terminal windows.

### Option B: Start each service manually

1) Backend

```powershell
cd backend
.\start.bat
```

2) Frontend

```powershell
cd frontend
.\start.bat
```

3) Open the app

- `http://localhost:8000`

---

## Default test accounts

If your database is seeded with default users, you can use:

- Admin: `admin@example.com` / `admin123`
- User: `test@example.com` / `password123`

If these do not exist in your environment, create an admin user with:

```powershell
cd frontend
.\create-admin.bat
```

---

## User roles and access

### Admin

- Full CRUD for records, documents, import/export
- User management and role assignment
- Database auto-discovery and table sync configuration

### User

- View-only access for protected areas (based on RBAC rules)
- Can access permitted pages and actions configured by the system

See full RBAC docs: [RBAC_GUIDE.md](RBAC_GUIDE.md)

---

## Core workflows

### 1) Import data

- Go to import page
- Upload CSV or JSON
- Review validation/result summary
- Check import history for logs

Reference format guide: [backend/JSON_IMPORT_GUIDE.md](backend/JSON_IMPORT_GUIDE.md)

### 2) Export data

- Use export feature in UI
- Choose output format: CSV / JSON / Excel
- Download generated file

### 3) Manage documents

- Upload file(s)
- Add category/metadata
- Download or delete when needed

### 4) Auto-discover database tables (admin)

- Add database connection
- Run table discovery
- Review detected schema
- Sync selected/all tables

References:
- [AUTO_DISCOVERY_QUICK_START.md](AUTO_DISCOVERY_QUICK_START.md)
- [AUTO_DISCOVERY_GUIDE.md](AUTO_DISCOVERY_GUIDE.md)

---

## Important endpoints

Use backend base URL: `http://localhost:8080`

- `GET /health` - service health check
- `POST /upload/csv` - import CSV
- `POST /upload/json` - import JSON
- `GET /download/csv` - export CSV
- `GET /download/json` - export JSON
- `GET /download/excel` - export Excel

For full API details: [backend/API_DOCUMENTATION.md](backend/API_DOCUMENTATION.md)

---

## Project structure

```text
DataImportDashboard/
├─ backend/      # Go API, handlers, models, routes, DB config
├─ frontend/     # Laravel app, views, JS/CSS, auth
├─ stress_tests/ # Performance and upload test scripts/sample files
└─ *.md          # Setup, RBAC, auth, auto-discovery and usage docs
```

---

## Configuration notes

### Backend

- Main backend env config: `backend/.env`
- Key variables include DB type/host/port/user/password/name and server port

### Frontend

- Frontend env config: `frontend/.env`
- Frontend API URL is configured in `frontend/resources/js/app.js` (`window.API_BASE_URL`)

### Backend `.env` example

Use `backend/.env` to configure the API server and database.

MySQL example:

```env
PORT=8080
ENV=development

DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=data_import_db

UPLOAD_PATH=./uploads
MAX_UPLOAD_SIZE=10485760
ALLOWED_ORIGINS=http://localhost,http://localhost:8000
```

PostgreSQL example:

```env
DB_TYPE=postgres
DB_HOST=127.0.0.1
DB_PORT=5432
DB_USER=postgres
DB_PASSWORD=your_password
DB_NAME=data_import_db
```

---

## Backend API quick usage

### Import CSV

```bash
curl -X POST http://localhost:8080/api/v1/import/csv \
	-F "file=@example.csv"
```

### Import JSON

```bash
curl -X POST http://localhost:8080/api/v1/import/json \
	-F "file=@example.json"
```

### Export CSV

```bash
curl -O http://localhost:8080/api/v1/export/csv
```

### Create data record

```bash
curl -X POST http://localhost:8080/api/v1/data-records \
	-H "Content-Type: application/json" \
	-d '{
		"name": "Sample Product",
		"description": "Sample description",
		"category": "electronics",
		"value": 149.99,
		"status": "active"
	}'
```

### File upload support

- Document uploads support all common file types (documents, spreadsheets, images, video, audio, archives, and more)
- Empty files are rejected
- Max upload size is controlled by `MAX_UPLOAD_SIZE`

---

## Remote database setup (optional)

If your DB runs on another machine, enable remote access and open firewall ports.

### MySQL

```sql
CREATE USER 'your_user'@'%' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON data_import_db.* TO 'your_user'@'%';
FLUSH PRIVILEGES;
```

In `my.cnf`/`my.ini`:

```ini
[mysqld]
bind-address = 0.0.0.0
```

### PostgreSQL

In `postgresql.conf`:

```ini
listen_addresses = '*'
```

In `pg_hba.conf`:

```ini
host    all    all    0.0.0.0/0    md5
```

Default DB ports:

- MySQL: `3306`
- PostgreSQL: `5432`

---

## Backend production run

Build:

```bash
cd backend
go build -o data-import-api.exe main.go
```

Run:

```bash
./data-import-api.exe
```

---

## Troubleshooting

### Backend not reachable

- Confirm backend is running on `http://localhost:8080`
- Check firewall/port conflicts

### Frontend cannot load assets

- In `frontend/`, run `npm install` then `npm run build`
- Clear Laravel view cache if needed:

```powershell
php artisan view:clear
```

### Port already in use

- Identify process using the port and stop it
- Or run Laravel on another port, e.g. `php artisan serve --port=8001`

### Cannot login as admin

- Create or promote an admin account using [CREATE_ADMIN_POSTGRES.md](CREATE_ADMIN_POSTGRES.md) or `frontend/create-admin.bat`

### Backend database connection issues

- Verify DB host/port/user/password/name in `backend/.env`
- Verify DB service is reachable from the backend machine
- Verify firewall allows DB traffic (`3306` for MySQL, `5432` for PostgreSQL)

### Import failures

- CSV headers must match expected fields
- JSON must be a valid array of objects
- Check import logs from API/UI history pages

---

## Documentation map

### General

- [USER_GUIDE.md](USER_GUIDE.md)
- [AUTHENTICATION_GUIDE.md](AUTHENTICATION_GUIDE.md)
- [DOKUMENTASI_HALAMAN_DAN_FITUR.md](DOKUMENTASI_HALAMAN_DAN_FITUR.md)

### RBAC and permissions

- [RBAC_GUIDE.md](RBAC_GUIDE.md)
- [QUICK_START_RBAC.md](QUICK_START_RBAC.md)
- [TABLE_PERMISSIONS_GUIDE.md](TABLE_PERMISSIONS_GUIDE.md)
- [ADMIN_ONLY_RESTRICTIONS.md](ADMIN_ONLY_RESTRICTIONS.md)

### Auto-discovery

- [AUTO_DISCOVERY_QUICK_START.md](AUTO_DISCOVERY_QUICK_START.md)
- [AUTO_DISCOVERY_GUIDE.md](AUTO_DISCOVERY_GUIDE.md)

### Backend technical docs

- [backend/API_DOCUMENTATION.md](backend/API_DOCUMENTATION.md)
- [backend/QUICK_START.md](backend/QUICK_START.md)
- [backend/POSTMAN_TESTING_GUIDE.md](backend/POSTMAN_TESTING_GUIDE.md)
- [backend/HUMAN_ROUTES_GUIDE.md](backend/HUMAN_ROUTES_GUIDE.md)
- [backend/JSON_IMPORT_GUIDE.md](backend/JSON_IMPORT_GUIDE.md)

### Database setup

- [POSTGRES_SETUP.md](POSTGRES_SETUP.md)
- [CREATE_ADMIN_POSTGRES.md](CREATE_ADMIN_POSTGRES.md)

---

## Support checklist

When reporting an issue, include:

- What you were trying to do
- Exact error message / screenshot
- Whether backend (`:8080`) and frontend (`:8000`) are both running
- Database type (MySQL/PostgreSQL)
- Relevant log output from terminal
