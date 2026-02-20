# 🔐 Create Admin User Manually in PostgreSQL

## Method 1: Quick SQL Insert (Recommended)

### Step 1: Connect to PostgreSQL

```powershell
# Open PowerShell and connect to PostgreSQL
psql -U postgres -d data_import_db

# Or if using different credentials:
psql -U your_db_user -d data_import_db -h localhost -p 5432
```

### Step 2: Create Admin User with SQL

```sql
-- Insert admin user directly
-- Password: "admin123" (already bcrypt hashed)
INSERT INTO users (username, password, role, full_name, email, created_at, updated_at) 
VALUES (
    'admin',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    'admin',
    'System Administrator',
    'admin@example.com',
    NOW(),
    NOW()
);

-- Verify user was created
SELECT id, username, role, full_name, email, created_at 
FROM users 
WHERE username = 'admin';
```

**Done!** You can now login with:

- **Username:** `admin`
- **Password:** `admin123`

---

## Method 2: Create User with Custom Password

If you want a different password, you need to generate a bcrypt hash first.

### Step 1: Generate Bcrypt Hash

**Option A - Using Online Tool:**
1. Go to: https://bcrypt-generator.com/
2. Enter your desired password
3. Use rounds: 10
4. Copy the generated hash

**Option B - Using Go (if you have Go installed):**

```powershell
# Create a temporary Go file
@"
package main
import (
    "fmt"
    "golang.org/x/crypto/bcrypt"
)
func main() {
    password := "YourPasswordHere"
    hash, _ := bcrypt.GenerateFromPassword([]byte(password), 10)
    fmt.Println(string(hash))
}
"@ | Out-File hash_password.go -Encoding UTF8

# Run it
go run hash_password.go
```

**Option C - Using Node.js (if installed):**

```powershell
# Install bcrypt if needed
npm install -g bcrypt

# Create hash script
@"
const bcrypt = require('bcrypt');
const password = 'YourPasswordHere';
bcrypt.hash(password, 10, (err, hash) => {
    console.log(hash);
});
"@ | Out-File hash_password.js -Encoding UTF8

# Run it
node hash_password.js
```

### Step 2: Insert User with Your Hash

```sql
-- Connect to database
psql -U postgres -d data_import_db

-- Insert with your custom hash
INSERT INTO users (username, password, role, full_name, email, created_at, updated_at) 
VALUES (
    'your_username',
    'YOUR_BCRYPT_HASH_HERE',
    'admin',
    'Your Full Name',
    'your.email@example.com',
    NOW(),
    NOW()
);
```

---

## Method 3: Using Backend Command (Automated)

If the backend code has the create-admin command:

```powershell
cd d:\DataImportDashboard\backend
go run main.go create-admin
```

This automatically creates:
- Username: `admin`
- Password: `admin123`
- Role: `admin`

---

## 🔍 Check Existing Users

### View all users:

```sql
-- Connect to database
psql -U postgres -d data_import_db

-- List all users
SELECT id, username, role, full_name, email, created_at 
FROM users 
ORDER BY created_at DESC;
```

---

## 🗑️ Delete a User (if needed)

```sql
-- Delete by username
DELETE FROM users WHERE username = 'username_to_delete';

-- Delete by ID
DELETE FROM users WHERE id = 1;

-- Delete all users (CAREFUL!)
DELETE FROM users;
```

---

## ✏️ Update User Details

### Change password:

```sql
-- Update password (use bcrypt hash)
UPDATE users 
SET password = 'YOUR_NEW_BCRYPT_HASH', 
    updated_at = NOW() 
WHERE username = 'admin';
```

### Change role:

```sql
-- Make user an admin
UPDATE users 
SET role = 'admin', 
    updated_at = NOW() 
WHERE username = 'john_doe';

-- Change to regular user
UPDATE users 
SET role = 'user', 
    updated_at = NOW() 
WHERE username = 'john_doe';
```

### Update email:

```sql
UPDATE users 
SET email = 'newemail@example.com', 
    updated_at = NOW() 
WHERE username = 'admin';
```

---

## 🧪 Test the Admin Account

### Using PowerShell:

```powershell
# Login with the admin account
$response = Invoke-RestMethod `
    -Uri "http://localhost:8080/api/auth/login" `
    -Method Post `
    -Body (@{
        username = "admin"
        password = "admin123"
    } | ConvertTo-Json) `
    -ContentType "application/json"

# Display token
Write-Host "  Login successful!" -ForegroundColor Green
Write-Host "Token: $($response.token)"
Write-Host "User: $($response.user.username)"
Write-Host "Role: $($response.user.role)"
```

---

## 📋 Common PostgreSQL Commands

### Connect to database:
```powershell
# Default connection
psql -U postgres -d data_import_db

# With password prompt
psql -U postgres -d data_import_db -W

# Specify host and port
psql -U postgres -d data_import_db -h localhost -p 5432
```

### Inside psql:
```sql
-- List all tables
\dt

-- Describe users table
\d users

-- Show all databases
\l

-- Quit psql
\q
```

---

## 🔐 User Table Schema Reference

The `users` table typically has these columns:

| Column | Type | Description |
|--------|------|-------------|
| `id` | SERIAL/INTEGER | Primary key (auto-increment) |
| `username` | VARCHAR | Unique username |
| `password` | VARCHAR | Bcrypt hashed password |
| `role` | VARCHAR | 'admin' or 'user' |
| `full_name` | VARCHAR | User's full name |
| `email` | VARCHAR | Email address |
| `created_at` | TIMESTAMP | When user was created |
| `updated_at` | TIMESTAMP | Last update time |

---

## 🚨 Troubleshooting

### "psql: command not found"

**Solution:** Add PostgreSQL to PATH or use full path:

```powershell
# Windows - Find your PostgreSQL installation
# Usually: C:\Program Files\PostgreSQL\14\bin\psql.exe

# Use full path
& "C:\Program Files\PostgreSQL\14\bin\psql.exe" -U postgres -d data_import_db

# Or add to PATH globally
$env:Path += ";C:\Program Files\PostgreSQL\14\bin"
```

### "database does not exist"

**Solution:** Create the database first:

```powershell
# Connect to postgres default database
psql -U postgres -d postgres

# Create database
CREATE DATABASE data_import_db;

# Exit
\q

# Now connect to your database
psql -U postgres -d data_import_db
```

### "table users does not exist"

**Solution:** Run migrations or create table:

```sql
-- Create users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    full_name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Verify table
\d users
```

### "password authentication failed"

**Solution:** Check PostgreSQL user password:

```powershell
# Reset postgres password (as Windows admin)
# 1. Stop PostgreSQL service
Stop-Service postgresql*

# 2. Edit pg_hba.conf to trust local connections temporarily
# Location: C:\Program Files\PostgreSQL\14\data\pg_hba.conf
# Change: local all postgres md5
# To: local all postgres trust

# 3. Start PostgreSQL
Start-Service postgresql*

# 4. Connect and reset password
psql -U postgres
ALTER USER postgres PASSWORD 'your_new_password';
\q

# 5. Revert pg_hba.conf changes and restart
```

---

##   Quick Reference

**Create default admin:**
```sql
INSERT INTO users (username, password, role, full_name, email, created_at, updated_at) 
VALUES ('admin', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin', 'Administrator', 'admin@example.com', NOW(), NOW());
```

**Login credentials:**
- Username: `admin`
- Password: `admin123`

**Test login:**
```powershell
$r = Invoke-RestMethod -Uri "http://localhost:8080/api/auth/login" -Method Post `
    -Body '{"username":"admin","password":"admin123"}' -ContentType "application/json"
$token = $r.token
Write-Host "Token: $token"
```

---

## 🎯 Summary

1. **Connect:** `psql -U postgres -d data_import_db`
2. **Insert:** Copy the SQL INSERT command from Method 1
3. **Test:** Login via API to get token
4. **Done:** Use token for stress testing

**The fastest way is Method 1 - just copy/paste the SQL and you're done!**  
