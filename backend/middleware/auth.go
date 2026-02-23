package middleware

import (
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

// AuthRequired checks if user is authenticated
// Supports: 1) Context values (from session/JWT middleware), 2) Query params (fallback)
func AuthRequired() gin.HandlerFunc {
	return func(c *gin.Context) {
		// Priority 1: Get from Gin context (set by session/JWT middleware earlier)
		userID, exists := c.Get("user_id")
		userRole, roleExists := c.Get("user_role")

		if exists && userID != nil {
			// User already authenticated by earlier middleware
			c.Set("user_id", userID)
			if roleExists {
				c.Set("user_role", userRole)
			}
			c.Next()
			return
		}

		// Priority 2: Fallback to query params (for transition/testing)
		userIDStr := c.Query("user_id")
		userRoleQuery := c.Query("user_role")

		if userIDStr != "" {
			if uid, err := strconv.ParseUint(userIDStr, 10, 32); err == nil {
				c.Set("user_id", uint(uid))
				if userRoleQuery != "" {
					c.Set("user_role", userRoleQuery)
				}
				c.Next()
				return
			}
		}

		// Priority 3: Fallback to headers (less secure, but flexible)
		headerUserID := c.GetHeader("X-User-ID")
		headerRole := c.GetHeader("X-User-Role")
		if headerUserID != "" {
			if uid, err := strconv.ParseUint(headerUserID, 10, 32); err == nil {
				c.Set("user_id", uint(uid))
				if headerRole != "" {
					c.Set("user_role", headerRole)
				}
				c.Next()
				return
			}
		}

		// ❌ No valid authentication found
		c.JSON(http.StatusUnauthorized, gin.H{
			"error": "Authentication required. Please login or provide valid user credentials.",
		})
		c.Abort()
	}
}
