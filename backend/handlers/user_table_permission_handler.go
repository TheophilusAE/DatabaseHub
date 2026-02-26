package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"fmt"
	"net/http"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type UserTablePermissionHandler struct {
	permRepo  *repository.UserTablePermissionRepository
	tableRepo *repository.TableConfigRepository
	dbManager *config.MultiDatabaseManager
}

func getRequestUserRole(c *gin.Context) string {
	role := strings.TrimSpace(c.Query("user_role"))
	if role == "" {
		if ctxRole, exists := c.Get("user_role"); exists {
			if roleStr, ok := ctxRole.(string); ok {
				role = strings.TrimSpace(roleStr)
			}
		}
	}
	if role == "" {
		role = strings.TrimSpace(c.GetHeader("X-User-Role"))
	}

	return strings.ToLower(role)
}

func NewUserTablePermissionHandler(
	permRepo *repository.UserTablePermissionRepository,
	tableRepo *repository.TableConfigRepository,
	dbManager *config.MultiDatabaseManager,
) *UserTablePermissionHandler {
	if dbManager == nil {
		dbManager = config.GetMultiDatabaseManager()
	}

	return &UserTablePermissionHandler{
		permRepo:  permRepo,
		tableRepo: tableRepo,
		dbManager: dbManager,
	}
}

// GetAccessibleTables returns tables based on WHO is requesting (not target user)
// GetAccessibleTables returns ALL tables for admin permission management
// ✅ No auth required - Laravel frontend already ensures only admins can access this page
func (h *UserTablePermissionHandler) GetAccessibleTables(c *gin.Context) {
	userIDStr := c.Param("userId")
	_, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{
			"success": false,
			"error":   "Invalid user ID",
		})
		return
	}

	// Auto-discover and sync tables from all active DB connections
	if syncErr := h.syncDiscoveredTablesForPermissions(); syncErr != nil {
		// Continue with existing data even if discovery partially fails
		fmt.Printf("Warning: table auto-discovery sync failed: %v\n", syncErr)
	}

	// ✅ ALWAYS return ALL synced tables for admin management page
	// Laravel + middleware already protect this route
	allTables, err := h.tableRepo.GetAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"error":   "Failed to fetch tables",
		})
		return
	}

	connectionNames := map[string]bool{}
	if h.dbManager != nil {
		for _, name := range h.dbManager.ListConnections() {
			connectionNames[strings.ToLower(strings.TrimSpace(name))] = true
		}
	}

	filteredTables := make([]models.TableConfig, 0, len(allTables))
	for _, table := range allTables {
		dbName := strings.ToLower(strings.TrimSpace(table.DatabaseName))
		if dbName == "" {
			continue
		}

		if len(connectionNames) == 0 || connectionNames[dbName] {
			filteredTables = append(filteredTables, table)
		}
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    filteredTables,
		"count":   len(filteredTables),
	})
}

func (h *UserTablePermissionHandler) syncDiscoveredTablesForPermissions() error {
	if h.dbManager == nil {
		return nil
	}

	connections := h.dbManager.ListConnectionDetails()
	for _, conn := range connections {
		if conn == nil {
			continue
		}

		databaseKey := strings.TrimSpace(conn.Name)
		if databaseKey == "" {
			continue
		}

		dbConn, err := h.dbManager.GetConnection(databaseKey)
		if err != nil {
			continue
		}

		tableNames, err := h.listTablesForConnection(dbConn, conn.Type)
		if err != nil {
			continue
		}

		for _, tableName := range tableNames {
			tableName = strings.TrimSpace(tableName)
			if tableName == "" {
				continue
			}

			existing, findErr := h.tableRepo.FindByDatabaseAndTable(databaseKey, tableName)
			if findErr == nil && existing != nil && existing.ID != 0 {
				if !existing.IsActive {
					existing.IsActive = true
					_ = h.tableRepo.Update(existing)
				}
				continue
			}

			primaryKey, pkErr := h.getPrimaryKeyForConnection(dbConn, conn.Type, tableName)
			if pkErr != nil || strings.TrimSpace(primaryKey) == "" {
				primaryKey = "id"
			}

			displayDBName := strings.TrimSpace(conn.DBName)
			if displayDBName == "" {
				displayDBName = databaseKey
			}

			tableConfig := models.TableConfig{
				Name:         fmt.Sprintf("%s_%s", databaseKey, tableName),
				DatabaseName: databaseKey,
				Table:        tableName,
				Description:  fmt.Sprintf("Auto-discovered from database '%s'", displayDBName),
				Columns:      "[]",
				PrimaryKey:   primaryKey,
				IsActive:     true,
				CreatedBy:    "system-auto",
			}

			_ = h.tableRepo.Create(&tableConfig)
		}
	}

	return nil
}

func (h *UserTablePermissionHandler) listTablesForConnection(db *gorm.DB, dbType string) ([]string, error) {
	dbKind := strings.ToLower(strings.TrimSpace(dbType))
	query := ""

	switch dbKind {
	case "mysql":
		query = `
			SELECT table_name
			FROM information_schema.tables
			WHERE table_schema = DATABASE()
			AND table_type = 'BASE TABLE'
			ORDER BY table_name
		`
	default:
		query = `
			SELECT table_name
			FROM information_schema.tables
			WHERE table_schema = 'public'
			AND table_type = 'BASE TABLE'
			ORDER BY table_name
		`
	}

	rows, err := db.Raw(query).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	tables := []string{}
	for rows.Next() {
		var tableName string
		if scanErr := rows.Scan(&tableName); scanErr != nil {
			continue
		}
		tables = append(tables, tableName)
	}

	return tables, nil
}

func (h *UserTablePermissionHandler) getPrimaryKeyForConnection(db *gorm.DB, dbType string, tableName string) (string, error) {
	dbKind := strings.ToLower(strings.TrimSpace(dbType))
	query := ""

	switch dbKind {
	case "mysql":
		query = `
			SELECT kcu.column_name
			FROM information_schema.table_constraints tc
			JOIN information_schema.key_column_usage kcu
				ON tc.constraint_name = kcu.constraint_name
				AND tc.table_schema = kcu.table_schema
				AND tc.table_name = kcu.table_name
			WHERE tc.constraint_type = 'PRIMARY KEY'
			AND tc.table_schema = DATABASE()
			AND tc.table_name = ?
			ORDER BY kcu.ordinal_position
			LIMIT 1
		`
	default:
		query = `
			SELECT kcu.column_name
			FROM information_schema.table_constraints tc
			JOIN information_schema.key_column_usage kcu
				ON tc.constraint_name = kcu.constraint_name
				AND tc.table_schema = kcu.table_schema
			WHERE tc.constraint_type = 'PRIMARY KEY'
			AND tc.table_schema = 'public'
			AND tc.table_name = ?
			ORDER BY kcu.ordinal_position
			LIMIT 1
		`
	}

	var primaryKey string
	if err := db.Raw(query, tableName).Scan(&primaryKey).Error; err != nil {
		return "", err
	}

	return strings.TrimSpace(primaryKey), nil
}

// GetUserPermissions gets all permission records for a specific user
// ✅ No auth required - Laravel frontend already ensures only admins can access this page
func (h *UserTablePermissionHandler) GetUserPermissions(c *gin.Context) {
	userIDStr := c.Param("userId")
	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid user ID"})
		return
	}

	// ✅ No authentication check - Laravel protects this admin-only route
	// Admin can view any user's permissions to manage them

	permissions, err := h.permRepo.GetUserPermissions(uint(userID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to fetch permissions"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "data": permissions})
}

// BulkAssignTablePermissions assigns multiple table permissions to a user (ADMIN ONLY)
func (h *UserTablePermissionHandler) BulkAssignTablePermissions(c *gin.Context) {
	if getRequestUserRole(c) != "admin" {
		c.JSON(http.StatusForbidden, gin.H{"success": false, "error": "Only administrators can assign permissions"})
		return
	}

	var request struct {
		UserID         uint   `json:"user_id"`
		TableConfigIDs []uint `json:"table_config_ids"`
		CanView        bool   `json:"can_view"`
		CanEdit        bool   `json:"can_edit"`
		CanDelete      bool   `json:"can_delete"`
		CanExport      bool   `json:"can_export"`
		CanImport      bool   `json:"can_import"`
	}

	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid request body"})
		return
	}

	// Revoke all existing permissions first
	err := h.permRepo.RevokeAllPermissions(request.UserID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to revoke existing permissions"})
		return
	}

	// Assign new permissions
	if len(request.TableConfigIDs) > 0 {
		err = h.permRepo.BulkAssignPermissions(
			request.UserID, request.TableConfigIDs,
			request.CanView, request.CanEdit, request.CanDelete,
			request.CanExport, request.CanImport,
		)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to assign permissions"})
			return
		}
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "message": "Permissions updated successfully"})
}

// RevokeAllUserPermissions removes all table permissions for a user (ADMIN ONLY)
func (h *UserTablePermissionHandler) RevokeAllUserPermissions(c *gin.Context) {
	if getRequestUserRole(c) != "admin" {
		c.JSON(http.StatusForbidden, gin.H{"success": false, "error": "Only administrators can revoke permissions"})
		return
	}

	userIDStr := c.Param("userId")
	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid user ID"})
		return
	}

	err = h.permRepo.RevokeAllPermissions(uint(userID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to revoke permissions"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "message": "All permissions revoked successfully"})
}

// CheckTableAccess checks if a user has access to a specific table
func (h *UserTablePermissionHandler) CheckTableAccess(c *gin.Context) {
	userIDStr := c.Param("userId")
	tableIDStr := c.Param("tableId")

	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid user ID"})
		return
	}

	tableID, err := strconv.ParseUint(tableIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid table ID"})
		return
	}

	hasAccess, err := h.permRepo.HasTableAccess(uint(userID), uint(tableID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to check access"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "has_access": hasAccess})
}

// AssignTablePermission assigns a single table permission to a user (ADMIN ONLY)
func (h *UserTablePermissionHandler) AssignTablePermission(c *gin.Context) {
	if getRequestUserRole(c) != "admin" {
		c.JSON(http.StatusForbidden, gin.H{"success": false, "error": "Only administrators can assign permissions"})
		return
	}

	var request struct {
		UserID        uint `json:"user_id"`
		TableConfigID uint `json:"table_config_id"`
		CanView       bool `json:"can_view"`
		CanEdit       bool `json:"can_edit"`
		CanDelete     bool `json:"can_delete"`
		CanExport     bool `json:"can_export"`
		CanImport     bool `json:"can_import"`
	}

	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid request body"})
		return
	}

	err := h.permRepo.AssignPermission(
		request.UserID, request.TableConfigID,
		request.CanView, request.CanEdit, request.CanDelete,
		request.CanExport, request.CanImport,
	)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to assign permission"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "message": "Permission assigned successfully"})
}

// RevokeTablePermission removes a specific table permission from a user (ADMIN ONLY)
func (h *UserTablePermissionHandler) RevokeTablePermission(c *gin.Context) {
	if getRequestUserRole(c) != "admin" {
		c.JSON(http.StatusForbidden, gin.H{"success": false, "error": "Only administrators can revoke permissions"})
		return
	}

	userIDStr := c.Param("userId")
	tableIDStr := c.Param("tableId")

	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid user ID"})
		return
	}

	tableID, err := strconv.ParseUint(tableIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "error": "Invalid table ID"})
		return
	}

	err = h.permRepo.RevokePermission(uint(userID), uint(tableID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "error": "Failed to revoke permission"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"success": true, "message": "Permission revoked successfully"})
}
