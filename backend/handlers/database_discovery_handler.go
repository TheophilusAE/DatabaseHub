package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

// DatabaseDiscoveryHandler handles automatic database and table discovery
type DatabaseDiscoveryHandler struct {
	dbManager       *config.MultiDatabaseManager
	tableConfigRepo *repository.TableConfigRepository
}

func NewDatabaseDiscoveryHandler(
	dbManager *config.MultiDatabaseManager,
	tableConfigRepo *repository.TableConfigRepository,
) *DatabaseDiscoveryHandler {
	return &DatabaseDiscoveryHandler{
		dbManager:       dbManager,
		tableConfigRepo: tableConfigRepo,
	}
}

// DiscoveredTable represents a discovered table with its schema
type DiscoveredTable struct {
	TableName    string           `json:"table_name"`
	RowCount     int64            `json:"row_count"`
	Columns      []ColumnMetadata `json:"columns"`
	PrimaryKeys  []string         `json:"primary_keys"`
	IsConfigured bool             `json:"is_configured"`
}

// ColumnMetadata represents column information
type ColumnMetadata struct {
	Name         string `json:"name"`
	Type         string `json:"type"`
	Nullable     bool   `json:"nullable"`
	DefaultValue string `json:"default_value"`
	IsPrimary    bool   `json:"is_primary"`
	IsUnique     bool   `json:"is_unique"`
	Size         int    `json:"size"`
}

// ListDatabases returns all available database connections
func (h *DatabaseDiscoveryHandler) ListDatabases(c *gin.Context) {
	connections := h.dbManager.ListConnectionDetails()

	// Format response with database information
	var databases []map[string]interface{}
	for _, conn := range connections {
		databases = append(databases, map[string]interface{}{
			"name":    conn.Name,
			"type":    conn.Type,
			"host":    conn.Host,
			"port":    conn.Port,
			"db_name": conn.DBName,
		})
	}

	c.JSON(http.StatusOK, gin.H{
		"success":   true,
		"databases": databases,
		"count":     len(databases),
	})
}

// DiscoverTables discovers all tables in a specific database
func (h *DatabaseDiscoveryHandler) DiscoverTables(c *gin.Context) {
	databaseName := c.Query("database")
	if databaseName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Database name is required"})
		return
	}

	// Get database connection
	db, err := h.dbManager.GetConnection(databaseName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get database connection: " + err.Error()})
		return
	}

	// Get database type
	connInfo := h.dbManager.GetConnectionInfoSafe(databaseName)
	if connInfo == nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Database connection info not found"})
		return
	}

	// Discover tables based on database type
	var tables []DiscoveredTable
	var discoverErr error

	switch connInfo.Type {
	case "postgres":
		tables, discoverErr = h.discoverPostgresTables(db, databaseName)
	case "mysql":
		tables, discoverErr = h.discoverMySQLTables(db, databaseName)
	default:
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported database type: " + connInfo.Type})
		return
	}

	if discoverErr != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to discover tables: " + discoverErr.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success":  true,
		"database": databaseName,
		"tables":   tables,
		"count":    len(tables),
	})
}

// SyncTables automatically creates table configurations for discovered tables
func (h *DatabaseDiscoveryHandler) SyncTables(c *gin.Context) {
	var request struct {
		Database string   `json:"database" binding:"required"`
		Tables   []string `json:"tables"` // If empty, sync all tables
	}

	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// Get database connection
	db, err := h.dbManager.GetConnection(request.Database)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get database connection: " + err.Error()})
		return
	}

	// Get database type
	connInfo := h.dbManager.GetConnectionInfoSafe(request.Database)
	if connInfo == nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Database connection info not found"})
		return
	}

	// Discover tables
	var discoveredTables []DiscoveredTable
	var discoverErr error

	switch connInfo.Type {
	case "postgres":
		discoveredTables, discoverErr = h.discoverPostgresTables(db, request.Database)
	case "mysql":
		discoveredTables, discoverErr = h.discoverMySQLTables(db, request.Database)
	default:
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported database type: " + connInfo.Type})
		return
	}

	if discoverErr != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to discover tables: " + discoverErr.Error()})
		return
	}

	// Filter tables if specific tables are requested
	tablesToSync := discoveredTables
	if len(request.Tables) > 0 {
		tablesToSync = []DiscoveredTable{}
		for _, dt := range discoveredTables {
			for _, tableName := range request.Tables {
				if dt.TableName == tableName {
					tablesToSync = append(tablesToSync, dt)
					break
				}
			}
		}
	}

	// Get username for created_by
	username := c.GetString("user")
	if username == "" {
		username = "system"
	}

	// Create or update table configurations
	syncedTables := []string{}
	skippedTables := []string{}

	for _, table := range tablesToSync {
		// Convert columns to JSON
		columnsJSON, err := json.Marshal(table.Columns)
		if err != nil {
			skippedTables = append(skippedTables, table.TableName)
			continue
		}

		// Determine primary key
		primaryKey := "id"
		if len(table.PrimaryKeys) > 0 {
			primaryKey = table.PrimaryKeys[0]
		}

		// Check if configuration already exists
		existingConfig, err := h.tableConfigRepo.FindByDatabaseAndTable(request.Database, table.TableName)

		if err == nil && existingConfig.ID != 0 {
			// Update existing configuration
			existingConfig.Columns = string(columnsJSON)
			existingConfig.PrimaryKey = primaryKey
			existingConfig.IsActive = true

			if err := h.tableConfigRepo.Update(existingConfig); err != nil {
				skippedTables = append(skippedTables, table.TableName)
				continue
			}
		} else {
			// Create new configuration
			tableConfig := models.TableConfig{
				Name:         fmt.Sprintf("%s_%s", request.Database, table.TableName),
				DatabaseName: request.Database,
				Table:        table.TableName,
				Description:  fmt.Sprintf("Auto-synced from %s database", request.Database),
				Columns:      string(columnsJSON),
				PrimaryKey:   primaryKey,
				IsActive:     true,
				CreatedBy:    username,
			}

			if err := h.tableConfigRepo.Create(&tableConfig); err != nil {
				skippedTables = append(skippedTables, table.TableName)
				continue
			}
		}

		syncedTables = append(syncedTables, table.TableName)
	}

	c.JSON(http.StatusOK, gin.H{
		"success":        true,
		"database":       request.Database,
		"synced_tables":  syncedTables,
		"synced_count":   len(syncedTables),
		"skipped_tables": skippedTables,
		"skipped_count":  len(skippedTables),
	})
}

// discoverPostgresTables discovers tables in a PostgreSQL database
func (h *DatabaseDiscoveryHandler) discoverPostgresTables(db *gorm.DB, databaseName string) ([]DiscoveredTable, error) {
	var tables []DiscoveredTable

	// Get all table names
	query := `
		SELECT table_name 
		FROM information_schema.tables 
		WHERE table_schema = 'public' 
		AND table_type = 'BASE TABLE'
		AND table_name NOT IN ('users', 'table_configs', 'table_joins', 'import_mappings', 
								'export_configs', 'import_logs', 'documents', 'data_records',
								'user_table_permissions')
		ORDER BY table_name
	`

	rows, err := db.Raw(query).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			continue
		}

		// Get row count
		var rowCount int64
		db.Raw(fmt.Sprintf("SELECT COUNT(*) FROM %s", tableName)).Scan(&rowCount)

		// Get column information
		columns, primaryKeys := h.getPostgresColumns(db, tableName)

		// Check if already configured
		isConfigured := false
		existingConfig, err := h.tableConfigRepo.FindByDatabaseAndTable(databaseName, tableName)
		if err == nil && existingConfig.ID != 0 {
			isConfigured = true
		}

		tables = append(tables, DiscoveredTable{
			TableName:    tableName,
			RowCount:     rowCount,
			Columns:      columns,
			PrimaryKeys:  primaryKeys,
			IsConfigured: isConfigured,
		})
	}

	return tables, nil
}

// discoverMySQLTables discovers tables in a MySQL database
func (h *DatabaseDiscoveryHandler) discoverMySQLTables(db *gorm.DB, databaseName string) ([]DiscoveredTable, error) {
	var tables []DiscoveredTable

	// Get database name from connection
	connInfo := h.dbManager.GetConnectionInfoSafe(databaseName)
	if connInfo == nil {
		return nil, fmt.Errorf("database connection info not found")
	}

	// Get all table names
	query := `
		SELECT table_name 
		FROM information_schema.tables 
		WHERE table_schema = ?
		AND table_type = 'BASE TABLE'
		AND table_name NOT IN ('users', 'table_configs', 'table_joins', 'import_mappings', 
								'export_configs', 'import_logs', 'documents', 'data_records',
								'user_table_permissions')
		ORDER BY table_name
	`

	rows, err := db.Raw(query, connInfo.DBName).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			continue
		}

		// Get row count
		var rowCount int64
		db.Raw(fmt.Sprintf("SELECT COUNT(*) FROM %s", tableName)).Scan(&rowCount)

		// Get column information
		columns, primaryKeys := h.getMySQLColumns(db, connInfo.DBName, tableName)

		// Check if already configured
		isConfigured := false
		existingConfig, err := h.tableConfigRepo.FindByDatabaseAndTable(databaseName, tableName)
		if err == nil && existingConfig.ID != 0 {
			isConfigured = true
		}

		tables = append(tables, DiscoveredTable{
			TableName:    tableName,
			RowCount:     rowCount,
			Columns:      columns,
			PrimaryKeys:  primaryKeys,
			IsConfigured: isConfigured,
		})
	}

	return tables, nil
}

// getPostgresColumns gets column metadata for a PostgreSQL table
func (h *DatabaseDiscoveryHandler) getPostgresColumns(db *gorm.DB, tableName string) ([]ColumnMetadata, []string) {
	query := `
		SELECT 
			c.column_name,
			c.data_type,
			c.is_nullable,
			c.column_default,
			CASE WHEN pk.column_name IS NOT NULL THEN true ELSE false END as is_primary,
			c.character_maximum_length
		FROM information_schema.columns c
		LEFT JOIN (
			SELECT ku.column_name
			FROM information_schema.table_constraints tc
			JOIN information_schema.key_column_usage ku
				ON tc.constraint_name = ku.constraint_name
			WHERE tc.table_schema = 'public'
				AND tc.table_name = $1
				AND tc.constraint_type = 'PRIMARY KEY'
		) pk ON c.column_name = pk.column_name
		WHERE c.table_schema = 'public' AND c.table_name = $1
		ORDER BY c.ordinal_position
	`

	rows, err := db.Raw(query, tableName).Rows()
	if err != nil {
		return []ColumnMetadata{}, []string{}
	}
	defer rows.Close()

	var columns []ColumnMetadata
	var primaryKeys []string

	for rows.Next() {
		var col ColumnMetadata
		var nullable string
		var defaultValue *string
		var maxLength *int

		if err := rows.Scan(&col.Name, &col.Type, &nullable, &defaultValue, &col.IsPrimary, &maxLength); err != nil {
			continue
		}

		col.Nullable = (nullable == "YES")
		if defaultValue != nil {
			col.DefaultValue = *defaultValue
		}
		if maxLength != nil {
			col.Size = *maxLength
		}

		// Map PostgreSQL types to generic types
		col.Type = mapPostgresType(col.Type)

		columns = append(columns, col)

		if col.IsPrimary {
			primaryKeys = append(primaryKeys, col.Name)
		}
	}

	return columns, primaryKeys
}

// getMySQLColumns gets column metadata for a MySQL table
func (h *DatabaseDiscoveryHandler) getMySQLColumns(db *gorm.DB, schema, tableName string) ([]ColumnMetadata, []string) {
	query := `
		SELECT 
			c.column_name,
			c.data_type,
			c.is_nullable,
			c.column_default,
			c.column_key,
			c.character_maximum_length
		FROM information_schema.columns c
		WHERE c.table_schema = ? AND c.table_name = ?
		ORDER BY c.ordinal_position
	`

	rows, err := db.Raw(query, schema, tableName).Rows()
	if err != nil {
		return []ColumnMetadata{}, []string{}
	}
	defer rows.Close()

	var columns []ColumnMetadata
	var primaryKeys []string

	for rows.Next() {
		var col ColumnMetadata
		var nullable string
		var defaultValue *string
		var columnKey string
		var maxLength *int

		if err := rows.Scan(&col.Name, &col.Type, &nullable, &defaultValue, &columnKey, &maxLength); err != nil {
			continue
		}

		col.Nullable = (nullable == "YES")
		col.IsPrimary = (columnKey == "PRI")
		col.IsUnique = (columnKey == "UNI")

		if defaultValue != nil {
			col.DefaultValue = *defaultValue
		}
		if maxLength != nil {
			col.Size = *maxLength
		}

		// Map MySQL types to generic types
		col.Type = mapMySQLType(col.Type)

		columns = append(columns, col)

		if col.IsPrimary {
			primaryKeys = append(primaryKeys, col.Name)
		}
	}

	return columns, primaryKeys
}

// mapPostgresType maps PostgreSQL data types to generic types
func mapPostgresType(pgType string) string {
	typeMap := map[string]string{
		"character varying":           "varchar",
		"character":                   "char",
		"integer":                     "int",
		"bigint":                      "bigint",
		"smallint":                    "smallint",
		"double precision":            "float",
		"real":                        "float",
		"numeric":                     "decimal",
		"boolean":                     "bool",
		"timestamp without time zone": "datetime",
		"timestamp with time zone":    "datetime",
		"date":                        "date",
		"time":                        "time",
		"text":                        "text",
		"json":                        "json",
		"jsonb":                       "json",
		"uuid":                        "varchar",
		"bytea":                       "blob",
	}

	if mapped, ok := typeMap[strings.ToLower(pgType)]; ok {
		return mapped
	}
	return pgType
}

// mapMySQLType maps MySQL data types to generic types
func mapMySQLType(mysqlType string) string {
	typeMap := map[string]string{
		"tinyint":    "int",
		"smallint":   "int",
		"mediumint":  "int",
		"int":        "int",
		"bigint":     "bigint",
		"float":      "float",
		"double":     "float",
		"decimal":    "decimal",
		"varchar":    "varchar",
		"char":       "char",
		"text":       "text",
		"tinytext":   "text",
		"mediumtext": "text",
		"longtext":   "text",
		"datetime":   "datetime",
		"timestamp":  "datetime",
		"date":       "date",
		"time":       "time",
		"year":       "int",
		"blob":       "blob",
		"json":       "json",
	}

	if mapped, ok := typeMap[strings.ToLower(mysqlType)]; ok {
		return mapped
	}
	return mysqlType
}
