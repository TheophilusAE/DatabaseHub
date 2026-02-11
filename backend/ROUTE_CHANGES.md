# API Route Changes - Quick Reference

## ✅ Changes Made

### 1. Fixed Database Error
**Problem:** `ERROR: invalid input syntax for type json (SQLSTATE 22P02)`

**Solution:** Changed `metadata` field from required `string` to optional `*string` (pointer) to properly handle null/empty values in PostgreSQL.

### 2. Simplified Route Names
Made API routes shorter and more intuitive!

---

## 📋 New Simplified Routes

### **Data Records** (was `/data-records`, now `/records`)

| Method | Old Route | New Route |
|--------|-----------|-----------|
| GET | `/api/v1/data-records` | `/api/v1/records` |
| POST | `/api/v1/data-records` | `/api/v1/records` |
| GET | `/api/v1/data-records/:id` | `/api/v1/records/:id` |
| PUT | `/api/v1/data-records/:id` | `/api/v1/records/:id` |
| DELETE | `/api/v1/data-records/:id` | `/api/v1/records/:id` |
| GET | `/api/v1/data-records/category/:category` | `/api/v1/records/category/:category` |

### **Files/Documents** (was `/documents`, now `/files`)

| Method | Old Route | New Route |
|--------|-----------|-----------|
| GET | `/api/v1/documents` | `/api/v1/files` |
| POST | `/api/v1/documents/upload` | `/api/v1/files` |
| GET | `/api/v1/documents/:id` | `/api/v1/files/:id` |
| GET | `/api/v1/documents/download/:id` | `/api/v1/files/download/:id` |
| DELETE | `/api/v1/documents/:id` | `/api/v1/files/:id` |
| GET | `/api/v1/documents/category/:category` | `/api/v1/files/category/:category` |

### **Import/Export Routes** (unchanged)

Import and export routes remain the same:
- `POST /api/v1/import/csv`
- `POST /api/v1/import/json`
- `GET /api/v1/import/logs`
- `GET /api/v1/export/csv`
- `GET /api/v1/export/json`

---

## 🎯 Quick Test Commands

### Test with cURL:

```bash
# BEFORE: Create record (old)
curl -X POST http://localhost:8080/api/v1/data-records -H "Content-Type: application/json" -d '{...}'

# NOW: Create record (new - shorter!)
curl -X POST http://localhost:8080/api/v1/records -H "Content-Type: application/json" -d '{
  "name": "MacBook Pro",
  "description": "16-inch M3 Max laptop",
  "category": "electronics",
  "value": 2499.99,
  "status": "active"
}'

# BEFORE: Upload file (old)
curl -X POST http://localhost:8080/api/v1/documents/upload -F "file=@photo.jpg"

# NOW: Upload file (new - shorter!)
curl -X POST http://localhost:8080/api/v1/files -F "file=@photo.jpg"
```

### Test in Postman:

**Create Record:**
```
POST http://localhost:8080/api/v1/records
Body (raw JSON):
{
  "name": "MacBook Pro",
  "description": "16-inch M3 Max laptop",
  "category": "electronics",
  "value": 2499.99,
  "status": "active"
}
```

**Upload File:**
```
POST http://localhost:8080/api/v1/files
Body (form-data):
- file: [Select File]
- category: documents
- description: Test upload
```

---

## 🔧 What Changed Internally

### Model Changes ([models/data_record.go](d:\DataImportDashboard\backend\models\data_record.go))

**Before:**
```go
Metadata string `gorm:"type:json" json:"metadata"`
```

**After:**
```go
Metadata *string `gorm:"type:text" json:"metadata,omitempty"`
```

**Benefits:**
- ✅ No more JSON syntax errors
- ✅ Metadata is optional (can be null)
- ✅ Compatible with PostgreSQL and MySQL
- ✅ Automatically omitted from JSON when empty

### Route Changes ([routes/routes.go](d:\DataImportDashboard\backend\routes\routes.go))

**Before:**
```go
dataRecords := v1.Group("/data-records")
documents := v1.Group("/documents")
documents.POST("/upload", ...)
```

**After:**
```go
records := v1.Group("/records")  // Simpler!
files := v1.Group("/files")      // Simpler!
files.POST("", ...)              // Upload at root of /files
```

---

## 📝 Migration Guide

If you have existing code using the old routes:

### For Laravel/Frontend Integration:

**Replace:**
- `data-records` → `records`
- `documents` → `files`
- `documents/upload` → `files` (POST method)

**Example (Laravel HTTP Client):**

```php
// OLD
$response = Http::get('http://localhost:8080/api/v1/data-records');
$response = Http::post('http://localhost:8080/api/v1/documents/upload', [...]);

// NEW
$response = Http::get('http://localhost:8080/api/v1/records');
$response = Http::post('http://localhost:8080/api/v1/files', [...]);
```

### For Postman Collections:

1. Open your collection
2. Find and replace:
   - `data-records` → `records`
   - `documents/upload` → `files`
   - `documents` → `files`
3. Save collection

---

## ✨ Benefits of New Routes

1. **Shorter & Cleaner**
   - `/records` vs `/data-records` (save 5 characters!)
   - `/files` vs `/documents/upload` (save 9 characters!)

2. **More Intuitive**
   - Upload to `/files` instead of `/documents/upload`
   - All file operations under `/files/*`

3. **RESTful Standard**
   - POST to `/files` uploads a file
   - GET `/files` lists files
   - Simple and logical!

4. **Less Typing**
   - Easier to remember
   - Faster to type
   - Cleaner code

---

## 🧪 Testing Checklist

After updating your code:

- [ ] Test creating records: `POST /api/v1/records`
- [ ] Test listing records: `GET /api/v1/records`
- [ ] Test uploading files: `POST /api/v1/files`
- [ ] Test downloading files: `GET /api/v1/files/download/:id`
- [ ] Verify no 500 errors on POST requests
- [ ] Verify metadata field works (optional now)
- [ ] Update your frontend/client code
- [ ] Update Postman collection
- [ ] Update API documentation

---

## 🚀 Ready to Use!

The backend has been rebuilt and all changes are active.

**To start the server:**
```bash
go run main.go
```

**To test immediately:**
```bash
# Health check
curl http://localhost:8080/health

# Create a record (NEW ROUTE!)
curl -X POST http://localhost:8080/api/v1/records \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","category":"test","value":99.99,"status":"active"}'
```

---

**All fixed! The error is resolved and routes are simplified!** 🎉
