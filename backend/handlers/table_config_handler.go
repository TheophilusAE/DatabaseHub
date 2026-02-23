package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

// DatabaseConfigHandler handles database connection management
type DatabaseConfigHandler struct {
	dbManager *config.MultiDatabaseManager
}

func NewDatabaseConfigHandler(dbManager *config.MultiDatabaseManager) *DatabaseConfigHandler {
	return &DatabaseConfigHandler{dbManager: dbManager}
}

// AddDatabaseConnection adds a new database connection
func (h *DatabaseConfigHandler) AddDatabaseConnection(c *gin.Context) {
	var conn config.DatabaseConnection
	if err := c.ShouldBindJSON(&conn); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if err := h.dbManager.AddConnection(&conn); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "Database connection added successfully",
		"name":    conn.Name,
	})
}

// ListDatabaseConnections lists all database connections
func (h *DatabaseConfigHandler) ListDatabaseConnections(c *gin.Context) {
	connections := h.dbManager.ListConnections()
	c.JSON(http.StatusOK, gin.H{
		"connections": connections,
		"count":       len(connections),
	})
}

// TestDatabaseConnection tests a database connection
func (h *DatabaseConfigHandler) TestDatabaseConnection(c *gin.Context) {
	name := c.Query("name")
	if name == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Connection name is required"})
		return
	}

	if err := h.dbManager.TestConnection(name); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"status": "failed",
			"error":  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"status":  "success",
		"message": "Database connection is alive",
	})
}

// RemoveDatabaseConnection removes a database connection
func (h *DatabaseConfigHandler) RemoveDatabaseConnection(c *gin.Context) {
	name := c.Query("name")
	if name == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Connection name is required"})
		return
	}

	if err := h.dbManager.RemoveConnection(name); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Database connection removed successfully",
	})
}

// TableConfigHandler handles table configuration management
type TableConfigHandler struct {
	tableConfigRepo *repository.TableConfigRepository
	tableJoinRepo   *repository.TableJoinRepository
	permRepo        *repository.UserTablePermissionRepository
}

func NewTableConfigHandler(
	tableConfigRepo *repository.TableConfigRepository,
	tableJoinRepo *repository.TableJoinRepository,
	permRepo *repository.UserTablePermissionRepository,
) *TableConfigHandler {
	return &TableConfigHandler{
		tableConfigRepo: tableConfigRepo,
		tableJoinRepo:   tableJoinRepo,
		permRepo:        permRepo,
	}
}

// CreateTableConfig creates a new table configuration
func (h *TableConfigHandler) CreateTableConfig(c *gin.Context) {
	var config models.TableConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	config.CreatedBy = c.GetString("user")

	if err := h.tableConfigRepo.Create(&config); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create table configuration"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "Table configuration created successfully",
		"config":  config,
	})
}

// ListTableConfigs lists all table configurations
// ListTableConfigs lists all table configurations (with permission filtering)
func (h *TableConfigHandler) ListTableConfigs(c *gin.Context) {
	// ✅ Get user info from auth middleware context (secure)
	currentUserID, exists := c.Get("user_id")
	currentUserRole, roleExists := c.Get("user_role")

	roleStr := ""
	if roleExists {
		if role, ok := currentUserRole.(string); ok {
			roleStr = role
		}
	}
	if roleStr == "" {
		roleStr = c.Query("user_role")
	}
	if roleStr == "" {
		roleStr = c.GetHeader("X-User-Role")
	}

	resolvedUserID := uint(0)
	if exists && currentUserID != nil {
		switch v := currentUserID.(type) {
		case uint:
			resolvedUserID = v
		case int:
			if v > 0 {
				resolvedUserID = uint(v)
			}
		case int64:
			if v > 0 {
				resolvedUserID = uint(v)
			}
		}
	}
	if resolvedUserID == 0 {
		userIDStr := c.Query("user_id")
		if userIDStr != "" {
			if uid, err := strconv.ParseUint(userIDStr, 10, 32); err == nil {
				resolvedUserID = uint(uid)
			}
		}
	}
	if resolvedUserID == 0 {
		headerUserID := c.GetHeader("X-User-ID")
		if headerUserID != "" {
			if uid, err := strconv.ParseUint(headerUserID, 10, 32); err == nil {
				resolvedUserID = uint(uid)
			}
		}
	}

	// ✅ Admin always sees ALL configs
	if roleStr == "admin" {
		configs, err := h.tableConfigRepo.FindAll()
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{
				"success": false,
				"error":   "Failed to retrieve table configurations",
			})
			return
		}
		c.JSON(http.StatusOK, gin.H{
			"success": true,
			"data":    configs,
			"count":   len(configs),
		})
		return
	}

	// ✅ For regular users: get ONLY their permitted tables
	if resolvedUserID == 0 {
		c.JSON(http.StatusUnauthorized, gin.H{
			"success": false,
			"error":   "Not authenticated",
		})
		return
	}

	// ✅ GetAccessibleTables returns []TableConfig directly
	accessibleTables, err := h.permRepo.GetAccessibleTables(resolvedUserID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"error":   "Failed to fetch permitted tables",
		})
		return
	}

	// ✅ Create map of permitted table IDs for fast lookup
	accessMap := make(map[uint]bool)
	for _, tableConfig := range accessibleTables {
		accessMap[tableConfig.ID] = true // ✅ Use tableConfig.ID (which is uint)
	}

	// ✅ Get ALL configs, then filter
	allConfigs, err := h.tableConfigRepo.FindAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"error":   "Failed to retrieve table configurations",
		})
		return
	}

	// ✅ Filter to only permitted configs
	filtered := make([]*models.TableConfig, 0)
	for _, config := range allConfigs {
		if accessMap[config.ID] {
			filtered = append(filtered, config)
		}
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    filtered,
		"count":   len(filtered),
	})
}

// GetTableConfig gets a specific table configuration
func (h *TableConfigHandler) GetTableConfig(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	config, err := h.tableConfigRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table configuration not found"})
		return
	}

	c.JSON(http.StatusOK, config)
}

// UpdateTableConfig updates a table configuration
func (h *TableConfigHandler) UpdateTableConfig(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	var config models.TableConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	config.ID = uint(id)

	if err := h.tableConfigRepo.Update(&config); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update table configuration"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Table configuration updated successfully",
		"config":  config,
	})
}

// DeleteTableConfig deletes a table configuration
func (h *TableConfigHandler) DeleteTableConfig(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	if err := h.tableConfigRepo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete table configuration"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Table configuration deleted successfully",
	})
}

// CreateTableJoin creates a new table join configuration
func (h *TableConfigHandler) CreateTableJoin(c *gin.Context) {
	var join models.TableJoin
	if err := c.ShouldBindJSON(&join); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	join.CreatedBy = c.GetString("user")

	if err := h.tableJoinRepo.Create(&join); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create table join"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "Table join created successfully",
		"join":    join,
	})
}

// ListTableJoins lists all table join configurations
func (h *TableConfigHandler) ListTableJoins(c *gin.Context) {
	joins, err := h.tableJoinRepo.FindAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to retrieve table joins"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"joins": joins,
		"count": len(joins),
	})
}

// GetTableJoin gets a specific table join configuration
func (h *TableConfigHandler) GetTableJoin(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	join, err := h.tableJoinRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table join not found"})
		return
	}

	c.JSON(http.StatusOK, join)
}

// UpdateTableJoin updates a table join configuration
func (h *TableConfigHandler) UpdateTableJoin(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	var join models.TableJoin
	if err := c.ShouldBindJSON(&join); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	join.ID = uint(id)

	if err := h.tableJoinRepo.Update(&join); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update table join"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Table join updated successfully",
		"join":    join,
	})
}

// DeleteTableJoin deletes a table join configuration
func (h *TableConfigHandler) DeleteTableJoin(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	if err := h.tableJoinRepo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete table join"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Table join deleted successfully",
	})
}
