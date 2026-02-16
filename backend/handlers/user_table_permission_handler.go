package handlers

import (
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

// UserTablePermissionHandler handles user table permission operations
type UserTablePermissionHandler struct {
	permRepo        *repository.UserTablePermissionRepository
	userRepo        *repository.UserRepository
	tableConfigRepo *repository.TableConfigRepository
}

// NewUserTablePermissionHandler creates a new handler
func NewUserTablePermissionHandler(
	permRepo *repository.UserTablePermissionRepository,
	userRepo *repository.UserRepository,
	tableConfigRepo *repository.TableConfigRepository,
) *UserTablePermissionHandler {
	return &UserTablePermissionHandler{
		permRepo:        permRepo,
		userRepo:        userRepo,
		tableConfigRepo: tableConfigRepo,
	}
}

// AssignTablePermissionRequest represents the request to assign table permissions
type AssignTablePermissionRequest struct {
	UserID        uint `json:"user_id" binding:"required"`
	TableConfigID uint `json:"table_config_id" binding:"required"`
	CanView       bool `json:"can_view"`
	CanEdit       bool `json:"can_edit"`
	CanDelete     bool `json:"can_delete"`
	CanExport     bool `json:"can_export"`
	CanImport     bool `json:"can_import"`
}

// BulkAssignTablePermissionRequest for assigning multiple tables at once
type BulkAssignTablePermissionRequest struct {
	UserID         uint   `json:"user_id" binding:"required"`
	TableConfigIDs []uint `json:"table_config_ids" binding:"required"`
	CanView        bool   `json:"can_view"`
	CanEdit        bool   `json:"can_edit"`
	CanDelete      bool   `json:"can_delete"`
	CanExport      bool   `json:"can_export"`
	CanImport      bool   `json:"can_import"`
}

// GetUserPermissions retrieves all table permissions for a user
func (h *UserTablePermissionHandler) GetUserPermissions(c *gin.Context) {
	userIDStr := c.Param("userId")
	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
		return
	}

	permissions, err := h.permRepo.GetUserPermissions(uint(userID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    permissions,
	})
}

// GetAccessibleTables returns tables accessible to a user
func (h *UserTablePermissionHandler) GetAccessibleTables(c *gin.Context) {
	userIDStr := c.Param("userId")
	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
		return
	}

	tableIDs, err := h.permRepo.GetAccessibleTables(uint(userID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	// Get full table configs
	tables := []models.TableConfig{}
	if len(tableIDs) > 0 {
		if err := h.tableConfigRepo.GetByIDs(tableIDs, &tables); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    tables,
	})
}

// AssignTablePermission creates or updates a table permission for a user
func (h *UserTablePermissionHandler) AssignTablePermission(c *gin.Context) {
	var req AssignTablePermissionRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// Check if user exists
	user, err := h.userRepo.GetByID(req.UserID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "User not found"})
		return
	}

	// Admin users have access to all tables by default
	if user.IsAdmin() {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Cannot assign table permissions to admin users"})
		return
	}

	// Check if table config exists
	tableConfig, err := h.tableConfigRepo.GetByID(req.TableConfigID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table configuration not found"})
		return
	}

	// Check if permission already exists
	existing, err := h.permRepo.GetPermissionByUserAndTable(req.UserID, req.TableConfigID)
	if err == nil {
		// Update existing
		existing.CanView = req.CanView
		existing.CanEdit = req.CanEdit
		existing.CanDelete = req.CanDelete
		existing.CanExport = req.CanExport
		existing.CanImport = req.CanImport

		if err := h.permRepo.Update(existing); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}

		c.JSON(http.StatusOK, gin.H{
			"success": true,
			"message": "Permission updated successfully",
			"data":    existing,
		})
		return
	}

	// Create new permission
	permission := &models.UserTablePermission{
		UserID:        req.UserID,
		TableConfigID: req.TableConfigID,
		CanView:       req.CanView,
		CanEdit:       req.CanEdit,
		CanDelete:     req.CanDelete,
		CanExport:     req.CanExport,
		CanImport:     req.CanImport,
	}

	if err := h.permRepo.Create(permission); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	// Load the table config for response
	permission.TableConfig = *tableConfig

	c.JSON(http.StatusCreated, gin.H{
		"success": true,
		"message": "Permission assigned successfully",
		"data":    permission,
	})
}

// BulkAssignTablePermissions assigns multiple tables to a user at once
func (h *UserTablePermissionHandler) BulkAssignTablePermissions(c *gin.Context) {
	var req BulkAssignTablePermissionRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// Check if user exists
	user, err := h.userRepo.GetByID(req.UserID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "User not found"})
		return
	}

	// Admin users have access to all tables by default
	if user.IsAdmin() {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Cannot assign table permissions to admin users"})
		return
	}

	permissions := models.UserTablePermission{
		CanView:   req.CanView,
		CanEdit:   req.CanEdit,
		CanDelete: req.CanDelete,
		CanExport: req.CanExport,
		CanImport: req.CanImport,
	}

	if err := h.permRepo.BulkCreatePermissions(req.UserID, req.TableConfigIDs, permissions); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"message": "Permissions assigned successfully",
	})
}

// RevokeTablePermission removes a table permission
func (h *UserTablePermissionHandler) RevokeTablePermission(c *gin.Context) {
	userIDStr := c.Param("userId")
	tableIDStr := c.Param("tableId")

	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
		return
	}

	tableID, err := strconv.ParseUint(tableIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table ID"})
		return
	}

	if err := h.permRepo.DeleteByUserAndTable(uint(userID), uint(tableID)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"message": "Permission revoked successfully",
	})
}

// RevokeAllUserPermissions removes all table permissions for a user
func (h *UserTablePermissionHandler) RevokeAllUserPermissions(c *gin.Context) {
	userIDStr := c.Param("userId")
	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
		return
	}

	if err := h.permRepo.RevokeAllUserPermissions(uint(userID)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"message": "All permissions revoked successfully",
	})
}

// CheckTableAccess checks if a user has access to a specific table
func (h *UserTablePermissionHandler) CheckTableAccess(c *gin.Context) {
	userIDStr := c.Param("userId")
	tableIDStr := c.Param("tableId")

	userID, err := strconv.ParseUint(userIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
		return
	}

	tableID, err := strconv.ParseUint(tableIDStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table ID"})
		return
	}

	hasAccess, err := h.permRepo.HasTableAccess(uint(userID), uint(tableID))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success":    true,
		"has_access": hasAccess,
	})
}
