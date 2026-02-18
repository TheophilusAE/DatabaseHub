package middleware

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

// AdminOnly is a middleware that restricts access to admin users only
func AdminOnly() gin.HandlerFunc {
	return func(c *gin.Context) {
		// Check for user_role parameter (typically from query or session)
		userRole := c.Query("user_role")

		// Also check context if set by another middleware
		if userRole == "" {
			if role, exists := c.Get("user_role"); exists {
				userRole = role.(string)
			}
		}

		// If no role found, check if it's in the header
		if userRole == "" {
			userRole = c.GetHeader("X-User-Role")
		}

		// Check if user is admin
		if userRole != "admin" {
			c.JSON(http.StatusForbidden, gin.H{
				"error":   "Access denied",
				"message": "This operation requires administrator privileges",
			})
			c.Abort()
			return
		}

		c.Next()
	}
}

// OptionalAdminOnly allows the request but sets a flag for admin-only features
func OptionalAdminOnly() gin.HandlerFunc {
	return func(c *gin.Context) {
		userRole := c.Query("user_role")

		if userRole == "" {
			if role, exists := c.Get("user_role"); exists {
				userRole = role.(string)
			}
		}

		if userRole == "" {
			userRole = c.GetHeader("X-User-Role")
		}

		// Set admin flag in context
		c.Set("is_admin", userRole == "admin")
		c.Next()
	}
}
