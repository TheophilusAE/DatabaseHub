# Quick Start Guide

## Prerequisites Check

Before starting, ensure you have:
- ✅ Go 1.21 or higher installed
- ✅ MySQL or PostgreSQL database server (local or remote)
- ✅ Database created and credentials ready

## Step-by-Step Setup

### 1. Install Go (if not installed)

Download from: https://golang.org/dl/

Verify installation:
```bash
go version
```

### 2. Configure Database

**Option A: Using MySQL**

On your database server, run:
```sql
CREATE DATABASE data_import_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'datauser'@'%' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON data_import_db.* TO 'datauser'@'%';
FLUSH PRIVILEGES;
```

**Option B: Using PostgreSQL**

On your database server, run:
```sql
CREATE DATABASE data_import_db;
CREATE USER datauser WITH PASSWORD  'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE data_import_db TO datauser;
```

### 3. Configure Application

Edit the `.env` file in the `backend` folder:

**For Local MySQL:**
```env
PORT=8080
ENV=development

DB_TYPE=mysql
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=data_import_db

UPLOAD_PATH=./uploads
MAX_UPLOAD_SIZE=10485760
ALLOWED_ORIGINS=http://localhost,http://localhost:8000
```

**For Remote MySQL:**
```env
PORT=8080
ENV=development

DB_TYPE=mysql
DB_HOST=192.168.1.100    # Your database server IP
DB_PORT=3306
DB_USER=datauser
DB_PASSWORD=your_secure_password
DB_NAME=data_import_db

UPLOAD_PATH=./uploads
MAX_UPLOAD_SIZE=10485760
ALLOWED_ORIGINS=http://localhost,http://localhost:8000
```

**For PostgreSQL:**
```env
DB_TYPE=postgres
DB_HOST=localhost   # or remote IP
DB_PORT=5432
DB_USER=postgres
DB_PASSWORD=your_password
DB_NAME=data_import_db
```

### 4. Start the Server

**Option A: Using the start script (Recommended)**
```bash
.\start.bat
```

**Option B: Manual start**
```bash
# Install dependencies
go mod download

# Run the server
go run main.go
```

**Option C: Build and run executable**
```bash
# Build
go build -o backend.exe main.go

# Run
.\backend.exe
```

### 5. Verify Installation

Open your browser or use curl:
```bash
curl http://localhost:8080/health
```

Expected response:
```json
{
  "status": "ok",
  "message": "Server is running"
}
```

## Testing the API

### Test Data Import

1. Import sample CSV:
```bash
curl -X POST http://localhost:8080/api/v1/import/csv -F "file=@sample_data.csv"
```

2. Import sample JSON:
```bash
curl -X POST http://localhost:8080/api/v1/import/json -F "file=@sample_data.json"
```

### Test Data Retrieval

Get all records:
```bash
curl http://localhost:8080/api/v1/data-records
```

### Test Data Export

Export to CSV:
```bash
curl -O http://localhost:8080/api/v1/export/csv
```

Export to JSON:
```bash
curl -O http://localhost:8080/api/v1/export/json
```

### Test Document Upload

Upload a file:
```bash
curl -X POST http://localhost:8080/api/v1/documents/upload -F "file=@yourfile.pdf" -F "category=documents"
```

## Common Issues and Solutions

### Issue 1: "Failed to connect to database"

**Solution:**
1. Verify database server is running
2. Check credentials in `.env` file
3. Ensure database `data_import_db` exists
4. Check firewall allows connection on database port
5. For remote database, ensure it accepts remote connections

### Issue 2: "go: command not found"

**Solution:**
Install Go from https://golang.org/dl/ and add to PATH

### Issue 3: "Port 8080 already in use"

**Solution:**
Change PORT in `.env` file to another port (e.g., 8081)

### Issue 4: "missing go.sum entry"

**Solution:**
```bash
go mod tidy
go mod download
```

### Issue 5: Remote MySQL connection refused

**Solution:**
1. Edit MySQL config (my.cnf or my.ini):
   ```ini
   [mysqld]
   bind-address = 0.0.0.0
   ```
2. Restart MySQL service
3. Open port 3306 in firewall

### Issue 6: Remote PostgreSQL connection refused

**Solution:**
1. Edit postgresql.conf:
   ```
   listen_addresses = '*'
   ```
2. Edit pg_hba.conf:
   ```
   host    all    all    0.0.0.0/0    md5
   ```
3. Restart PostgreSQL service
4. Open port 5432 in firewall

## Server Output

When the server starts successfully, you'll see:
```
=========================================
Data Import Dashboard - Backend Server
=========================================

Configuration loaded successfully
Server Port: 8080
Database Type: mysql
Database Host: localhost:3306

Connecting to database...
Running database migrations...
✓ Database migration completed successfully

=========================================
✓ Server is ready and running!
=========================================
  URL:         http://localhost:8080
  Health:      http://localhost:8080/health
  API Docs:    See API_DOCUMENTATION.md
  Environment: development
=========================================

Press Ctrl+C to stop the server
```

## Next Steps

1. ✅ Server is running
2. 📖 Read [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for all endpoints
3. 🧪 Test with sample data (sample_data.csv and sample_data.json)
4. 🚀 Build your Laravel frontend to connect to this API
5. 📝 Customize data models in `/models` if needed

## Production Deployment

When ready for production:

1. Update `.env`:
   ```env
   ENV=production
   PORT=8080
   ```

2. Build optimized executable:
   ```bash
   go build -ldflags="-s -w" -o backend.exe main.go
   ```

3. Run as Windows service or use a process manager

4. Set up SSL/TLS certificate for HTTPS

5. Configure reverse proxy (nginx/Apache) if needed

## Support

For detailed API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

For project structure and features, see [README.md](README.md)
