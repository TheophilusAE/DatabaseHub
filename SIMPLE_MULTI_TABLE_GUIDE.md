# Simple Multi-Table User Guide

## Overview

The Simple Multi-Table system provides easy-to-use features for working with multiple database tables:

1. **View All Tables** - Browse and view data from all tables in your database
2. **Multi-Table Upload** - Upload files to multiple tables at once
3. **Selective Export** - Choose specific tables, columns, and filters to export data

## Features

### 1. View All Tables

**Location:** Multi-Table → View All Tables

Browse all tables in your database and view their data:

- See all available tables with row counts
- Click any table to view its data
- Paginated data viewing (25, 50, 100, or 250 rows per page)
- Navigate through pages of data
- View all columns and their values

**How to Use:**
1. Go to **Multi-Table → View All Tables**
2. Click on any table card to open the data viewer
3. Use pagination controls to browse through rows
4. Change "Rows per page" to see more or fewer rows at once

### 2. Multi-Table Upload

**Location:** Multi-Table → Multi-Table Upload

Upload data files to multiple tables simultaneously:

- Add multiple files in one upload session
- Each file gets assigned to a specific table
- Supports CSV and JSON formats
- Automatic format detection
- View upload results for each table
- See success/failure counts per table

**How to Use:**
1. Go to **Multi-Table → Multi-Table Upload**
2. Click on "Choose a file..." to select a file
3. Select the target table from the dropdown
4. Click "Add Another File" to add more files
5. Repeat for each file you want to upload
6. Click "Start Upload" to upload all files

**File Format Requirements:**

**CSV Files:**
- First row must contain column headers
- Column names should match your table columns
- Use comma as delimiter

**JSON Files:**
- Array of objects: `[{...}, {...}]`
- Keys should match your table column names

**Example CSV:**
```csv
id,name,email,age
1,John Doe,john@example.com,30
2,Jane Smith,jane@example.com,25
```

**Example JSON:**
```json
[
  {"id": 1, "name": "John Doe", "email": "john@example.com", "age": 30},
  {"id": 2, "name": "Jane Smith", "email": "jane@example.com", "age": 25}
]
```

### 3. Selective Export

**Location:** Multi-Table → Selective Export

Export data with precise control over what to include:

- Select multiple tables to export
- Choose specific columns from each table
- Add SQL filters to each table
- Export to CSV or JSON format
- All data combined in one export file

**How to Use:**
1. Go to **Multi-Table → Selective Export**
2. Choose export format (CSV or JSON)
3. Select a table from the dropdown
4. (Optional) Select specific columns (leave unselected for all columns)
5. (Optional) Add filter conditions
6. Click "Add Another Table" to include more tables
7. Click "Export Data" to download the file

**Filter Examples:**
- `status = 'active'` - Only active records
- `age > 18` - Only records where age is greater than 18
- `created_at > '2024-01-01'` - Records created after a date
- `status = 'active' AND age > 18` - Multiple conditions

**Column Selection:**
- Check "Select All" to include all columns
- Or check individual columns to include only specific ones
- Unchecking all columns will export all columns by default

## Access Control

Both admin and user roles can access all three features:

- **Admins** can upload, view, and export data from any table
- **Users** can upload, view, and export data from any table
- All actions are logged for auditing

## API Endpoints

The backend API endpoints are available at:

### List Tables
```
GET http://localhost:8080/simple-multi/tables
```

Returns all tables in the database with row counts.

### View Table Data
```
GET http://localhost:8080/simple-multi/tables/{table_name}?page=1&page_size=50
```

Returns paginated data from a specific table.

### Get Table Columns
```
GET http://localhost:8080/simple-multi/tables/{table_name}/columns
```

Returns column information for a table.

### Multi-Table Upload
```
POST http://localhost:8080/simple-multi/upload-multiple
Content-Type: multipart/form-data

files: [file1, file2, ...]
table_names: [table1, table2, ...]
```

Uploads files to multiple tables.

### Selective Export
```
POST http://localhost:8080/simple-multi/export-selected
Content-Type: application/json

{
  "tables": [
    {
      "table_name": "users",
      "columns": ["id", "name", "email"],
      "filters": "status = 'active'"
    }
  ],
  "format": "csv"
}
```

Exports selected data from multiple tables.

## Tips and Best Practices

### Viewing Tables
- Tables are loaded from the `public` schema (PostgreSQL)
- Large tables may take longer to display
- Use pagination to navigate through large datasets
- Table data is read-only in the viewer

### Uploading Data
- Test with small files first
- Make sure column names in your file match the table columns
- Check the upload results for any errors
- Failed rows will be reported in the results

### Exporting Data
- Use filters to reduce the amount of data exported
- Select only the columns you need to make exports smaller
- JSON format preserves data types better than CSV
- CSV format is easier to open in spreadsheet applications

## Troubleshooting

### Cannot see any tables
- Check database connection
- Make sure you have proper permissions
- Tables must be in the `public` schema

### Upload fails
- Verify file format (CSV or JSON)
- Check that column names match table columns
- Make sure data types are compatible
- Check file size (max 500MB)

### Export returns no data
- Check your filter conditions
- Make sure the table has data
- Verify column names are correct

### Page loads slowly
- Large tables may take time to display
- Reduce page size to load fewer rows
- Use filters in exports to limit data

## Example Workflows

### Workflow 1: View Data in Multiple Tables
1. Go to View All Tables
2. Click on first table to view its data
3. Browse through the data using pagination
4. Close viewer and select another table

### Workflow 2: Upload Data to Several Tables
1. Prepare CSV/JSON files for each table
2. Go to Multi-Table Upload
3. Add each file and select its target table
4. Upload all files at once
5. Review results to confirm success

### Workflow 3: Export Specific Data
1. Go to Selective Export
2. Add first table and select columns
3. Add filter conditions if needed
4. Add more tables as needed
5. Choose export format and download

### Workflow 4: Data Migration
1. View source table to understand structure
2. Export data from source table with filters
3. Modify exported data as needed
4. Upload modified data to destination table

## Security Notes

- All operations require authentication
- Upload actions are logged with user information
- Database credentials are stored securely
- All queries use parameterized statements (SQL injection protection)

## Limitations

- Maximum file upload size: 500MB
- Viewing is limited to `public` schema tables
- Page size limited to 1000 rows maximum
- Export may time out for very large datasets

## Support

For issues or questions:
1. Check this guide
2. Review error messages in the UI
3. Check browser console for errors
4. Review backend logs in `backend/logs/`
