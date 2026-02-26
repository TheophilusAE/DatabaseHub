# DataBridge - Backend (Go)

A robust Go backend API for importing and exporting data and documents from a database. Built with Gin framework and GORM ORM.

## Features

- ðŸ“Š **Data Import/Export**: Support for CSV and JSON formats
- ðŸ“ **Document Management**: Upload, download, and manage **ALL file types** (PDF, Word, Excel, images, videos, ZIP, etc.)
- ðŸ—„ï¸ **Database Support**: MySQL and PostgreSQL
- ðŸ“ **Import Logging**: Track all import operations with detailed logs
- ðŸ” **Pagination**: Efficient data retrieval with pagination
- ðŸ·ï¸ **Categories**: Organize data and documents by categories
- ðŸ”„ **Remote Database**: Connect to database running on another device
- ðŸŒ **CORS Support**: Ready for frontend integration with Laravel
- ðŸ“¦ **Universal File Support**: No restrictions on file types or extensions

## Project Structure

```
backend/
â”œâ”€â”€ config/              # Configuration and database setup
â”‚   â”œâ”€â”€ config.go        # Application configuration
â”‚   â””â”€â”€ database.go      # Database connection
â”œâ”€â”€ models/              # Data models
â”‚   â”œâ”€â”€ data_record.go   # Generic data record model
â”‚   â”œâ”€â”€ document.go      # Document/file model
â”‚   â””â”€â”€ import_log.go    # Import operation tracking
â”œâ”€â”€ repository/          # Database operations
â”‚   â”œâ”€â”€ data_record_repository.go
â”‚   â”œâ”€â”€ document_repository.go
â”‚   â””â”€â”€ import_log_repository.go
â”œâ”€â”€ handlers/            # HTTP request handlers
â”‚   â”œâ”€â”€ data_record_handler.go
â”‚   â”œâ”€â”€ document_handler.go
â”‚   â”œâ”€â”€ import_handler.go
â”‚   â””â”€â”€ export_handler.go
â”œâ”€â”€ middleware/          # Middleware functions
â”‚   â””â”€â”€ cors.go          # CORS configuration
â”œâ”€â”€ routes/              # API route definitions
â”‚   â””â”€â”€ routes.go
â”œâ”€â”€ uploads/             # File upload directory (auto-created)
â”œâ”€â”€ main.go              # Application entry point
â”œâ”€â”€ go.mod               # Go module dependencies
â””â”€â”€ .env                 # Environment configuration
```

## Prerequisites

- Go 1.21 or higher
- MySQL 8.0+ or PostgreSQL 12+ running on local or remote device
- Git (optional)

## Installation

### 1. Clone or Navigate to the Project

```bash
cd d:\DataImportDashboard\backend
```

### 2. Install Dependencies

```bash
go mod download
```

### 3. Configure Environment

Copy `.env.example` to `.env`:

```bash
copy .env.example .env
```

Edit `.env` with your database configuration:

**For MySQL (running on another device):**
```env
PORT=8080
ENV=development

DB_TYPE=mysql
DB_HOST=192.168.1.100        # Replace with your database server IP
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=data_import_db

UPLOAD_PATH=./uploads
MAX_UPLOAD_SIZE=10485760

ALLOWED_ORIGINS=http://localhost,http://localhost:8000
```

**For PostgreSQL:**
```env
DB_TYPE=postgres
DB_HOST=192.168.1.100        # Replace with your database server IP
DB_PORT=5432
DB_USER=postgres
DB_PASSWORD=your_password
DB_NAME=data_import_db
```

### 4. Create Database

On your remote database server, create the database:

**MySQL:**
```sql
CREATE DATABASE data_import_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**PostgreSQL:**
```sql
CREATE DATABASE data_import_db;
```

### 5. Run the Application

```bash
go run main.go
```

The server will start on `http://localhost:8080` and automatically create the necessary tables.

## API Endpoints

### Health Check
- `GET /health` - Check server status

### Data Records
- `GET /api/v1/data-records` - Get all records (paginated)
  - Query params: `page=1&limit=10`
- `GET /api/v1/data-records/:id` - Get record by ID
- `POST /api/v1/data-records` - Create new record
- `PUT /api/v1/data-records/:id` - Update record
- `DELETE /api/v1/data-records/:id` - Delete record
- `GET /api/v1/data-records/category/:category` - Get records by category

### Documents
- `GET /api/v1/documents` - Get all documents (paginated)
- `GET /api/v1/documents/:id` - Get document by ID
- `POST /api/v1/documents/upload` - Upload document
- `GET /api/v1/documents/download/:id` - Download document
- `DELETE /api/v1/documents/:id` - Delete document
- `GET /api/v1/documents/category/:category` - Get documents by category

### Import
- `POST /api/v1/import/csv` - Import data from CSV file
- `POST /api/v1/import/json` - Import data from JSON file
- `GET /api/v1/import/logs` - Get import logs (paginated)
- `GET /api/v1/import/logs/:id` - Get import log by ID

### Export
- `GET /api/v1/export/csv` - Export data to CSV
  - Query params: `category=optional`
- `GET /api/v1/export/json` - Export data to JSON
  - Query params: `category=optional`
- `GET /api/v1/export/excel` - Export to Excel (not yet implemented)

## Usage Examples

### Import CSV File

**CSV File Format (example.csv):**
```csv
name,description,category,value,status,metadata
Product A,Description A,electronics,99.99,active,{"color":"red"}
Product B,Description B,books,29.99,active,{"author":"John"}
```

**Request:**
```bash
curl -X POST http://localhost:8080/api/v1/import/csv \
  -F "file=@example.csv"
```

### Import JSON File

**JSON File Format (example.json):**
```json
[
  {
    "name": "Product A",
    "description": "Description A",
    "category": "electronics",
    "value": 99.99,
    "status": "active"
  },
  {
    "name": "Product B",
    "description": "Description B",
    "category": "books",
    "value": 29.99,
    "status": "active"
  }
]
```

**Request:**
```bash
curl -X POST http://localhost:8080/api/v1/import/json \
  -F "file=@example.json"
```

### Export Data to CSV

```bash
curl -O http://localhost:8080/api/v1/export/csv
```

### Upload Document (All File Types Supported)

The API supports **ALL file types** including:
- **Documents**: PDF, DOC, DOCX, TXT, RTF, ODT
- **Spreadsheets**: XLS, XLSX, CSV, ODS
- **Images**: JPG, PNG, GIF, BMP, SVG, WEBP, TIFF
- **Videos**: MP4, AVI, MOV, WMV, MKV, FLV
- **Audio**: MP3, WAV, AAC, FLAC, OGG
- **Archives**: ZIP, RAR, 7Z, TAR, GZ
- **CAD/Design**: DWG, DXF, PSD, AI, SKETCH
- **Code**: JS, PY, GO, JAVA, CPP, etc.
- **And any other file type!**

```bash
# Upload PDF
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@document.pdf" \
  -F "category=reports" \
  -F "description=Monthly report"

# Upload Excel file
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@data.xlsx" \
  -F "category=spreadsheets"

# Upload image
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@photo.jpg" \
  -F "category=images"

# Upload video
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@video.mp4" \
  -F "category=videos"

# Upload any other file type
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@archive.zip" \
  -F "category=archives"
```

### Create Data Record

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

### Get All Records with Pagination

```bash
curl http://localhost:8080/api/v1/data-records?page=1&limit=10
```

## Database Schema

### DataRecord Table
- `id` - Primary key
- `name` - Record name (required)
- `description` - Text description
- `category` - Category tag
- `value` - Numeric value
- `status` - Status (active/inactive)
- `metadata` - JSON metadata
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp
- `deleted_at` - Soft delete timestamp

### Document Table
- `id` - Primary key
- `file_name` - Stored filename
- `original_name` - Original filename
- `file_path` - Path on disk
- `file_size` - Size in bytes
- `file_type` - File extension
- `mime_type` - MIME type
- `category` - Category tag
- `description` - Description
- `uploaded_by` - Uploader info
- `status` - Status
- `created_at` - Upload timestamp
- `updated_at` - Update timestamp
- `deleted_at` - Soft delete timestamp

### ImportLog Table
- `id` - Primary key
- `file_name` - Imported filename
- `import_type` - Type (csv/json)
- `total_records` - Total records
- `success_count` - Success count
- `failure_count` - Failure count
- `status` - Status (pending/processing/completed/failed)
- `error_message` - Error details
- `imported_by` - Importer info
- `created_at` - Import timestamp
- `updated_at` - Update timestamp

## Remote Database Configuration

### MySQL Remote Access

On your database server, grant remote access:

```sql
CREATE USER 'your_user'@'%' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON data_import_db.* TO 'your_user'@'%';
FLUSH PRIVILEGES;
```

Edit MySQL configuration (`my.cnf` or `my.ini`):
```ini
[mysqld]
bind-address = 0.0.0.0
```

Restart MySQL service.

### PostgreSQL Remote Access

Edit `postgresql.conf`:
```
listen_addresses = '*'
```

Edit `pg_hba.conf`:
```
host    all    all    0.0.0.0/0    md5
```

Restart PostgreSQL service.

### Firewall Configuration

Open the database port on your firewall:
- MySQL: Port 3306
- PostgreSQL: Port 5432

## Building for Production

### Build Binary

```bash
go build -o data-import-api.exe main.go
```

### Run in Production

1. Set environment to production in `.env`:
```env
ENV=production
```

2. Run the binary:
```bash
.\data-import-api.exe
```

## Testing with Postman/Thunder Client

Import these example requests:

1. **Health Check**: GET `http://localhost:8080/health`
2. **Create Record**: POST `http://localhost:8080/api/v1/data-records`
3. **Import CSV**: POST `http://localhost:8080/api/v1/import/csv`
4. **Export CSV**: GET `http://localhost:8080/api/v1/export/csv`

## Troubleshooting

### Database Connection Issues

1. Verify database is running:
   - MySQL: `mysql -h 192.168.1.100 -u root -p`
   - PostgreSQL: `psql -h 192.168.1.100 -U postgres`

2. Check firewall allows connections on database port

3. Verify credentials in `.env` file

4. Check database server logs for connection attempts

### Import Failures

- Ensure CSV headers match expected format
- JSON must be valid array of objects
- Check import logs: GET `/api/v1/import/logs`

### File Upload Issues

- Check `UPLOAD_PATH` directory permissions
- Verify `MAX_UPLOAD_SIZE` setting (default: 10MB, increase if needed)
- Ensure disk space is available
- **All file types are supported** - no extension restrictions
- Empty files (0 bytes) will be rejected

## Next Steps - Laravel Frontend Integration

When ready for Laravel frontend:

1. Update `ALLOWED_ORIGINS` in `.env`:
```env
ALLOWED_ORIGINS=http://localhost:8000,http://your-laravel-domain
```

2. Use Laravel HTTP client to call APIs:
```php
$response = Http::get('http://localhost:8080/api/v1/data-records');
$data = $response->json();
```

3. Handle file uploads from Laravel:
```php
$response = Http::attach(
    'file', file_get_contents($file->path()), $file->getClientOriginalName()
)->post('http://localhost:8080/api/v1/import/csv');
```

## License

MIT License

## Support

For issues or questions, please create an issue in the repository.

