# Test Data for Import Dashboard

This directory contains comprehensive test data files for testing the import and upload functionality of the Data Import Dashboard.

## Setup Instructions

### 1. Create Database Tables

Run the SQL script to create all test tables in your PostgreSQL database:

```bash
# Using psql command line
psql -U postgres -d data_import_db -f test_tables_setup.sql

# Or connect to your database and run the script
psql -U postgres -d data_import_db
\i test_tables_setup.sql
```

### 2. Available Test Tables

The following tables will be created:

1. **data_records** - Default application table for generic data
2. **customers** - Customer information with addresses
3. **products** - Product catalog with inventory details
4. **orders** - Sales orders linked to customers
5. **employees** - Employee directory with departments
6. **sales** - Sales transactions and metrics
7. **inventory** - Warehouse inventory tracking

## Test Files Overview

### CSV Files (for CSV Import Testing)

| File | Table | Records | Description |
|------|-------|---------|-------------|
| `sample_data.csv` | data_records | 8 | Original sample data for generic records |
| `sample_customers.csv` | customers | 10 | Customer data with addresses and purchase history |
| `sample_products.csv` | products | 15 | Product catalog with pricing and inventory |
| `sample_employees.csv` | employees | 15 | Employee directory with departments |
| `sample_sales.csv` | sales | 15 | Sales transactions with product/customer references |
| `sample_inventory.csv` | inventory | 15 | Warehouse inventory with locations |

### JSON Files (for JSON Import Testing)

| File | Table | Records | Description |
|------|-------|---------|-------------|
| `sample_data.json` | data_records | 6 | Generic records in JSON format |
| `sample_customers.json` | customers | 5 | Customer data in JSON format |
| `sample_products.json` | products | 5 | Product catalog in JSON format |

## Testing Import Functions

### Method 1: Using the Web Interface

1. **Start the application:**
   ```bash
   cd backend
   go run main.go
   ```

2. **Access the frontend** at http://localhost:8000

3. **Login as admin** (create admin if needed):
   ```bash
   cd backend
   ./create-admin.bat
   ```

4. **Import CSV data:**
   - Go to Import page
   - Select table (e.g., "customers")
   - Choose file (e.g., `sample_customers.csv`)
   - Click Import

5. **Import JSON data:**
   - Same process but select .json files

### Method 2: Using API Directly (with curl)

#### Import CSV to data_records:
```bash
curl -X POST http://localhost:8080/api/import/csv \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@sample_data.csv"
```

#### Import JSON to data_records:
```bash
curl -X POST http://localhost:8080/api/import/json \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@sample_data.json"
```

#### Import CSV to specific table (customers):
```bash
curl -X POST "http://localhost:8080/api/simple-multi-table/customers/import/csv" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@sample_customers.csv"
```

### Method 3: Using Postman

Import the API collection and test:
- See `backend/POSTMAN_TESTING_GUIDE.md` for details
- Import endpoints: `/api/import/csv`, `/api/import/json`
- Multi-table endpoints: `/api/simple-multi-table/{table}/import/csv`

## Verifying Imports

### Check imported data via API:
```bash
# Get data_records
curl -X GET http://localhost:8080/api/data-records \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get customers
curl -X GET http://localhost:8080/api/simple-multi-table/customers/records \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get products
curl -X GET http://localhost:8080/api/simple-multi-table/products/records \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Check via database:
```sql
-- Check record counts
SELECT 'data_records' as table_name, COUNT(*) FROM data_records
UNION ALL
SELECT 'customers', COUNT(*) FROM customers
UNION ALL
SELECT 'products', COUNT(*) FROM products
UNION ALL
SELECT 'employees', COUNT(*) FROM employees
UNION ALL
SELECT 'sales', COUNT(*) FROM sales
UNION ALL
SELECT 'inventory', COUNT(*) FROM inventory;

-- View sample data
SELECT * FROM customers LIMIT 5;
SELECT * FROM products WHERE category = 'Electronics';
SELECT * FROM employees WHERE department = 'Engineering';
```

## Testing Upload Functions (Documents)

The application also supports document uploads. Test document upload:

1. **Via Web Interface:**
   - Navigate to Documents page
   - Click Upload
   - Select any file (PDF, DOC, images, etc.)
   - Add description
   - Submit

2. **Via API:**
```bash
curl -X POST http://localhost:8080/api/documents/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test_document.pdf" \
  -F "description=Test document upload"
```

## Stress Testing

For testing with larger datasets:

1. **Generate large CSV files** using the stress test tools:
   ```bash
   cd stress_tests
   python generate_large_dataset.py --records 10000 --output large_products.csv
   ```

2. **See guides:**
   - `STRESS_TEST_GUIDE.md` - Detailed stress testing instructions
   - `STRESS_TEST_QUICKSTART.md` - Quick start for stress tests

## Table Auto-Discovery

The application can auto-discover existing tables:

1. **Discover tables via API:**
```bash
curl -X POST http://localhost:8080/api/database-discovery/discover \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **After discovery, import to any discovered table** using the simple-multi-table endpoints

## Troubleshooting

### Import fails:
- Check that tables exist (`test_tables_setup.sql` was run)
- Verify column names match between CSV/JSON and table schema
- Check for data type mismatches
- View logs in terminal where backend is running

### Permission errors:
- Ensure you're logged in as admin
- Check RBAC settings if table permissions are enabled
- See `TABLE_PERMISSIONS_GUIDE.md` for permission setup

### File upload errors:
- Check `UPLOAD_PATH` in backend `.env`
- Verify the `uploads` directory exists and is writable
- Check file size limits in configuration

## Additional Resources

- **Backend API Docs:** `backend/API_DOCUMENTATION.md`
- **Multi-Table Guide:** `MULTI_TABLE_GUIDE.md`
- **Simple Multi-Table Guide:** `SIMPLE_MULTI_TABLE_GUIDE.md`
- **JSON Import Guide:** `backend/JSON_IMPORT_GUIDE.md`
- **User Guide:** `USER_GUIDE.md`

## Quick Test Workflow

```bash
# 1. Create tables
psql -U postgres -d data_import_db -f test_tables_setup.sql

# 2. Start backend
cd backend
go run main.go

# 3. Start frontend (in new terminal)
cd frontend
php artisan serve

# 4. Open browser
# Go to http://localhost:8000

# 5. Login and test imports with provided CSV/JSON files
```

Happy Testing! 🚀
