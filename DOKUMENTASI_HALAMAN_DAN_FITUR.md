# Dokumentasi Lengkap Halaman dan Fitur - Data Import Dashboard

## Ringkasan Sistem
**Data Import Dashboard** adalah aplikasi web berbasis Laravel (frontend) dan Go (backend) yang dirancang untuk mengelola import/export data berskala besar, manajemen dokumen, dan kontrol akses berbasis peran (Admin dan User).

---

## STRUKTUR HALAMAN DAN FITUR

### 1. HALAMAN AUTENTIKASI (Publik)

#### 1.1. Login (`/login`)
**Tujuan:** Halaman untuk pengguna masuk ke sistem  
**Fitur:**
- Form input email dan password
- Validasi kredensial pengguna
- Hash password menggunakan bcrypt
- Redirect otomatis ke dashboard sesuai role (admin/user)
- Session management
- Pesan error jika kredensial salah

**Backend API:** `POST /auth/login`

---

#### 1.2. Register (`/register`)
**Tujuan:** Halaman pendaftaran akun baru  
**Fitur:**
- Form input: nama, email, password
- Validasi email unik
- Password minimal 8 karakter
- Hash password otomatis
- Default role: "user"
- Auto-login setelah registrasi berhasil

**Backend API:** `POST /auth/register`

---

### 2. HALAMAN ADMIN (Khusus Admin)

#### 2.1. Admin Dashboard (`/admin/dashboard`)
**Tujuan:** Halaman utama admin untuk monitoring sistem  
**Fitur:**
- Statistik pengguna (total user, admin, user biasa)
- Ringkasan data records
- Ringkasan dokumen yang diupload
- Log import terbaru
- Grafik/chart aktivitas sistem
- Quick actions (tambah user, import data, dll)

**Backend API:** 
- `GET /users/stats`
- `GET /data` (pagination)
- `GET /documents`
- `GET /upload/history`

---

#### 2.2. Manajemen User (`/admin/users`)
**Tujuan:** CRUD lengkap untuk user management  

**Fitur:**
- **List Users** (`GET /admin/users`)
  - Tabel daftar semua user
  - Pagination, search, filter by role
  - Informasi: ID, Nama, Email, Role, Created Date
  - Actions: Edit, Delete, Manage Permissions
  - Bulk delete (hapus banyak user sekaligus)

- **Create User** (`GET /admin/users/create`)
  - Form tambah user baru
  - Input: nama, email, password, role
  - Validasi email unik
  
- **Edit User** (`GET /admin/users/{id}/edit`)
  - Form edit data user
  - Update nama, email, role
  - Reset password

- **User Permissions** (`GET /admin/users/{id}/permissions`)
  - Assign table permissions ke user
  - Kontrol akses per tabel: View, Edit, Delete, Export, Import
  - Bulk assign permissions
  - Revoke permissions

**Backend API:**
- `GET /users` - List all users
- `POST /users` - Create user
- `GET /users/:id` - Get user by ID
- `PUT /users/:id` - Update user
- `DELETE /users/:id` - Delete user
- `GET /simple-multi/permissions/users/:userId` - Get user permissions
- `POST /simple-multi/permissions/bulk-assign` - Bulk assign permissions

---

#### 2.3. Data Records Management (`/admin/data-records`)
**Tujuan:** Kelola data records yang diimport  

**Fitur:**
- **List Data Records** (`GET /admin/data-records`)
  - Tabel semua data records
  - Pagination, search, filter by category/status
  - Kolom: ID, Name, Category, Value, Status, Created Date
  - Actions: View, Edit, Delete
  - Export selected records

- **Create Record** (`GET /admin/data-records/create`)
  - Form tambah data record manual
  - Input: name, description, category, value, status, metadata

- **Edit Record** (`GET /admin/data-records/{id}/edit`)
  - Form edit data record
  - Update semua field
  - Soft delete support

**Backend API:**
- `GET /data` - List records
- `POST /data` - Create record
- `GET /data/:id` - Get record
- `PUT /data/:id` - Update record
- `DELETE /data/:id` - Delete record
- `GET /data/category/:category` - Filter by category

---

#### 2.4. Document Management (`/admin/documents`)
**Tujuan:** Kelola file/dokumen yang diupload  

**Fitur:**
- **List Documents** (`GET /admin/documents`)
  - Tabel semua dokumen
  - Informasi: File Name, Type, Size, Category, Upload Date, Uploaded By
  - Filter by category, type, date
  - Search by filename
  - Actions: Download, Delete
  - Preview untuk image/PDF

- **Upload Document** (`GET /admin/documents/create`)
  - Form upload dokumen baru
  - Support multiple files
  - Input: file, category, document type, description
  - Validasi: file size, allowed extensions
  - Auto-generate unique filename

**Backend API:**
- `GET /documents` - List documents
- `POST /documents` - Upload document
- `GET /documents/:id` - Get document info
- `GET /documents/:id/download` - Download file
- `DELETE /documents/:id` - Delete document
- `GET /documents/category/:category` - Filter by category

---

#### 2.5. Document Categories Management (`/admin/configuration/document-categories`)
**Tujuan:** Kelola kategori dokumen (Admin Only)  

**Fitur:**
- List semua kategori dokumen
- Create kategori baru
- Edit nama kategori
- Delete kategori (jika tidak terpakai)
- Validasi unique category name

**Backend API:**
- `GET /document-categories` - List categories
- `POST /document-categories` - Create category (Admin Only)
- `PUT /document-categories/:id` - Update category (Admin Only)
- `DELETE /document-categories/:id` - Delete category (Admin Only)

---

#### 2.6. Import Data (`/admin/import`)
**Tujuan:** Import data dari file CSV/JSON/Excel  

**Fitur:**
- **Import Interface** (`GET /admin/import`)
  - Upload file CSV/JSON
  - Preview data sebelum import
  - Pilih target table
  - Column mapping (pemetaan kolom file ke kolom database)
  - Validation rules
  - Truncate option (hapus data lama sebelum import)
  - Batch processing untuk file besar (+ 100K rows)
  - Worker pool untuk parallel processing
  - Progress bar real-time
  - Error handling per row
  
- **Import History** (`GET /admin/import/history`)
  - Log semua import yang pernah dilakukan
  - Informasi: File Name, Type, Total Records, Success, Failed, Status, Date
  - View detail log
  - Download error report
  - Filter by status, date, user

**Backend API:**
- `POST /upload/csv` - Import CSV (streaming + workers)
- `POST /upload/json` - Import JSON (streaming decoder)
- `GET /upload/history` - List import logs
- `GET /upload/history/:id` - Get specific log

**Fitur Khusus Import:**
- Streaming parser untuk file besar (billions of rows)
- Worker pool dengan configurable workers (default 8)
- Batch size configurable (default 5000)
- Atomic counters untuk statistik real-time
- Memory efficient (reuse record memory)
- Auto-retry untuk failed batches

---

#### 2.7. Export Data (`/admin/export`)
**Tujuan:** Export data ke file CSV/JSON/Excel  

**Fitur:**
- Pilih data source (table atau join)
- Filter data: WHERE conditions
- Sort/Order by
- Select columns to export
- Format: CSV, JSON, Excel (future)
- Streaming export untuk dataset besar
- Chunk download untuk file besar
- Export selected rows only
- Save export configuration untuk reuse

**Backend API:**
- `GET /download/csv` - Export to CSV (streaming)
- `GET /download/json` - Export to JSON (streaming)
- `GET /download/excel` - Export to Excel (not implemented yet)

**Fitur Khusus Export:**
- Streaming output untuk billions of rows
- Configurable batch size (default 10000)
- Periodic flush untuk memory efficiency
- Optional category filter
- Auto-generated filename dengan timestamp

---

#### 2.8. Data Exchange (`/admin/data-exchange`)
**Tujuan:** Interface unified untuk import dan export sederhana  

**Fitur:**
- Tab-based interface: Import | Export
- **Import Tab:**
  - Upload file CSV/JSON
  - Auto-detect format
  - Simple column mapping
  - Direct import tanpa konfigurasi kompleks
  
- **Export Tab:**
  - Select table
  - Quick filters
  - One-click export
  - Download langsung

**Backend API:**
- `POST /unified/import` - Simple import
- `GET /unified/export` - Simple export
- `POST /unified/export` - Export dengan config

---

#### 2.9. Multi-Table Hub (`/admin/multi-table/hub`)
**Tujuan:** Dashboard untuk operasi multi-database dan multi-table  

**Fitur:**
- Navigation hub ke semua fitur multi-table
- Quick stats: total databases, tables, joins, mappings
- Recent activities
- Quick actions

**Sub-halaman Multi-Table:**

##### 2.9.1. Database Management (`/admin/multi-table/databases`)
**Fitur:**
- List semua database connections
- Add new database connection (MySQL/PostgreSQL)
- Test connection
- Edit connection config
- Remove connection
- Switch active database
- Connection pooling status

**Backend API:**
- `GET /databases` - List connections
- `POST /databases` - Add connection
- `GET /databases/test` - Test connection
- `DELETE /databases` - Remove connection

##### 2.9.2. Database Discovery (`/admin/multi-table/databases` - Discovery Tab)
**Fitur:**
- Auto-discover available databases
- List all tables in database
- View table schema
- Auto-sync tables to TableConfig
- Detect primary keys and foreign keys
- Column metadata (type, size, nullable, default)

**Backend API:**
- `GET /discovery/databases` - List databases
- `GET /discovery/tables?database=db1` - Discover tables
- `POST /discovery/sync` - Sync tables

##### 2.9.3. Table Configurations (`/admin/multi-table/tables`)
**Fitur:**
- List semua table configurations
- Create table config (manual atau auto-sync)
- Edit table metadata
- Set primary key
- Define column definitions JSON
- Mark table as active/inactive
- Delete table config

**Backend API:**
- `GET /tables` - List table configs
- `POST /tables` - Create config
- `GET /tables/:id` - Get config
- `PUT /tables/:id` - Update config
- `DELETE /tables/:id` - Delete config

##### 2.9.4. Table Joins (`/admin/multi-table/joins`)
**Fitur:**
- List semua table joins
- Create join configuration
- Select left table & right table
- Define join type: INNER, LEFT, RIGHT, FULL
- Define join condition (ON clause)
- Select columns to include
- Set target table untuk hasil join
- Test join query
- Delete join

**Backend API:**
- `GET /joins` - List joins
- `POST /joins` - Create join
- `GET /joins/:id` - Get join
- `PUT /joins/:id` - Update join
- `DELETE /joins/:id` - Delete join

##### 2.9.5. Import Mappings (`/admin/multi-table/import-mappings`)
**Fitur:**
- List import mappings
- Create mapping template
- Define source format (CSV, JSON, XML)
- Map source fields to table columns
- Transformation rules (format date, convert types, dll)
- Validation rules per column
- Save mapping untuk reuse
- Delete mapping

**Backend API:**
- `GET /multi-import/mappings` - List mappings
- `POST /multi-import/mappings` - Create mapping
- `PUT /multi-import/mappings/:id` - Update mapping
- `DELETE /multi-import/mappings/:id` - Delete mapping

##### 2.9.6. Export Configurations (`/admin/multi-table/export-configs`)
**Fitur:**
- List export configs
- Create export config
- Select source: table atau join
- Define filters (WHERE)
- Define order (ORDER BY)
- Select columns
- Set target format (CSV, JSON, XML, Excel)
- Save config untuk scheduled export
- Delete config

**Backend API:**
- `GET /multi-export/configs` - List configs
- `POST /multi-export/configs` - Create config
- `PUT /multi-export/configs/:id` - Update config
- `DELETE /multi-export/configs/:id` - Delete config

##### 2.9.7. Multi-Table Import (`/admin/multi-table/import`)
**Fitur:**
- Import ke multiple tables sekaligus
- Upload file dengan multiple sheets/sections
- Auto-detect target table per section
- Apply import mapping
- Validate sebelum import
- Rollback on error (transaction)
- Progress per table
- Detailed error log

**Backend API:**
- `POST /multi-import/table` - Import to specific table

##### 2.9.8. Multi-Table Export (`/admin/multi-table/export`)
**Fitur:**
- Export dari multiple tables
- Join-based export
- Apply export config
- Create ZIP untuk multiple files
- Export to single file dengan multiple sheets
- Schedule export (future)

**Backend API:**
- `GET /multi-export/table?config_name=export1` - Export from table
- `GET /multi-export/join-to-table?join_name=join1` - Export joined data

---

#### 2.10. Simple Multi-Table View (`/admin/simple-multi/view-tables`)
**Tujuan:** Interface sederhana untuk view dan edit data dari multiple tables  

**Fitur:**
- Dropdown select table
- View table data dengan pagination
- Filter, search, sort
- CRUD operations:
  - Create new row (inline form)
  - Edit row (inline edit atau modal)
  - Delete row
  - Bulk delete
- Export selected data
- Upload data ke table
- Permission-aware (hanya table yang user punya akses)

**Backend API:**
- `GET /simple-multi/tables` - List accessible tables
- `GET /simple-multi/tables/:table` - Get table data
- `GET /simple-multi/tables/:table/columns` - Get columns
- `POST /simple-multi/tables/:table/rows` - Create row
- `PUT /simple-multi/tables/:table/rows` - Update row
- `DELETE /simple-multi/tables/:table/rows` - Delete row
- `POST /simple-multi/upload-multiple` - Upload to multiple tables
- `POST /simple-multi/export-selected` - Export selected data

---

### 3. HALAMAN USER (Role: User)

**Note:** User memiliki akses terbatas dibanding Admin. Akses ke table dikontrol oleh admin melalui **User Table Permissions**.

#### 3.1. User Dashboard (`/user/dashboard`)
**Tujuan:** Halaman utama user setelah login  

**Fitur:**
- Welcome message
- Summary data yang user bisa akses
- List tabel yang user punya permission
- Recent activities
- Quick actions (import, export, upload dokumen)
- Notifikasi

---

#### 3.2. Data Records (`/user/data-records`)
**Fitur:**
- View data records (read-only atau sesuai permission)
- Create record baru (jika punya permission)
- Edit record (jika punya edit permission)
- Export data (jika punya export permission)

---

#### 3.3. Documents (`/user/documents`)
**Fitur:**
- View dokumen yang user upload
- Upload dokumen baru
- Download dokumen
- Delete dokumen milik sendiri

---

#### 3.4. Import Data (`/user/import`)
**Fitur:**
- Import data ke tabel yang user punya import permission
- View import history milik sendiri

---

#### 3.5. Export Data (`/user/export`)
**Fitur:**
- Export data dari tabel yang user punya export permission

---

#### 3.6. Multi-Table Features (`/user/multi-table/*`)
**Fitur:**
- Akses sama seperti admin TETAPI dibatasi oleh table permissions
- User hanya bisa operasi pada table yang di-assign oleh admin

---

#### 3.7. Simple Multi View (`/user/simple-multi/view-tables`)
**Fitur:**
- Dropdown hanya menampilkan tabel yang user punya akses
- CRUD dibatasi sesuai permission (can_view, can_edit, can_delete, can_export, can_import)

---

## FITUR KEAMANAN & KONTROL AKSES

### Role-Based Access Control (RBAC)
1. **Admin:**
   - Full access ke semua fitur
   - User management
   - Permission management
   - Database configuration
   - Table configuration
   - System settings

2. **User:**
   - Limited access berdasarkan table permissions
   - Hanya operasi yang di-allow oleh admin

### User Table Permissions
**5 Level Akses per Table:**
1. **can_view** - Melihat data
2. **can_edit** - Edit data
3. **can_delete** - Hapus data
4. **can_export** - Export data
5. **can_import** - Import data

### Middleware Protection
- **AuthRequired** - Harus login
- **AdminOnly** - Khusus admin
- **TablePermission** - Check permission per tabel

---

## FITUR TEKNIS UTAMA

### 1. High-Performance Import
- **Streaming Parser:** Tidak load seluruh file ke memory
- **Worker Pool:** Parallel processing (8 workers default)
- **Batch Processing:** 5000 rows per batch
- **Progress Tracking:** Real-time counters
- **Error Handling:** Row-level error tanpa stop import
- **Support Format:** CSV, JSON

### 2. High-Performance Export
- **Streaming Output:** Tidak load seluruh data ke memory
- **Chunked Download:** 10000 rows per chunk
- **Periodic Flush:** Hindari memory overflow
- **Filter & Sort:** WHERE dan ORDER BY
- **Column Selection:** Export hanya kolom tertentu

### 3. Multi-Database Support
- MySQL dan PostgreSQL
- Multiple database connections
- Connection pooling
- Auto-discovery schema

### 4. Auto-Discovery & Sync
- Deteksi database otomatis
- Scan table schema
- Deteksi primary key & foreign key
- Sync ke TableConfig

### 5. Logging & Audit Trail
- Import logs (file, total, success, failed, error message)
- User activity logs
- System logs

---

## ALUR KERJA SISTEM

### Alur Import Data (Admin/User)
1. Login
2. Navigate ke Import page
3. Upload file CSV/JSON
4. Preview data
5. Mapping kolom (opsional)
6. Submit import
7. Backend: parse → validate → batch → workers → database
8. View hasil: total, success, failed
9. Check import history untuk detail log

### Alur Export Data (Admin/User)
1. Login
2. Navigate ke Export page
3. Pilih table atau join
4. Set filters (opsional)
5. Pilih format (CSV/JSON)
6. Click export
7. Backend: query → stream → flush → download

### Alur Permission Assignment (Admin Only)
1. Login sebagai admin
2. Navigate ke Users > Permissions
3. Pilih user
4. Select tables
5. Set permission level (view/edit/delete/export/import)
6. Save
7. User sekarang bisa akses table tersebut

---

## TEKNOLOGI & STACK

### Frontend
- **Framework:** Laravel 10.x (PHP)
- **Template Engine:** Blade
- **CSS:** Tailwind CSS / Bootstrap
- **JavaScript:** Vanilla JS / Alpine.js
- **AJAX:** Fetch API

### Backend
- **Framework:** Gin (Go)
- **ORM:** GORM
- **Database:** PostgreSQL / MySQL
- **Authentication:** Bcrypt password hashing
- **Session:** Laravel session (frontend)
- **File Storage:** Local filesystem

### Architecture
- **Pattern:** MVC + Repository Pattern
- **API:** RESTful
- **Communication:** JSON
- **CORS:** Enabled dengan origin control

---

## API ENDPOINTS SUMMARY

### Authentication
- `POST /auth/login` - Login
- `POST /auth/register` - Register
- `POST /auth/logout` - Logout
- `GET /auth/verify` - Verify session

### Users
- `GET /users` - List users
- `GET /users/stats` - User statistics
- `POST /users` - Create user
- `PUT /users/:id` - Update user
- `DELETE /users/:id` - Delete user

### Data Records
- `GET /data` - List records
- `POST /data` - Create record
- `PUT /data/:id` - Update record
- `DELETE /data/:id` - Delete record
- `GET /data/category/:category` - Filter by category

### Documents
- `GET /documents` - List documents
- `POST /documents` - Upload document
- `GET /documents/:id/download` - Download document
- `DELETE /documents/:id` - Delete document

### Import/Export
- `POST /upload/csv` - Import CSV
- `POST /upload/json` - Import JSON
- `GET /upload/history` - Import logs
- `GET /download/csv` - Export CSV
- `GET /download/json` - Export JSON

### Multi-Database
- `GET /databases` - List DB connections
- `POST /databases` - Add DB connection
- `GET /discovery/databases` - List databases
- `GET /discovery/tables` - Discover tables
- `POST /discovery/sync` - Sync tables

### Table Management
- `GET /tables` - List table configs
- `POST /tables` - Create table config
- `GET /joins` - List joins
- `POST /joins` - Create join

### Permissions
- `GET /simple-multi/permissions/users/:userId` - Get user permissions
- `POST /simple-multi/permissions/bulk-assign` - Assign permissions
- `DELETE /simple-multi/permissions/users/:userId/all` - Revoke all

### Simple Multi-Table
- `GET /simple-multi/tables` - List tables
- `GET /simple-multi/tables/:table` - Get table data
- `POST /simple-multi/tables/:table/rows` - Create row
- `PUT /simple-multi/tables/:table/rows` - Update row
- `DELETE /simple-multi/tables/:table/rows` - Delete row

---

## KESIMPULAN

**Data Import Dashboard** adalah sistem lengkap untuk:
1. ✅ Import/Export data berskala besar (billions of rows)
2. ✅ Manajemen multiple databases
3. ✅ User management dengan RBAC
4. ✅ Table-level permission control
5. ✅ Document management
6. ✅ Auto-discovery database schema
7. ✅ High-performance dengan streaming & worker pools
8. ✅ Audit trail lengkap

**Total Halaman:** ~25 halaman (admin + user)  
**Total API Endpoints:** ~80+ endpoints  
**Role Access:** 2 level (Admin, User) dengan granular table permissions

---

## UNTUK LAPORAN / PRESENTASI

**Screenshots yang Perlu:**
1. Login page
2. Admin dashboard
3. User management page
4. Import interface (dengan progress bar)
5. Export interface
6. Permission assignment page
7. Multi-table hub
8. Database discovery page
9. Simple multi-table view
10. Import history log

**Diagram yang Perlu:**
- ERD (sudah ada di REPORT_CHARTS_MERMAID.md)
- Flowchart Login
- Flowchart Import/Export
- DFD Level 0 & 1
- Use Case Diagram
- Sequence Diagram

Semua sudah tersedia di file: `REPORT_CHARTS_MERMAID.md`
