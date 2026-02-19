package handlers

import (
	"dataImportDashboard/repository"
	"net/http"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"
)

type UserTablePermissionHandler struct {
	permRepo  *repository.UserTablePermissionRepository
	tableRepo *repository.TableConfigRepository
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
) *UserTablePermissionHandler {
	return &UserTablePermissionHandler{
		permRepo:  permRepo,
		tableRepo: tableRepo,
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

	// ✅ ALWAYS return ALL tables for admin management page
	// Laravel already protects this route - only admins can access it
	allTables, err := h.tableRepo.GetAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"error":   "Failed to fetch tables",
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    allTables,
		"count":   len(allTables),
	})
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
