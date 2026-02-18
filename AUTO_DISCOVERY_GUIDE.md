# 🔍 Auto-Discovery Guide - Automatic Database & Table Configuration

## Overview

The Data Import Dashboard features **automatic database and table discovery**, eliminating the need for manual configuration! Simply select a database, and the system will automatically discover and sync all tables with their complete schemas.

## 🔒 Admin-Only Feature

**Important:** Database and table configuration features are restricted to **administrator accounts only**. Regular users can view and use configured tables but cannot modify configurations.

- ✅ **Admins**: Full access to discovery, sync, and configuration
- ❌ **Regular Users**: Read-only access, cannot configure tables

See [ADMIN_ONLY_RESTRICTIONS.md](ADMIN_ONLY_RESTRICTIONS.md) for complete details.

---

## 🎯 Key Features

### ✅ What's Automated
- **Database Detection**: Automatically lists all available database connections
- **Table Discovery**: Scans and identifies all tables in a selected database  
- **Schema Introspection**: Automatically detects:
  - Column names
  - Data types
  - Primary keys
  - Nullable fields
  - Default values
  - Column sizes
- **One-Click Sync**: Sync single or multiple tables instantly
- **Re-sync Support**: Update table configurations if schema changes
- **Status Indicators**: See which tables are already configured

### 🚀 How to Use

#### Step 1: Add a Database Connection (One-Time Setup)

If you haven't already, add your database connection:

1. Navigate to **Multi-Table → Database Connections**
2. Click **Add Database Connection**
3. Enter connection details:
   - **Name**: A friendly name (e.g., "production_db")
   - **Type**: postgres or mysql
   - **Host**: Database server address
   - **Port**: Database port (5432 for PostgreSQL, 3306 for MySQL)
   - **Username**: Database user
   - **Password**: Database password
   - **Database Name**: The specific database to connect to
4. Click **Add Connection**

#### Step 2: Auto-Discover Tables

1. Go to **Multi-Table → Table Configurations**
2. You'll see the **Auto-Discover Tables** section at the top
3. Select a database from the dropdown
4. Click **Discover Tables**
5. The system will scan the database and show all available tables

#### Step 3: Sync Tables

**Option A: Sync All Tables**
- Click **Sync All Tables** button
- All selected tables (checked boxes) will be synced automatically

**Option B: Sync Individual Tables**
- Click **Sync Now** button next to any specific table
- That table will be synced individually

**Option C: Selective Sync**
- Uncheck any tables you don't want to sync
- Click **Sync All Tables** to sync only checked tables

#### Step 4: Use Your Tables

Once synced, tables are ready to use for:
- **Data Import**: Upload CSV/JSON files
- **Data Export**: Download data in various formats
- **Multi-Table Operations**: Join tables, configure mappings
- **RBAC**: Assign user permissions per table

## 🔧 Technical Details

### Backend Implementation

**New Endpoints:**
- `GET /discovery/databases` - List all available database connections
- `GET /discovery/tables?database=<name>` - Discover tables in a database
- `POST /discovery/sync` - Sync tables to configuration

**New Handler:**
- `DatabaseDiscoveryHandler` - Handles all auto-discovery operations

**Supported Databases:**
- PostgreSQL (fully supported)
- MySQL (fully supported)
- Support for additional databases can be easily added

### Database Types Mapping

The system automatically maps database-specific types to generic types:

**PostgreSQL Mappings:**
- `character varying` → `varchar`
- `integer` → `int`
- `timestamp without time zone` → `datetime`
- `jsonb` → `json`
- And more...

**MySQL Mappings:**
- `tinyint`, `smallint`, `mediumint` → `int`
- `tinytext`, `mediumtext`, `longtext` → `text`
- `double` → `float`
- And more...

### Excluded System Tables

The discovery automatically excludes internal system tables:
- `users`
- `table_configs`
- `table_joins`
- `import_mappings`
- `export_configs`
- `import_logs`
- `documents`
- `data_records`
- `user_table_permissions`

## 📝 Configuration Details

### What Gets Configured

When you sync a table, the system automatically creates a `TableConfig` with:

```json
{
  "name": "database_tablename",
  "database_name": "your_database",
  "table_name": "actual_table_name",
  "description": "Auto-synced from your_database database",
  "columns": [...], // Complete column metadata
  "primary_key": "id", // Auto-detected primary key
  "is_active": true
}
```

### Column Metadata Includes

For each column:
```json
{
  "name": "column_name",
  "type": "varchar",
  "size": 255,
  "nullable": false,
  "default_value": "",
  "is_primary": false,
  "is_unique": false
}
```

## 🔄 Re-syncing Tables

If your database schema changes:

1. Go to **Table Configurations**
2. Select your database and click **Discover Tables**
3. Tables marked as "Already Synced" will show a **Re-sync** button
4. Click **Re-sync** to update the configuration with new schema

The system will:
- Update column definitions
- Detect new columns
- Update primary keys
- Preserve existing table configuration name and settings

## 💡 Benefits

### For Users
- ✅ **Zero Manual Configuration**: No need to manually define schemas
- ✅ **Error-Free**: Eliminates typos and configuration mistakes
- ✅ **Fast Setup**: Configure dozens of tables in seconds
- ✅ **Always Current**: Easy to re-sync when schema changes
- ✅ **Visual Feedback**: See row counts and column information

### For Developers
- ✅ **Database Agnostic**: Works with PostgreSQL and MySQL
- ✅ **Extensible**: Easy to add support for more database types
- ✅ **Clean Code**: Well-structured handler and repository pattern
- ✅ **Type Safe**: Proper type mapping between database systems

## 🎨 UI Features

The new interface includes:
- 🌈 **Gradient Header**: Beautiful purple/pink gradient discovery section
- ✅ **Status Badges**: Green badges show already-synced tables
- 📊 **Statistics**: Row counts and column counts displayed
- 🔍 **Smart Filtering**: Checkbox selection for bulk operations
- 🔄 **Action Buttons**: Individual sync or bulk sync options

## ⚠️ Important Notes

1. **Database Connection Required**: You must add a database connection before discovery
2. **Permissions**: The database user must have read access to `information_schema`
3. **System Tables Excluded**: Internal dashboard tables are automatically filtered out
4. **Naming Convention**: Synced tables are named as `database_tablename`
5. **Primary Key Detection**: If no primary key is found, defaults to "id"

## 🔐 Security

- Database passwords are never exposed in API responses
- Only users with proper permissions can discover and sync tables
- Read-only access to information_schema is sufficient

## 🐛 Troubleshooting

**"No databases available"**
- Add a database connection first at Multi-Table → Database Connections

**"No tables found"**
- Verify the database has tables
- Check user has permissions to view information_schema
- Ensure you're connected to the correct database

**"Failed to discover tables"**
- Check database connection is active
- Verify network connectivity
- Check database server is running

## 📚 Related Guides

- [QUICK_START_MASSIVE_SCALE.md](QUICK_START_MASSIVE_SCALE.md) - Getting started
- [MULTI_TABLE_GUIDE.md](MULTI_TABLE_GUIDE.md) - Multi-table operations
- [RBAC_GUIDE.md](RBAC_GUIDE.md) - Role-based access control
- [POSTGRES_SETUP.md](POSTGRES_SETUP.md) - PostgreSQL setup

## 🎉 Example Workflow

```
1. Add database connection "production_db"
   ↓
2. Navigate to Table Configurations
   ↓
3. Select "production_db" from dropdown
   ↓
4. Click "Discover Tables"
   ↓
5. System finds: users, orders, products, inventory (50 tables total)
   ↓
6. Review discovered tables (shows row counts, columns)
   ↓
7. Click "Sync All Tables" or sync individually
   ↓
8. ✓ All 50 tables configured in 10 seconds!
   ↓
9. Tables ready for import/export operations
```

## 🚀 Future Enhancements

Planned features:
- Auto-discovery scheduling (periodic sync)
- Schema change notifications
- Foreign key relationship detection
- Index information
- View support
- Microsoft SQL Server support
- Oracle Database support
- SQLite support

---

**You're now ready to use auto-discovery! No more manual table configuration needed! 🎊**
