# 🚀 Auto-Discovery Quick Start

## 🔒 Admin Only Feature

**Important:** Only administrators can configure databases and tables. If you're a regular user, contact your administrator to set up tables for you.

## What's New?

**No more manual table configuration!** Administrators can now automatically discover and sync tables from databases.

## Quick Setup (3 Steps)

### 1️⃣ Add a Database Connection

```
Navigate to: Multi-Table → Database Connections → Add Connection

Enter:
- Name: my_database
- Type: postgres (or mysql)
- Host: localhost
- Port: 5432 (postgres) or 3306 (mysql)
- Username: your_user
- Password: your_password
- Database: your_database_name
```

### 2️⃣ Discover Tables

```
Navigate to: Multi-Table → Table Configurations

1. Select database from "Auto-Discover Tables" section
2. Click "Discover Tables"
3. System shows all tables with row counts and column info
```

### 3️⃣ Sync Tables

```
Option A: Click "Sync All Tables" (syncs all checked tables)
Option B: Click "Sync Now" on individual tables
```

**Done! 🎉** Your tables are now configured and ready to use!

## What Happens When You Sync?

The system automatically:
-   Detects all columns
-   Identifies data types  
-   Finds primary keys
-   Discovers nullable fields
-   Maps database types to generic types
-   Creates table configurations

## Benefits

| Before | After |
|--------|-------|
| Manual schema definition |   Automatic detection |
| Error-prone JSON editing |   Zero configuration |
| Minutes per table |   Seconds for all tables |
| Schema updates = manual work |   One-click re-sync |

## Example Workflow

```bash
# Old Way (Manual)
1. Create table config manually
2. Define columns as JSON: [{"name":"id","type":"int",...}]
3. Set primary key manually
4. Repeat for each table... 😫
Total time: 5-10 minutes per table

# New Way (Auto-Discovery)  
1. Select database
2. Click "Discover Tables"
3. Click "Sync All"
Total time: 10 seconds for ALL tables! 🚀
```

## Re-syncing After Schema Changes

If your database schema changes:

```
1. Go to Table Configurations
2. Select database → Discover Tables
3. Click "Re-sync" on changed tables
```

System updates the configuration with new schema automatically.

## Status Indicators

- ⚪ **White background** = Not yet synced
-   **Green background** = Already synced
- 🔄 **"Sync Now"** = Ready to sync
- 🔄 **"Re-sync"** = Update existing config

## API Endpoints (for developers)

```
GET  /discovery/databases          - List available databases
GET  /discovery/tables?database=X  - Discover tables in database
POST /discovery/sync               - Sync tables to configuration
     Body: { "database": "name", "tables": ["table1", "table2"] }
```

## Troubleshooting

**No databases available?**
→ Add database connection first

**No tables found?**
→ Check database has tables and user has permissions

**Sync failed?**
→ Verify database connection is active

## What Gets Excluded?

System tables are automatically filtered:
- users
- table_configs  
- import_logs
- documents
- data_records
- user_table_permissions
- etc.

## Supported Databases

-   PostgreSQL (fully supported)
-   MySQL (fully supported)  
- 🔜 SQL Server (coming soon)
- 🔜 SQLite (coming soon)

## Next Steps

After syncing tables:
1. **Import Data**: Upload CSV/JSON files to tables
2. **Export Data**: Download data in various formats
3. **Configure Joins**: Combine multiple tables
4. **Set Permissions**: Control user access per table

---

**🎊 That's it! You're ready to use auto-discovery!**

For detailed information, see [AUTO_DISCOVERY_GUIDE.md](AUTO_DISCOVERY_GUIDE.md)
