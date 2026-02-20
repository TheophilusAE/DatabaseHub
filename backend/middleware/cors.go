// middleware/cors.go
package middleware

import (
	"strings"

	"github.com/gin-contrib/cors"
	"github.com/gin-gonic/gin"
)

func CORSMiddleware(allowedOrigins string) gin.HandlerFunc {
	origins := strings.Split(allowedOrigins, ",")

	// Clean origins - remove empty and whitespace
	cleanOrigins := []string{}
	for _, o := range origins {
		trimmed := strings.TrimSpace(o)
		if trimmed != "" {
			cleanOrigins = append(cleanOrigins, trimmed)
		}
	}

	config := cors.Config{
		AllowOrigins:     cleanOrigins,
		AllowMethods:     []string{"GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"},
		AllowHeaders:     []string{"Origin", "Content-Type", "Accept", "Authorization", "X-User-ID", "X-User-Role", "X-CSRF-TOKEN", "X-Requested-With"},
		ExposeHeaders:    []string{"Content-Length"},
		AllowCredentials: true,
		MaxAge:           12 * 3600,
	}

	return cors.New(config)
}
