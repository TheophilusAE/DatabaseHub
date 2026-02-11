# API Documentation

Base URL: `http://localhost:8080`

## Health Check

### Check Server Status
```
GET /health
```

**Response:**
```json
{
  "status": "ok",
  "message": "Server is running"
}
```

---

## Data Records API

### Get All Data Records
```
GET /api/v1/data-records?page=1&limit=10
```

**Query Parameters:**
- `page` (optional): Page number, default: 1
- `limit` (optional): Records per page, default: 10

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "Product description",
      "category": "electronics",
      "value": 99.99,
      "status": "active",
      "metadata": "{}",
      "created_at": "2026-02-11T10:00:00Z",
      "updated_at": "2026-02-11T10:00:00Z"
    }
  ],
  "total": 100,
  "page": 1,
  "limit": 10,
  "total_pages": 10
}
```

### Get Data Record by ID
```
GET /api/v1/data-records/:id
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Product Name",
    "description": "Product description",
    "category": "electronics",
    "value": 99.99,
    "status": "active",
    "metadata": "{}",
    "created_at": "2026-02-11T10:00:00Z",
    "updated_at": "2026-02-11T10:00:00Z"
  }
}
```

### Create Data Record
```
POST /api/v1/data-records
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Product Name",
  "description": "Product description",
  "category": "electronics",
  "value": 99.99,
  "status": "active",
  "metadata": "{}"
}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Product Name",
    ...
  },
  "message": "Record created successfully"
}
```

### Update Data Record
```
PUT /api/v1/data-records/:id
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Updated Product Name",
  "description": "Updated description",
  "category": "electronics",
  "value": 149.99,
  "status": "active"
}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Updated Product Name",
    ...
  },
  "message": "Record updated successfully"
}
```

### Delete Data Record
```
DELETE /api/v1/data-records/:id
```

**Response:**
```json
{
  "message": "Record deleted successfully"
}
```

### Get Records by Category
```
GET /api/v1/data-records/category/:category
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "category": "electronics",
      ...
    }
  ]
}
```

---

## Documents API

### Get All Documents
```
GET /api/v1/documents?page=1&limit=10
```

**Query Parameters:**
- `page` (optional): Page number, default: 1
- `limit` (optional): Documents per page, default: 10

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "file_name": "20260211_150405_abc123.pdf",
      "original_name": "document.pdf",
      "file_path": "./uploads/20260211_150405_abc123.pdf",
      "file_size": 102400,
      "file_type": ".pdf",
      "mime_type": "application/pdf",
      "category": "reports",
      "description": "Monthly report",
      "uploaded_by": "user123",
      "status": "active",
      "created_at": "2026-02-11T10:00:00Z",
      "updated_at": "2026-02-11T10:00:00Z"
    }
  ],
  "total": 50,
  "page": 1,
  "limit": 10,
  "total_pages": 5
}
```

### Get Document by ID
```
GET /api/v1/documents/:id
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "file_name": "20260211_150405_abc123.pdf",
    "original_name": "document.pdf",
    ...
  }
}
```

### Upload Document
```
POST /api/v1/documents/upload
Content-Type: multipart/form-data
```

**Supported File Types:** ALL file types are supported (no restrictions)
- Documents: PDF, DOC, DOCX, TXT, RTF, ODT, etc.
- Spreadsheets: XLS, XLSX, CSV, ODS, etc.
- Images: JPG, PNG, GIF, BMP, SVG, WEBP, TIFF, etc.
- Videos: MP4, AVI, MOV, WMV, MKV, FLV, etc.
- Audio: MP3, WAV, AAC, FLAC, OGG, etc.
- Archives: ZIP, RAR, 7Z, TAR, GZ, etc.
- And any other file type!

**Form Data:**
- `file` (required): The file to upload (any file type)
- `category` (optional): Document category
- `description` (optional): Document description

**Example using cURL:**
```bash
# Upload PDF
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@document.pdf" \
  -F "category=reports" \
  -F "description=Monthly sales report"

# Upload Excel
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@data.xlsx" \
  -F "category=spreadsheets"

# Upload image
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@photo.jpg" \
  -F "category=images"

# Upload video
curl -X POST http://localhost:8080/api/v1/documents/upload \
  -F "file=@presentation.mp4" \
  -F "category=videos"
```

**Response:**
```json
{
  "message": "File uploaded successfully",
  "document": {
    "id": 1,
    "file_name": "20260211_150405_abc123.pdf",
    "original_name": "document.pdf",
    ...
  }
}
```

### Download Document
```
GET /api/v1/documents/download/:id
```

**Response:**
- File download with appropriate headers

### Delete Document
```
DELETE /api/v1/documents/:id
```

**Response:**
```json
{
  "message": "Document deleted successfully"
}
```

### Get Documents by Category
```
GET /api/v1/documents/category/:category
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "category": "reports",
      ...
    }
  ]
}
```

---

## Import API

### Import CSV File
```
POST /api/v1/import/csv
Content-Type: multipart/form-data
```

**Form Data:**
- `file` (required): CSV file to import

**CSV Format:**
```csv
name,description,category,value,status,metadata
Product A,Description A,electronics,99.99,active,"{""key"":""value""}"
```

**Example using cURL:**
```bash
curl -X POST http://localhost:8080/api/v1/import/csv \
  -F "file=@data.csv"
```

**Response:**
```json
{
  "message": "Import completed",
  "total": 100,
  "success": 98,
  "failed": 2,
  "import_log_id": 1
}
```

### Import JSON File
```
POST /api/v1/import/json
Content-Type: multipart/form-data
```

**Form Data:**
- `file` (required): JSON file to import

**JSON Format:**
```json
[
  {
    "name": "Product A",
    "description": "Description A",
    "category": "electronics",
    "value": 99.99,
    "status": "active",
    "metadata": "{}"
  }
]
```

**Example using cURL:**
```bash
curl -X POST http://localhost:8080/api/v1/import/json \
  -F "file=@data.json"
```

**Response:**
```json
{
  "message": "Import completed",
  "total": 50,
  "import_log_id": 2
}
```

### Get Import Logs
```
GET /api/v1/import/logs?page=1&limit=10
```

**Query Parameters:**
- `page` (optional): Page number, default: 1
- `limit` (optional): Logs per page, default: 10

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "file_name": "data.csv",
      "import_type": "csv",
      "total_records": 100,
      "success_count": 98,
      "failure_count": 2,
      "status": "completed",
      "error_message": "",
      "imported_by": "user123",
      "created_at": "2026-02-11T10:00:00Z",
      "updated_at": "2026-02-11T10:05:00Z"
    }
  ],
  "total": 20,
  "page": 1,
  "limit": 10,
  "total_pages": 2
}
```

### Get Import Log by ID
```
GET /api/v1/import/logs/:id
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "file_name": "data.csv",
    "import_type": "csv",
    "total_records": 100,
    "success_count": 98,
    "failure_count": 2,
    "status": "completed",
    ...
  }
}
```

---

## Export API

### Export to CSV
```
GET /api/v1/export/csv?category=electronics
```

**Query Parameters:**
- `category` (optional): Filter by category

**Response:**
- CSV file download

**Example using cURL:**
```bash
curl -O http://localhost:8080/api/v1/export/csv
curl -O http://localhost:8080/api/v1/export/csv?category=electronics
```

### Export to JSON
```
GET /api/v1/export/json?category=electronics
```

**Query Parameters:**
- `category` (optional): Filter by category

**Response:**
- JSON file download

**Example using cURL:**
```bash
curl -O http://localhost:8080/api/v1/export/json
curl -O http://localhost:8080/api/v1/export/json?category=books
```

### Export to Excel
```
GET /api/v1/export/excel
```

**Response:**
```json
{
  "message": "Excel export not yet implemented. Use CSV or JSON export for now."
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Invalid input or missing required fields"
}
```

### 404 Not Found
```json
{
  "error": "Record not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error message"
}
```

---

## Status Codes

- `200 OK` - Successful GET, PUT, DELETE requests
- `201 Created` - Successful POST requests
- `400 Bad Request` - Invalid input
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

---

## Notes

1. All timestamps are in RFC3339 format (ISO 8601)
2. Pagination starts at page 1
3. Default page size is 10 records
4. Maximum export limit is 10,000 records
5. Maximum file upload size is configured in `.env` (default: 10MB, adjustable)
6. Soft deletes are used (records are not permanently deleted)
7. **ALL file types are supported** - no restrictions on file extensions or types
8. Empty files (0 bytes) are rejected
9. MIME types are automatically detected and stored
