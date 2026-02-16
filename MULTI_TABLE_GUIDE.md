# Multi-Table Import/Export System Guide

## Overview

The Multi-Table system allows you to:
- Connect to multiple databases (PostgreSQL and MySQL)
- Define dynamic table configurations
- Create table joins to combine data from multiple tables
- Import data to any configured table
- Export data from single tables or joined tables
- Save reusable import mappings and export configurations

## System Architecture

### Backend Components
- **Multi-Database Manager** (`backend/config/multi_database.go`): Manages connections to multiple databases
- **Table Config Models** (`backend/models/table_config.go`): Data models for tables, joins, mappings, and configs
- **Repositories** (`backend/repository/table_config_repository.go`): Database operations
- **Import Handler** (`backend/handlers/multi_table_import_handler.go`): Multi-table import with worker pools
- **Export Handler** (`backend/handlers/multi_table_export_handler.go`): Export with JOIN support
- **Config Handler** (`backend/handlers/table_config_handler.go`): CRUD APIs for configurations

### Frontend Pages
1. **Databases** - Manage database connections
2. **Table Configs** - Define table structures
3. **Table Joins** - Configure joins between tables
4. **Import Mappings** - Define column mappings for imports
5. **Export Configs** - Save export configurations
6. **Multi-Table Import** - Execute imports
7. **Multi-Table Export** - Execute exports

## Getting Started

### Step 1: Add Database Connections

1. Navigate to **Multi-Table → Databases**
2. Click **Add Database Connection**
3. Fill in the form:
   - **Connection Name**: A friendly name (e.g., "Production DB", "Analytics DB")
   - **Database Type**: PostgreSQL or MySQL
   - **Host**: Database server address
   - **Port**: Database port (5432 for PostgreSQL, 3306 for MySQL)
   - **Database Name**: The name of the database
   - **Username**: Database user
   - **Password**: Database password
4. Click **Test Connection** to verify
5. Click **Add Connection** to save

### Step 2: Define Table Configurations

1. Navigate to **Multi-Table → Table Configs**
2. Click **Add Table Configuration**
3. Fill in the form:
   - **Configuration Name**: A friendly name (e.g., "Users Table", "Orders Table")
   - **Database Connection**: Select from your saved connections
   - **Table Name**: The actual table name in the database
   - **Columns (JSON)**: Define the table structure in JSON format
   
   Example column definition:
   ```json
   [
     {"name": "id", "type": "integer"},
     {"name": "name", "type": "string"},
     {"name": "email", "type": "string"},
     {"name": "created_at", "type": "timestamp"}
   ]
   ```
4. Click **Add Configuration**

### Step 3: Create Table Joins (Optional)

If you need to combine data from multiple tables:

1. Navigate to **Multi-Table → Table Joins**
2. Click **Add Table Join**
3. Fill in the form:
   - **Join Name**: A friendly name (e.g., "Users with Orders")
   - **Left Table**: Select the first table
   - **Right Table**: Select the second table
   - **Join Type**: Choose from INNER, LEFT, RIGHT, or FULL OUTER
   - **Join Condition**: Specify the join condition (e.g., `users.id = orders.user_id`)
   - **Target Table** (Optional): Select a table where combined data can be exported
4. Click **Save Join**

### Step 4: Create Import Mappings

Define how CSV/JSON columns map to your table columns:

1. Navigate to **Multi-Table → Import Mappings**
2. Click **Add Import Mapping**
3. Fill in the form:
   - **Mapping Name**: A friendly name (e.g., "User CSV Import")
   - **Target Table**: Select the destination table
   - **Column Mappings**: Add mappings by clicking **Add Mapping**
     - **Source Column**: The column name in your CSV/JSON file
     - **Destination Column**: The column in your target table
4. Click **Save Mapping**

### Step 5: Create Export Configurations (Optional)

Save reusable export configurations with filters and sorting:

1. Navigate to **Multi-Table → Export Configs**
2. Click **Add Export Config**
3. Fill in the form:
   - **Config Name**: A friendly name
   - **Export Format**: CSV or JSON
   - **Data Source**: Choose "Single Table" or "Table Join"
   - **Filter Conditions**: Optional SQL WHERE conditions (e.g., `status = 'active'`)
   - **Sort By**: Optional column to sort by
   - **Sort Order**: ASC or DESC
   - **Selected Columns**: Optional comma-separated list of columns
   - **Limit Records**: Optional maximum number of records
   - **Include Headers**: Check for CSV headers
4. Click **Save Configuration**

## Importing Data

### Standard Import

1. Navigate to **Multi-Table → Multi-Table Import**
2. Select an **Import Mapping** from the dropdown
3. Drag and drop your file or click to browse
4. Click **Start Import**
5. Monitor the progress bar
6. View import results and history

### File Format Requirements

**CSV Files:**
- First row should contain column headers
- Headers must match the source columns in your mapping
- Use comma (`,`) as delimiter
- Quotes (`"`) for text fields containing commas

**JSON Files:**
- Array of objects: `[{"name": "John", "email": "john@example.com"}, ...]`
- Single object will be treated as one record
- Keys must match the source columns in your mapping

## Exporting Data

### Export from Single Table

1. Navigate to **Multi-Table → Multi-Table Export**
2. Go to the **Single Table Export** tab
3. Select a **Table Configuration**
4. Choose **Export Format** (CSV or JSON)
5. Optionally select a saved **Export Configuration**
6. Click **Export Data** to download

### Export from Joined Tables

1. Navigate to **Multi-Table → Multi-Table Export**
2. Go to the **Combined Tables Export** tab
3. Select a **Table Join**
4. Choose **Export Format**
5. Choose what to do with the result:
   - **Download File**: Get the export as a file
   - **Export to Table**: Save the combined data to a target table
6. Click **Start Export**

## API Endpoints

All API endpoints are prefixed with `/api/multi-table/`:

### Database Connections
- `POST /database-configs` - Add database connection
- `GET /database-configs` - List all connections
- `GET /database-configs/:id` - Get single connection
- `PUT /database-configs/:id` - Update connection
- `DELETE /database-configs/:id` - Delete connection
- `POST /database-configs/test` - Test connection

### Table Configurations
- `POST /table-configs` - Add table config
- `GET /table-configs` - List all table configs
- `GET /table-configs/:id` - Get single config
- `PUT /table-configs/:id` - Update config


## Performance Features

### Worker Pools
The import system uses worker pools for parallel processing:
- **Default Workers**: 32 concurrent workers
- **Batch Size**: 50,000 records per batch
- Automatically adjusts based on system resources

### Large Dataset Handling
- Streaming JSON parser for large files
- Chunked CSV reading
- Memory-efficient batch processing
- Progress tracking with real-time updates

## Common Use Cases

### Use Case 1: Multi-Database Reporting
1. Connect to production and analytics databases
2. Create table configs for relevant tables
3. Create joins between related tables
4. Export combined data for reports

### Use Case 2: Data Migration
1. Connect to source and destination databases
2. Export data from source table
3. Create import mapping for destination table
4. Import data to destination

### Use Case 3: Data Transformation
1. Create join between two tables
2. Set target table for transformation results
3. Export joined data directly to target table
4. Combined data is now available in a single table

### Use Case 4: Scheduled Exports
1. Create export configurations with filters
2. Save configurations for reuse
3. Use API to execute exports programmatically
4. Integrate with schedulers (cron, Task Scheduler)

## Troubleshooting

### Connection Issues
- Verify database credentials
- Check firewall rules
- Ensure database server is accessible
- Test connection before saving

### Import Failures
- Check column mappings match source data
- Verify data types are compatible
- Look for invalid data in source file
- Check import history for error details

### Export Issues
- Verify join conditions are correct
- Check table permissions
- Ensure target table has correct schema
- Validate filter conditions syntax

### Performance Issues
- Reduce batch size for systems with limited RAM
- Lower worker count if CPU is bottleneck
- Use filters to limit dataset size
- Consider indexing frequently joined columns

## Security Considerations

### Database Credentials
- Credentials are stored in the database
- Consider encrypting sensitive connection strings
- Use read-only database users for exports
- Restrict write access for imports

### Access Control
- Multi-table features respect Laravel's authentication
- Admin and user roles have separate routes
- Implement additional authorization as needed
- Audit import/export operations

### SQL Injection Protection
- All database queries use parameterized statements
- Filter conditions are validated
- Join conditions are sanitized
- Never construct raw SQL from user input

## Best Practices

### Table Configurations
1. Use descriptive names
2. Document column types accurately
3. Keep column definitions up to date
4. Test with small datasets first

### Import Mappings
1. Create separate mappings for different data sources
2. Name mappings clearly (include source format)
3. Validate mappings with sample data
4. Document any data transformations needed

### Export Configurations
1. Use filters to reduce dataset size
2. Save commonly used configurations
3. Test exports with limits first
4. Include only necessary columns

### Joins
1. Use appropriate join types (INNER vs LEFT)
2. Index join columns for performance
3. Test joins with small datasets first
4. Document the purpose of each join

## Advanced Features

### Dynamic Table Access
The system dynamically generates queries based on table configurations, allowing you to:
- Import to any table without code changes
- Export from any table structure
- Join tables across different databases
- Transform data on-the-fly

### Batch Processing
Large imports are processed in batches:
- Configurable batch size
- Progress tracking per batch
- Error handling per batch
- Resume capability (future enhancement)

### Cross-Database Operations
- Export from one database
- Import to another database
- Join tables from different databases
- Maintain referential integrity

## Future Enhancements

Potential features for future versions:
- Data validation rules
- Custom transformation functions
- Scheduled imports/exports
- Email notifications
- Data quality checks
- Duplicate detection
- Incremental imports/exports
- Version control for configurations

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review API documentation
3. Check backend logs at `backend/logs/`
4. Check frontend logs in browser console
5. Review database-specific documentation

## Summary

The Multi-Table system provides a flexible, scalable solution for managing data across multiple databases and tables. By following this guide, you can:
- Set up database connections
- Configure tables dynamically
- Create complex joins
- Import data with custom mappings
- Export data with filters and transformations
- Automate data operations

Start with simple single-table imports/exports and gradually explore more advanced features like joins and cross-database operations.


### Example 3: Export Filtered Data from Multiple Databases

**Step 1: Create Export Configuration**
```bash
curl -X POST http://localhost:8080/multi-export/configs \
  -H "Content-Type: application/json" \
  -d '{
    "name": "filtered_export",
    "source_type": "join",
    "source_id": 1,
    "target_format": "json",
    "filters": "{\"total\":{\"$gte\":500},\"city\":\"New York\"}",
    "order_by": "[\"order_date DESC\"]"
  }'
```

**Step 2: Execute Export**
```bash
curl http://localhost:8080/multi-export/table?config_name=filtered_export > output.json
```

---

## JSON Format Specifications

### Column Definition Format
```json
[
  {
    "name": "id",
    "type": "int",
    "size": 0,
    "nullable": false,
    "is_primary": true,
    "is_unique": true
  },
  {
    "name": "name",
    "type": "varchar",
    "size": 255,
    "nullable": false,
    "default": ""
  },
  {
    "name": "price",
    "type": "decimal",
    "nullable": true
  }
]
```

### Column Mapping Format
```json
{
  "target_column_1": "source_column_1",
  "target_column_2": "source_column_2",
  "id": "ProductID",
  "name": "ProductName"
}
```

### Select Columns Format
```json
[
  "left_table.id as order_id",
  "left_table.order_date",
  "right_table.name as customer_name",
  "right_table.email"
]
```

### Filters Format
```json
{
  "column1": "exact_value",
  "column2": {
    "$gt": 100,
    "$lt": 1000
  },
  "status": "active"
}
```

### Order By Format
```json
[
  "column1 DESC",
  "column2 ASC",
  "created_at DESC"
]
```

---

## Benefits

1. **Flexibility** - Work with any database and table structure
2. **Scalability** - Handle large datasets with batch processing
3. **Reusability** - Save configurations and mappings for repeated use
4. **Data Integration** - Combine data from multiple sources easily
5. **Cross-Database Operations** - Import from one DB and export to another

---

## Performance Tips

1. **Batch Size** - Adjust `import_batch_size` in config for optimal performance
2. **Indexes** - Create indexes on JOIN columns for faster queries
3. **Connection Pooling** - Configure `db_max_open_conns` based on your needs
4. **Filters** - Use filters in export configs to reduce data volume
5. **Parallel Processing** - The system uses worker pools for concurrent operations

---

## Troubleshooting

### Connection Issues
- Verify database credentials
- Check network connectivity
- Test connection using `/databases/test` endpoint

### Import Failures
- Verify column mapping matches your CSV/JSON structure
- Check data types match table schema
- Review import logs for specific errors

### Join Issues
- Ensure join condition uses correct table aliases (`left_table`, `right_table`)
- Verify both tables exist in their respective databases
- Check that join columns have compatible data types

---

## Security Considerations

1. **Database Credentials** - Store securely, never in version control
2. **Access Control** - Implement authentication for sensitive operations
3. **SQL Injection** - The system uses parameterized queries
4. **File Uploads** - Validate file sizes and types
5. **Connection Limits** - Set appropriate limits to prevent resource exhaustion

---

## Next Steps

1. Install dependencies: `cd backend && go mod tidy`
2. Run migrations: The system auto-migrates on startup
3. Start server: `go run main.go`
4. Configure your first database connection
5. Create table configurations
6. Set up import/export mappings
7. Start importing and exporting data!

For more examples and API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md).
