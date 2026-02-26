# Postman Testing Guide

Complete guide to test the DataBridge Backend API using Postman.

## Prerequisites

1. âœ… Backend server is running on `http://localhost:8080`
2. âœ… Postman installed (Download: https://www.postman.com/downloads/)
3. âœ… Database is configured and connected

---

## Quick Setup

### Start the Server
```bash
.\start.bat
```

Wait for:
```
âœ“ Server is ready and running!
URL: http://localhost:8080
```

---

## Test 1: Health Check

**Purpose:** Verify server is running

**Method:** `GET`  
**URL:** `http://localhost:8080/health`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/health`
4. Click **Send**

**Expected Response:**
```json
{
    "status": "ok",
    "message": "Server is running"
}
```

**Status Code:** `200 OK`

---

## Test 2: Create Data Record

**Purpose:** Add a new data record

**Method:** `POST`  
**URL:** `http://localhost:8080/data`

**Steps in Postman:**
1. Create new request
2. Set method to `POST`
3. Enter URL: `http://localhost:8080/data`
4. Go to **Body** tab
5. Select **raw** and **JSON** format
6. Paste this JSON:

```json
{
    "name": "MacBook Pro",
    "description": "16-inch M3 Max laptop",
    "category": "electronics",
    "value": 2499.99,
    "status": "active"
}
```

7. Click **Send**

**Expected Response:**
```json
{
    "data": {
        "id": 1,
        "name": "MacBook Pro",
        "description": "16-inch M3 Max laptop",
        "category": "electronics",
        "value": 2499.99,
        "status": "active",
        "metadata": "",
        "created_at": "2026-02-11T10:00:00Z",
        "updated_at": "2026-02-11T10:00:00Z"
    },
    "message": "Record created successfully"
}
```

**Status Code:** `201 Created`

---

## Test 3: Get All Records (with Pagination)

**Purpose:** Retrieve all data records

**Method:** `GET`  
**URL:** `http://localhost:8080/data?page=1&limit=10`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/data`
4. Go to **Params** tab
5. Add parameters:
   - Key: `page`, Value: `1`
   - Key: `limit`, Value: `10`
6. Click **Send**

**Expected Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "MacBook Pro",
            "description": "16-inch M3 Max laptop",
            "category": "electronics",
            "value": 2499.99,
            "status": "active",
            "metadata": "",
            "created_at": "2026-02-11T10:00:00Z",
            "updated_at": "2026-02-11T10:00:00Z"
        }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "total_pages": 1
}
```

**Status Code:** `200 OK`

---

## Test 4: Get Single Record by ID

**Purpose:** Retrieve a specific record

**Method:** `GET`  
**URL:** `http://localhost:8080/data/1`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/data/1`
   - Replace `1` with your record ID
4. Click **Send**

**Expected Response:**
```json
{
    "data": {
        "id": 1,
        "name": "MacBook Pro",
        "description": "16-inch M3 Max laptop",
        "category": "electronics",
        "value": 2499.99,
        "status": "active",
        "metadata": "",
        "created_at": "2026-02-11T10:00:00Z",
        "updated_at": "2026-02-11T10:00:00Z"
    }
}
```

**Status Code:** `200 OK`

---

## Test 5: Update Data Record

**Purpose:** Update an existing record

**Method:** `PUT`  
**URL:** `http://localhost:8080/data/1`

**Steps in Postman:**
1. Create new request
2. Set method to `PUT`
3. Enter URL: `http://localhost:8080/data/1`
4. Go to **Body** tab
5. Select **raw** and **JSON**
6. Paste:

```json
{
    "name": "MacBook Pro Updated",
    "description": "16-inch M3 Max laptop - Updated",
    "category": "electronics",
    "value": 2399.99,
    "status": "active"
}
```

7. Click **Send**

**Expected Response:**
```json
{
    "data": {
        "id": 1,
        "name": "MacBook Pro Updated",
        "description": "16-inch M3 Max laptop - Updated",
        "category": "electronics",
        "value": 2399.99,
        "status": "active",
        "metadata": "",
        "created_at": "2026-02-11T10:00:00Z",
        "updated_at": "2026-02-11T10:05:00Z"
    },
    "message": "Record updated successfully"
}
```

**Status Code:** `200 OK`

---

## Test 6: Import CSV File

**Purpose:** Import multiple records from CSV

**Method:** `POST`  
**URL:** `http://localhost:8080/upload/csv`

**Steps in Postman:**
1. Create new request
2. Set method to `POST`
3. Enter URL: `http://localhost:8080/upload/csv`
4. Go to **Body** tab
5. Select **form-data**
6. Add key: `file`
7. Change type from "Text" to **File** (hover over key, click dropdown)
8. Click **Select Files** and choose `sample_data.csv`
9. Click **Send**

**Expected Response:**
```json
{
    "message": "Import completed",
    "total": 8,
    "success": 8,
    "failed": 0,
    "import_log_id": 1
}
```

**Status Code:** `200 OK`

---

## Test 7: Import JSON File

**Purpose:** Import multiple records from JSON

**Method:** `POST`  
**URL:** `http://localhost:8080/upload/json`

**Steps in Postman:**
1. Create new request
2. Set method to `POST`
3. Enter URL: `http://localhost:8080/upload/json`
4. Go to **Body** tab
5. Select **form-data**
6. Add key: `file`
7. Change type to **File** (hover over key, click dropdown)
8. Click **Select Files** and choose `sample_data.json`
9. Click **Send**

**Expected Response:**
```json
{
    "message": "Import completed successfully",
    "total": 6,
    "success": 6,
    "failed": 0,
    "import_log_id": 2
}
```

**Status Code:** `200 OK`

**Note:** The `sample_data.json` file includes 6 records with various scenarios:
- Records with metadata
- Records without metadata field
- Records with null metadata

For detailed JSON format requirements, see: `JSON_IMPORT_GUIDE.md`

---

## Test 8: Get Import Logs

**Purpose:** View import history

**Method:** `GET`  
**URL:** `http://localhost:8080/upload/history?page=1&limit=10`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/upload/history`
4. Add query params: `page=1`, `limit=10`
5. Click **Send**

**Expected Response:**
```json
{
    "data": [
        {
            "id": 1,
            "file_name": "sample_data.csv",
            "import_type": "csv",
            "total_records": 8,
            "success_count": 8,
            "failure_count": 0,
            "status": "completed",
            "error_message": "",
            "imported_by": "",
            "created_at": "2026-02-11T10:00:00Z",
            "updated_at": "2026-02-11T10:00:05Z"
        }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "total_pages": 1
}
```

**Status Code:** `200 OK`

---

## Test 9: Export to CSV

**Purpose:** Export all records to CSV file

**Method:** `GET`  
**URL:** `http://localhost:8080/download/csv`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/download/csv`
4. Click **Send**
5. Click **Save Response** â†’ **Save to a file**

**Expected Response:**
- CSV file download
- Content-Type: `text/csv`
- Filename: `data_export_YYYYMMDD_HHMMSS.csv`

**Status Code:** `200 OK`

**Optional - Export by Category:**
URL: `http://localhost:8080/download/csv?category=electronics`

---

## Test 10: Export to JSON

**Purpose:** Export all records to JSON file

**Method:** `GET`  
**URL:** `http://localhost:8080/download/json`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/download/json`
4. Click **Send**
5. Click **Save Response** â†’ **Save to a file**

**Expected Response:**
- JSON file download
- Content-Type: `application/json`

**Status Code:** `200 OK`

---

## Test 11: Upload Document (Any File Type)

**Purpose:** Upload a document/file

**Method:** `POST`  
**URL:** `http://localhost:8080/documents`

**Steps in Postman:**
1. Create new request
2. Set method to `POST`
3. Enter URL: `http://localhost:8080/documents`
4. Go to **Body** tab
5. Select **form-data**
6. Add keys:
   - Key: `file`, Type: **File** â†’ Select any file (PDF, image, video, etc.)
   - Key: `category`, Type: Text, Value: `reports`
   - Key: `description`, Type: Text, Value: `Test document upload`
7. Click **Send**

**Expected Response:**
```json
{
    "message": "File uploaded successfully",
    "document": {
        "id": 1,
        "file_name": "20260211_150405_abc123.pdf",
        "original_name": "report.pdf",
        "file_path": "./uploads/20260211_150405_abc123.pdf",
        "file_size": 102400,
        "file_type": ".pdf",
        "mime_type": "application/pdf",
        "category": "reports",
        "description": "Test document upload",
        "uploaded_by": "anonymous",
        "status": "active",
        "created_at": "2026-02-11T15:04:05Z",
        "updated_at": "2026-02-11T15:04:05Z"
    }
}
```

**Status Code:** `201 Created`

---

## Test 12: Get All Documents

**Purpose:** List all uploaded documents

**Method:** `GET`  
**URL:** `http://localhost:8080/documents?page=1&limit=10`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/documents`
4. Add query params: `page=1`, `limit=10`
5. Click **Send**

**Expected Response:**
```json
{
    "data": [
        {
            "id": 1,
            "file_name": "20260211_150405_abc123.pdf",
            "original_name": "report.pdf",
            "file_path": "./uploads/20260211_150405_abc123.pdf",
            "file_size": 102400,
            "file_type": ".pdf",
            "mime_type": "application/pdf",
            "category": "reports",
            "description": "Test document upload",
            "uploaded_by": "anonymous",
            "status": "active",
            "created_at": "2026-02-11T15:04:05Z",
            "updated_at": "2026-02-11T15:04:05Z"
        }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "total_pages": 1
}
```

**Status Code:** `200 OK`

---

## Test 13: Download Document

**Purpose:** Download an uploaded file

**Method:** `GET`  
**URL:** `http://localhost:8080/documents/1/download`

**Steps in Postman:**
1. Create new request
2. Set method to `GET`
3. Enter URL: `http://localhost:8080/documents/1/download`
   - Replace `1` with your document ID
4. Click **Send**
5. Click **Save Response** â†’ **Save to a file**

**Expected Response:**
- File download with original filename
- Appropriate Content-Type header

**Status Code:** `200 OK`

---

## Test 14: Delete Data Record

**Purpose:** Delete a record (soft delete)

**Method:** `DELETE`  
**URL:** `http://localhost:8080/data/1`

**Steps in Postman:**
1. Create new request
2. Set method to `DELETE`
3. Enter URL: `http://localhost:8080/data/1`
4. Click **Send**

**Expected Response:**
```json
{
    "message": "Record deleted successfully"
}
```

**Status Code:** `200 OK`

---

## Test 15: Delete Document

**Purpose:** Delete a document and its file

**Method:** `DELETE`  
**URL:** `http://localhost:8080/documents/1`

**Steps in Postman:**
1. Create new request
2. Set method to `DELETE`
3. Enter URL: `http://localhost:8080/documents/1`
4. Click **Send**

**Expected Response:**
```json
{
    "message": "Document deleted successfully"
}
```

**Status Code:** `200 OK`

---

## Postman Collection Setup (Optional)

### Save as Collection:
1. Click **Collections** in left sidebar
2. Click **+** to create new collection
3. Name it: "DataBridge API"
4. Add all requests to this collection
5. Click **...** â†’ **Export** to save

### Environment Variables:
1. Click **Environments** in left sidebar
2. Create new environment: "Local Development"
3. Add variables:
   - `base_url`: `http://localhost:8080`
4. Use in URLs: `{{base_url}}/data` or `{{base_url}}/documents`

**No more `/api/v1/` prefix needed!**

---

## Human-Readable API Routes

All routes are now simple, intuitive, and easy to remember!

### Complete Route List:

**Data Records:**
- `GET /data` - List all data records
- `POST /data` - Create new record
- `GET /data/:id` - Get specific record (e.g., `/data/1`)
- `PUT /data/:id` - Update record
- `DELETE /data/:id` - Delete record
- `GET /data/category/:category` - Filter by category (e.g., `/data/category/electronics`)

**Documents/Files:**
- `GET /documents` - List all documents
- `POST /documents` - Upload new document
- `GET /documents/:id` - Get document info
- `GET /documents/:id/download` - Download file (e.g., `/documents/1/download`)
- `DELETE /documents/:id` - Delete document
- `GET /documents/category/:category` - Filter by category

**Upload/Import Data:**
- `POST /upload/csv` - Import data from CSV file
- `POST /upload/json` - Import data from JSON file
- `GET /upload/history` - View import history
- `GET /upload/history/:id` - Get specific import log

**Download/Export Data:**
- `GET /download/csv` - Export all data to CSV
- `GET /download/json` - Export all data to JSON
- `GET /download/excel` - Export all data to Excel
- Add `?category=electronics` to any export to filter by category

**System:**
- `GET /health` - Check server status

---

## Testing Checklist

- [ ] Health check works
- [ ] Can create data records
- [ ] Can retrieve all records with pagination
- [ ] Can get single record by ID
- [ ] Can update records
- [ ] Can delete records
- [ ] Can import CSV files
- [ ] Can import JSON files
- [ ] Can view import logs
- [ ] Can export to CSV
- [ ] Can export to JSON
- [ ] Can upload documents (all file types)
- [ ] Can list documents
- [ ] Can download documents
- [ ] Can delete documents

---

## Common HTTP Status Codes

| Code | Meaning | When You'll See It |
|------|---------|-------------------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST (create) |
| 400 | Bad Request | Invalid input/missing fields |
| 404 | Not Found | Record/document doesn't exist |
| 500 | Internal Server Error | Server/database error |

---

## Troubleshooting

### Issue: Connection Refused
**Solution:** Ensure server is running on port 8080

### Issue: 404 Not Found
**Solution:** Check URL path is correct

### Issue: 500 Internal Server Error
**Solution:** Check server logs in terminal, verify database connection

### Issue: File Upload Fails
**Solution:** Ensure you selected "File" type in form-data, not "Text"

### Issue: Import Returns 0 Success
**Solution:** Check CSV/JSON format matches expected structure

### Issue: JSON Import Fails with Parse Error
**Solution:** 
- Validate your JSON at jsonlint.com
- Ensure the file contains an array of objects: `[{...}, {...}]`
- Check that all field names have double quotes
- See `JSON_IMPORT_GUIDE.md` for complete format documentation

### Issue: CSV Import Fails
**Solution:** 
- Ensure CSV has proper headers matching field names
- Check for proper comma separation
- Verify no missing required fields (name)

---

## Next Steps

1. âœ… Test all endpoints in Postman
2. ðŸ“‹ Export Postman collection for team use
3. ðŸ”„ Integrate with Laravel frontend
4. ðŸ“Š Monitor import logs for any issues
5. ðŸš€ Ready for production!

---

## Complete API Reference

For detailed API documentation, see: `API_DOCUMENTATION.md`

---

**Happy Testing! ðŸš€**

