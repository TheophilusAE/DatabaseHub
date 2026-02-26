# ðŸ” Table-Level Authorization System

## Overview

The DataBridge now supports **table-level permissions** where administrators can control which tables each user can view and access. This provides fine-grained access control for multi-tenant environments or scenarios where users should only see specific data sets.

---

## ðŸŽ¯ Key Features

  **Granular Permissions**: Control access at the table level  
  **Admin Override**: Administrators always have full access to all tables  
  **Bulk Assignment**: Assign multiple tables to a user at once  
  **Visual Management**: Easy-to-use UI for managing permissions  
  **Automatic Filtering**: Views automatically show only permitted tables  
  **Future-Ready**: Support for view, edit, delete, export, import permissions (currently view is active)

---

## ðŸ—ï¸ Architecture

### Database Schema

**Table**: `user_table_permissions`

| Column | Type | Description |
|--------|------|-------------|
| id | INTEGER | Primary key |
| user_id | INTEGER | Reference to users table |
| table_config_id | INTEGER | Reference to table_configs table |
| can_view | BOOLEAN | User can view table data |
| can_edit | BOOLEAN | User can edit records (future) |
| can_delete | BOOLEAN | User can delete records (future) |
| can_export | BOOLEAN | User can export data (future) |
| can_import | BOOLEAN | User can import data (future) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |

### Backend Components

1. **Model**: `backend/models/user_table_permission.go`
   - Defines the permission structure
   - Relationships with User and TableConfig

2. **Repository**: `backend/repository/user_table_permission_repository.go`
   - CRUD operations for permissions
   - Bulk assignment methods
   - Permission checking utilities

3. **Handler**: `backend/handlers/user_table_permission_handler.go`
   - API endpoints for managing permissions
   - Validation and business logic

4. **Middleware**: `backend/middleware/table_permission.go`
   - Optional middleware for enforcing permissions
   - Can be applied to specific routes

### Frontend Components

1. **Permissions Page**: `frontend/resources/views/admin/users/permissions.blade.php`
   - UI for selecting which tables a user can access
   - Real-time updates and visual feedback

2. **User Index**: Updated to include "ðŸ” Permissions" button
3. **Controller**: Added `permissions()` method to UserController

---

## ðŸ“š API Endpoints

### Get User Permissions
```http
GET /permissions/users/:userId
```
Returns all table permissions for a specific user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "table_config_id": 3,
      "can_view": true,
      "can_edit": false,
      "table_config": {
        "id": 3,
        "name": "Sales Data",
        "table_name": "sales"
      }
    }
  ]
}
```

### Get Accessible Tables
```http
GET /permissions/users/:userId/tables
```
Returns only the tables the user has access to.

### Assign Single Table Permission
```http
POST /permissions/assign
Content-Type: application/json

{
  "user_id": 5,
  "table_config_id": 3,
  "can_view": true,
  "can_edit": false,
  "can_delete": false,
  "can_export": false,
  "can_import": false
}
```

### Bulk Assign Permissions
```http
POST /permissions/bulk-assign
Content-Type: application/json

{
  "user_id": 5,
  "table_config_ids": [1, 3, 5, 7],
  "can_view": true,
  "can_edit": false,
  "can_delete": false,
  "can_export": false,
  "can_import": false
}
```

### Revoke Specific Permission
```http
DELETE /permissions/users/:userId/tables/:tableId
```

### Revoke All User Permissions
```http
DELETE /permissions/users/:userId/all
```

### Check Table Access
```http
GET /permissions/check/:userId/:tableId
```
Returns whether user has access to a specific table.

---

## ðŸŽ¨ How to Use

### For Administrators

#### Assigning Table Permissions

1. **Navigate to User Management**
   ```
   Admin Dashboard â†’ Users
   ```

2. **Select a User**
   - Click the "ðŸ” Permissions" button next to any regular user
   - Note: Admin users don't need permissions (they have full access)

3. **Select Tables**
   - Check the boxes for tables the user should be able to view
   - Click "Select All" to grant access to all tables
   - Click "Deselect All" to remove all access

4. **Save Permissions**
   - Click "ðŸ’¾ Save Permissions" button
   - Changes take effect immediately

#### Visual Indicators

- **Green Badge**: "Granted" - User has access
- **Gray Badge**: "No Access" - User cannot view this table

### For Regular Users

When logged in as a regular user:

- **View Tables**: Navigate to any table view (e.g., "View All Tables")
- **Filtered Results**: Only tables you have permission to view will be displayed
- **No Warning Messages**: If you don't see a table, you don't have access to it

### Programmatic Usage

#### Check if User Has Access (JavaScript)

```javascript
const userId = 5;
const tableId = 3;

const response = await fetch(`http://localhost:8080/permissions/check/${userId}/${tableId}`);
const data = await response.json();

if (data.has_access) {
  // User can access this table
  loadTableData(tableId);
} else {
  // User cannot access this table
  showAccessDenied();
}
```

#### Get User's Accessible Tables

```javascript
const userId = 5;

const response = await fetch(`http://localhost:8080/permissions/users/${userId}/tables`);
const data = await response.json();

const tables = data.data; // Array of table configs
console.log(`User can access ${tables.length} tables`);
```

#### Filter Table List by User

```javascript
// In your table listing endpoint
const userId = session('user')['id'];
const userRole = session('user')['role'];

let url = 'http://localhost:8080/tables';
if (userRole !== 'admin') {
  url += `?user_id=${userId}&user_role=${userRole}`;
}

const response = await fetch(url);
const data = await response.json();
// data.data contains only tables user can access
```

---

## ðŸ”§ Integration with Existing Features

### Table Configuration

When creating table configurations (`/tables` endpoint):
- Admins can create any table config
- Regular users can only see configs they have permission for
- Use `?user_id=X&user_role=user` query params to filter

### Simple Multi-Table View

The "View All Tables" feature now automatically filters:
- Admins see all tables
- Regular users see only permitted tables
- No code changes needed in views - filtering happens server-side

### Future Integration Points

The permission model supports additional operations:
- **can_edit**: Control who can modify records
- **can_delete**: Control who can delete records
- **can_export**: Control who can export data
- **can_import**: Control who can upload data

These can be enabled by updating the handlers to check respective permissions.

---

## ðŸš€ Best Practices

### Security Considerations

1. **Always Check Permissions Server-Side**
   - Don't rely solely on frontend filtering
   - Use middleware for API endpoints that access table data

2. **Admin Bypass**
   - Admins automatically have access to all tables
   - No need to assign permissions to admin users

3. **Default Deny**
   - Users have no access by default
   - Permissions must be explicitly granted

### Performance

1. **Bulk Operations**
   - Use `/permissions/bulk-assign` for multiple tables
   - More efficient than multiple single assignments

2. **Caching Considerations**
   - Consider caching user permissions in session
   - Invalidate cache when permissions change

3. **Database Indexes**
   - Indexes on `user_id` and `table_config_id` columns
   - Composite index for faster permission lookups

---

## ðŸ› Troubleshooting

### User Can't See Any Tables

**Symptom**: Regular user sees "No tables found"

**Solutions**:
1. Check if user has been assigned any table permissions
2. Go to Admin â†’ Users â†’ Click Permissions â†’ Assign tables
3. Verify user role is not 'admin' (admins see all tables automatically)

### Permission Changes Not Taking Effect

**Symptom**: User still sees old table list after permission update

**Solutions**:
1. Hard refresh the page (Ctrl+F5)
2. Clear browser cache
3. Log out and log back in
4. Check browser console for API errors

### Admin Can't Assign Permissions

**Symptom**: Error when clicking "Permissions" button

**Solutions**:
1. Ensure backend server is running
2. Check `/tables` endpoint returns table configs
3. Verify table_configs table is populated
4. Check browser console for API errors

### API Returns Empty Table List

**Symptom**: `/permissions/users/:userId/tables` returns empty array

**Solutions**:
1. Verify user exists
2. Check if any permissions are assigned
3. Verify table_configs are active (`is_active = true`)
4. Ensure permissions reference valid table_config_ids

---

## ðŸ“ˆ Future Enhancements

### Planned Features

- [ ] **Permission Roles**: Create reusable permission sets
- [ ] **Time-Based Access**: Temporary table access with expiration
- [ ] **Row-Level Security**: Filter specific rows within tables
- [ ] **Audit Logging**: Track who accessed what and when
- [ ] **Permission Templates**: Quick apply common permission sets
- [ ] **Department/Group Permissions**: Assign tables to groups of users

### Integration Opportunities

- **Data Masking**: Hide sensitive columns for certain users
- **Export Limits**: Control export row limits per user
- **Import Validation**: Different validation rules per user
- **Custom Views**: User-specific table views with filters

---

## ðŸ“ Migration Guide

### Upgrading from Previous Version

1. **Restart Backend Server**
   ```bash
   cd backend
   go run main.go
   ```
   The new `user_table_permissions` table will be created automatically.

2. **Clear Browser Cache**
   - Hard refresh all pages (Ctrl+F5)

3. **Assign Initial Permissions**
   - All existing users will have no table access by default
   - Admins automatically have access to everything
   - Assign tables to regular users via Admin â†’ Users â†’ Permissions

### For Developers

If extending the system:

1. **Add Permission Checks to New Endpoints**
   ```go
   // In your handler
   userID := c.Query("user_id")
   tableID := c.Query("table_config_id")
   
   hasAccess, _ := h.permRepo.HasTableAccess(userID, tableID)
   if !hasAccess {
       c.JSON(403, gin.H{"error": "Access denied"})
       return
   }
   ```

2. **Use Middleware for Routes**
   ```go
   // In routes.go
   tablePermMiddleware := middleware.NewTablePermissionMiddleware(permRepo)
   
   protectedGroup := engine.Group("/protected")
   protectedGroup.Use(tablePermMiddleware.CheckTableAccessOrAdmin())
   {
       // Your protected routes
   }
   ```

---

##   Testing Checklist

- [ ] Admin can see all tables
- [ ] Regular user sees only assigned tables
- [ ] Assigning table permission works
- [ ] Revoking permission works
- [ ] Bulk assignment works
- [ ] "Select All" / "Deselect All" works
- [ ] Permission changes reflect immediately
- [ ] Cannot assign permissions to admin users
- [ ] Table views filter correctly
- [ ] API endpoints return correct data

---

## ðŸ“ž Support

For issues or questions:
1. Check the troubleshooting section above
2. Review API documentation
3. Check browser console for errors
4. Verify backend logs for issues

---

**Last Updated**: February 16, 2026  
**Version**: 1.0.0  
**Feature**: Table-Level Authorization

