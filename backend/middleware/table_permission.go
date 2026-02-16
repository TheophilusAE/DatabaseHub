package middleware

import (
	"dataImportDashboard/repository"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

// TablePermissionMiddleware checks if a user has access to a specific table
type TablePermissionMiddleware struct {
	permRepo *repository.UserTablePermissionRepository
}

// NewTablePermissionMiddleware creates a new middleware instance
func NewTablePermissionMiddleware(permRepo *repository.UserTablePermissionRepository) *TablePermissionMiddleware {
	return &TablePermissionMiddleware{
		permRepo: permRepo,
	}
}

// CheckTableAccess is a middleware that verifies user has access to a table
func (m *TablePermissionMiddleware) CheckTableAccess() gin.HandlerFunc {
	return func(c *gin.Context) {
		// Get user_id from query or context (you'll need to set this from your auth)
		userIDStr := c.Query("user_id")
		if userIDStr == "" {
			// Try to get from context if you have auth middleware
			if userID, exists := c.Get("user_id"); exists {
				userIDStr = strconv.FormatUint(uint64(userID.(uint)), 10)
			}
		}

		// Get table_config_id from query
		tableIDStr := c.Query("table_config_id")
		if tableIDStr == "" {
			tableIDStr = c.Param("tableId")
		}

		if userIDStr == "" || tableIDStr == "" {
			c.Next() // Skip check if no user or table specified
			return
		}

		userID, err := strconv.ParseUint(userIDStr, 10, 32)
		if err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid user ID"})
			c.Abort()
			return
		}

		tableID, err := strconv.ParseUint(tableIDStr, 10, 32)
		if err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table ID"})
			c.Abort()
			return
		}

		// Check if user has access
		hasAccess, err := m.permRepo.HasTableAccess(uint(userID), uint(tableID))
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to check permissions"})
			c.Abort()
			return
		}

		if !hasAccess {
			c.JSON(http.StatusForbidden, gin.H{"error": "You do not have permission to access this table"})
			c.Abort()
			return
		}

		c.Next()
	}
}

// Helper function to check if user is admin (bypass table permissions)
func IsAdmin(c *gin.Context) bool {
	role, exists := c.Get("user_role")
	if !exists {
		return false
	}
	return role == "admin"
}

// CheckTableAccessOrAdmin checks table permission or allows if user is admin
func (m *TablePermissionMiddleware) CheckTableAccessOrAdmin() gin.HandlerFunc {
	return func(c *gin.Context) {
		// Check if user is admin first
		if IsAdmin(c) {
			c.Next()
			return
		}

		// Otherwise check table permission
		m.CheckTableAccess()(c)
	}
}
