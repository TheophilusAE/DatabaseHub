# PostgreSQL Setup Guide

## Quick Start for PostgreSQL Users

This application is fully compatible with PostgreSQL and includes optimizations for handling large datasets.

### Prerequisites

1. PostgreSQL installed and running (version 12 or higher recommended)
2. Go 1.21 or higher
3. Database created for the application

### Database Setup

#### 1. Create Database
```sql
CREATE DATABASE data_import_db;
```

#### 2. Create User (Optional)
```sql
CREATE USER import_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE data_import_db TO import_user;
```

### Environment Configuration

Create or update your `.env` file in the `backend` directory:

```env
# Server Configuration
PORT=8080
ENV=development

# PostgreSQL Database Configuration
DB_TYPE=postgres
DB_HOST=localhost
DB_PORT=5432
DB_USER=postgres
DB_PASSWORD=your_password
DB_NAME=data_import_db

# Upload Configuration
UPLOAD_PATH=./uploads
ALLOWED_ORIGINS=http://localhost,http://localhost:8000
```

### Installation Steps

1. **Install Dependencies**
```powershell
cd backend
go mod tidy
```

2. **Run the Application**
```powershell
go run main.go
```

Or build and run:
```powershell
go build -o data-import-api.exe
./data-import-api.exe
```

### Multi-Database Setup with PostgreSQL

#### Adding Additional PostgreSQL Databases

You can connect to multiple PostgreSQL databases:

```http
POST http://localhost:8080/databases
Content-Type: application/json

{
  "name": "analytics_db",
  "type": "postgres",
  "host": "localhost",
  "port": "5432",
  "user": "postgres",
  "password": "password",
  "dbname": "analytics",
  "sslmode": "disable"
}
```

#### Connecting to Remote PostgreSQL

```json
{
  "name": "production_db",
  "type": "postgres",
  "host": "prod-server.example.com",
  "port": "5432",
  "user": "prod_user",
  "password": "secure_password",
  "dbname": "production",
  "sslmode": "require"
}
```

### PostgreSQL Performance Tuning

#### For Large Imports

The application is configured with these PostgreSQL optimizations:

1. **Connection Pooling**
   - Max Open Connections: 100
   - Max Idle Connections: 10
   - Configured automatically in the application

2. **Batch Inserts**
   - Default batch size: 50,000 records
   - Adjustable via config

3. **Prepared Statements**
   - Enabled by default for better performance

#### PostgreSQL Server Configuration

For handling large imports, consider these PostgreSQL settings:

```sql
-- Increase shared buffers (25% of RAM recommended)
ALTER SYSTEM SET shared_buffers = '4GB';

-- Increase work memory for sorting/hashing
ALTER SYSTEM SET work_mem = '256MB';

-- Increase maintenance work memory for index creation
ALTER SYSTEM SET maintenance_work_mem = '2GB';

-- Increase max connections if needed
ALTER SYSTEM SET max_connections = 200;

-- Optimize checkpoint settings
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';

-- Apply changes
SELECT pg_reload_conf();
```

Note: Restart PostgreSQL after making changes.

### Creating Tables for Multi-Table Import

#### Example: Create Products Table

```sql
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2),
    category VARCHAR(100),
    stock INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Example: Create Orders Table

```sql
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER,
    order_date TIMESTAMP,
    total DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Example: Create Customers Table

```sql
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### PostgreSQL-Specific Features

#### Using PostgreSQL JSON Types

You can use PostgreSQL's native JSON types in your table configurations:

```json
{
  "name": "events_config",
  "database_name": "default",
  "table_name": "events",
  "columns": "[{\"name\":\"id\",\"type\":\"serial\",\"is_primary\":true},{\"name\":\"event_data\",\"type\":\"jsonb\"},{\"name\":\"created_at\",\"type\":\"timestamp\"}]"
}
```

#### Using PostgreSQL Arrays

```json
{
  "name": "tags_config",
  "table_name": "articles",
  "columns": "[{\"name\":\"id\",\"type\":\"serial\",\"is_primary\":true},{\"name\":\"tags\",\"type\":\"text[]\"}]"
}
```

### Common PostgreSQL Commands

#### Check Connection
```sql
SELECT version();
```

#### List Databases
```sql
\l
```

#### Connect to Database
```sql
\c data_import_db
```

#### List Tables
```sql
\dt
```

#### View Table Structure
```sql
\d table_name
```

#### Check Table Size
```sql
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### Troubleshooting

#### Connection Issues

1. **Error: "connection refused"**
   - Ensure PostgreSQL is running: `pg_ctl status`
   - Check if PostgreSQL is listening on the correct port: `netstat -an | findstr 5432`

2. **Error: "password authentication failed"**
   - Verify credentials in `.env` file
   - Check `pg_hba.conf` for authentication settings

3. **Error: "database does not exist"**
   - Create the database: `CREATE DATABASE data_import_db;`

#### Performance Issues

1. **Slow Imports**
   - Disable indexes temporarily during bulk import
   - Increase `work_mem` in PostgreSQL
   - Use larger batch sizes (adjust in config)

2. **Connection Pool Exhausted**
   - Increase `max_connections` in PostgreSQL
   - Adjust `DB_MAX_OPEN_CONNS` in application config

### Backup and Restore

#### Backup Database
```bash
pg_dump -U postgres -d data_import_db -F c -f backup.dump
```

#### Restore Database
```bash
pg_restore -U postgres -d data_import_db -c backup.dump
```

### Monitoring

#### Active Connections
```sql
SELECT count(*) FROM pg_stat_activity WHERE datname = 'data_import_db';
```

#### Running Queries
```sql
SELECT pid, query, state, query_start 
FROM pg_stat_activity 
WHERE datname = 'data_import_db' 
AND state != 'idle';
```

#### Lock Information
```sql
SELECT * FROM pg_locks WHERE granted = false;
```

### Security Best Practices

1. **Use Strong Passwords**
2. **Enable SSL Connections** (set `sslmode=require`)
3. **Restrict Host Access** in `pg_hba.conf`
4. **Regular Backups**
5. **Keep PostgreSQL Updated**

### Next Steps

1. ✓ Set up PostgreSQL database
2. ✓ Configure `.env` file
3. ✓ Run `go mod tidy` in backend directory
4. ✓ Start the application
5. ✓ Test with `/health` endpoint
6. → Follow [MULTI_TABLE_GUIDE.md](MULTI_TABLE_GUIDE.md) for multi-table features

### Support

For PostgreSQL-specific issues, consult:
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [GORM PostgreSQL Driver](https://gorm.io/docs/connecting_to_the_database.html#PostgreSQL)

For application issues, see [README.md](README.md) and other guide files.
