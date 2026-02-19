package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type MultiTableExportHandler struct {
	tableConfigRepo  *repository.TableConfigRepository
	tableJoinRepo    *repository.TableJoinRepository
	exportConfigRepo *repository.ExportConfigRepository
	dbManager        *config.MultiDatabaseManager
}

func NewMultiTableExportHandler(
	tableConfigRepo *repository.TableConfigRepository,
	tableJoinRepo *repository.TableJoinRepository,
	exportConfigRepo *repository.ExportConfigRepository,
	dbManager *config.MultiDatabaseManager,
) *MultiTableExportHandler {
	return &MultiTableExportHandler{
		tableConfigRepo:  tableConfigRepo,
		tableJoinRepo:    tableJoinRepo,
		exportConfigRepo: exportConfigRepo,
		dbManager:        dbManager,
	}
}

// ExportFromTable exports data from a configured table
func (h *MultiTableExportHandler) ExportFromTable(c *gin.Context) {
	configName := c.Query("config_name")
	if configName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "config_name is required"})
		return
	}

	// Get export configuration
	exportConfig, err := h.exportConfigRepo.FindByName(configName)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Export configuration not found"})
		return
	}

	// Process based on source type
	switch exportConfig.SourceType {
	case "table":
		h.exportFromSingleTable(c, exportConfig)
	case "join":
		h.exportFromJoin(c, exportConfig)
	default:
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported source type"})
	}
}

// exportFromSingleTable exports data from a single table
func (h *MultiTableExportHandler) exportFromSingleTable(c *gin.Context, exportConfig *models.ExportConfig) {
	// Get table configuration
	tableConfig, err := h.tableConfigRepo.FindByID(exportConfig.SourceID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table configuration not found"})
		return
	}

	// Get database connection
	db, err := h.dbManager.GetConnection(tableConfig.DatabaseName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": fmt.Sprintf("Database connection error: %v", err)})
		return
	}

	// Parse column list
	var columns []string
	if exportConfig.ColumnList != "" {
		if err := json.Unmarshal([]byte(exportConfig.ColumnList), &columns); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Invalid column list"})
			return
		}
	} else {
		columns = []string{"*"}
	}

	// Build query
	query := fmt.Sprintf("SELECT %s FROM %s", strings.Join(columns, ", "), tableConfig.Table)

	// Add filters if any
	if exportConfig.Filters != "" {
		var filters map[string]interface{}
		if err := json.Unmarshal([]byte(exportConfig.Filters), &filters); err == nil {
			whereClause := h.buildWhereClause(filters)
			if whereClause != "" {
				query += " WHERE " + whereClause
			}
		}
	}

	// Add ORDER BY if specified
	if exportConfig.OrderBy != "" {
		var orderBy []string
		if err := json.Unmarshal([]byte(exportConfig.OrderBy), &orderBy); err == nil && len(orderBy) > 0 {
			query += " ORDER BY " + strings.Join(orderBy, ", ")
		}
	}

	// Execute export based on target format
	switch exportConfig.TargetFormat {
	case "csv":
		h.exportToCSV(c, db, query, exportConfig.Name)
	case "json":
		h.exportToJSON(c, db, query)
	default:
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported target format"})
	}
}

// exportFromJoin exports data from joined tables
func (h *MultiTableExportHandler) exportFromJoin(c *gin.Context, exportConfig *models.ExportConfig) {
	// Get table join configuration
	tableJoin, err := h.tableJoinRepo.FindByID(exportConfig.SourceID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table join configuration not found"})
		return
	}

	// Get database connections (assuming both tables are in same database for now)
	db, err := h.dbManager.GetConnection(tableJoin.LeftTable.DatabaseName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": fmt.Sprintf("Database connection error: %v", err)})
		return
	}

	// Parse select columns
	var selectColumns []string
	if tableJoin.SelectColumns != "" {
		if err := json.Unmarshal([]byte(tableJoin.SelectColumns), &selectColumns); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Invalid select columns"})
			return
		}
	} else {
		selectColumns = []string{"*"}
	}

	// Build JOIN query
	query := fmt.Sprintf(
		"SELECT %s FROM %s AS left_table %s JOIN %s AS right_table ON %s",
		strings.Join(selectColumns, ", "),
		tableJoin.LeftTable.Table,
		tableJoin.JoinType,
		tableJoin.RightTable.Table,
		tableJoin.JoinCondition,
	)

	// Add filters if any
	if exportConfig.Filters != "" {
		var filters map[string]interface{}
		if err := json.Unmarshal([]byte(exportConfig.Filters), &filters); err == nil {
			whereClause := h.buildWhereClause(filters)
			if whereClause != "" {
				query += " WHERE " + whereClause
			}
		}
	}

	// Add ORDER BY if specified
	if exportConfig.OrderBy != "" {
		var orderBy []string
		if err := json.Unmarshal([]byte(exportConfig.OrderBy), &orderBy); err == nil && len(orderBy) > 0 {
			query += " ORDER BY " + strings.Join(orderBy, ", ")
		}
	}

	// Execute export
	switch exportConfig.TargetFormat {
	case "csv":
		h.exportToCSV(c, db, query, exportConfig.Name)
	case "json":
		h.exportToJSON(c, db, query)
	default:
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported target format"})
	}
}

// ExportJoinedDataToTable exports joined data and imports it into a target table
func (h *MultiTableExportHandler) ExportJoinedDataToTable(c *gin.Context) {
	joinName := c.Query("join_name")
	if joinName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "join_name is required"})
		return
	}

	// Get table join configuration
	tableJoin, err := h.tableJoinRepo.FindByName(joinName)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table join configuration not found"})
		return
	}

	if tableJoin.TargetTableID == nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No target table configured for this join"})
		return
	}

	// Get source database
	sourceDB, err := h.dbManager.GetConnection(tableJoin.LeftTable.DatabaseName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Source database connection error"})
		return
	}

	// Get target table configuration
	targetTable, err := h.tableConfigRepo.FindByID(*tableJoin.TargetTableID)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Target table configuration not found"})
		return
	}

	// Get target database
	targetDB, err := h.dbManager.GetConnection(targetTable.DatabaseName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Target database connection error"})
		return
	}

	// Parse select columns
	var selectColumns []string
	if tableJoin.SelectColumns != "" {
		if err := json.Unmarshal([]byte(tableJoin.SelectColumns), &selectColumns); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Invalid select columns"})
			return
		}
	} else {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Select columns must be specified for export to table"})
		return
	}

	// Build JOIN query
	query := fmt.Sprintf(
		"SELECT %s FROM %s AS left_table %s JOIN %s AS right_table ON %s",
		strings.Join(selectColumns, ", "),
		tableJoin.LeftTable.Table,
		tableJoin.JoinType,
		tableJoin.RightTable.Table,
		tableJoin.JoinCondition,
	)

	// Execute query and get results
	dynamicRepo := repository.NewDynamicTableRepository(sourceDB)
	results, err := dynamicRepo.SelectWithJoin(query)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": fmt.Sprintf("Failed to execute join query: %v", err)})
		return
	}

	// Insert results into target table
	if len(results) > 0 {
		targetRepo := repository.NewDynamicTableRepository(targetDB)
		batchSize := config.AppConfig.ImportBatchSize

		// Prepare column names from first result
		columns := make([]string, 0, len(results[0]))
		for key := range results[0] {
			columns = append(columns, key)
		}

		// Convert results to batch format
		totalRecords := 0
		for i := 0; i < len(results); i += batchSize {
			end := i + batchSize
			if end > len(results) {
				end = len(results)
			}

			batch := make([][]interface{}, 0, end-i)
			for j := i; j < end; j++ {
				values := make([]interface{}, len(columns))
				for k, col := range columns {
					values[k] = results[j][col]
				}
				batch = append(batch, values)
			}

			if err := targetRepo.InsertBatch(targetTable.Table, columns, batch); err != nil {
				c.JSON(http.StatusInternalServerError, gin.H{
					"error": fmt.Sprintf("Failed to insert batch at offset %d: %v", i, err),
				})
				return
			}

			totalRecords += len(batch)
		}

		c.JSON(http.StatusOK, gin.H{
			"message":         "Joined data exported to table successfully",
			"total_records":   len(results),
			"target_table":    targetTable.Table,
			"target_database": targetTable.DatabaseName,
		})
	} else {
		c.JSON(http.StatusOK, gin.H{
			"message": "No records found to export",
		})
	}
}

// exportToCSV exports query results to CSV
func (h *MultiTableExportHandler) exportToCSV(c *gin.Context, db *gorm.DB, query string, configName string) {
	filename := fmt.Sprintf("%s_%s.csv", configName, time.Now().Format("20060102_150405"))
	c.Header("Content-Type", "text/csv")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))
	c.Header("Transfer-Encoding", "chunked")

	writer := csv.NewWriter(c.Writer)
	defer writer.Flush()

	// Execute query in streaming mode
	rows, err := db.Raw(query).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Query execution failed"})
		return
	}
	defer rows.Close()

	// Get column names
	columns, err := rows.Columns()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get columns"})
		return
	}

	// Write header
	if err := writer.Write(columns); err != nil {
		return
	}

	// Write data rows
	values := make([]interface{}, len(columns))
	valuePtrs := make([]interface{}, len(columns))
	for i := range values {
		valuePtrs[i] = &values[i]
	}

	rowCount := 0
	for rows.Next() {
		if err := rows.Scan(valuePtrs...); err != nil {
			continue
		}

		row := make([]string, len(columns))
		for i, val := range values {
			if val != nil {
				row[i] = fmt.Sprintf("%v", val)
			} else {
				row[i] = ""
			}
		}

		if err := writer.Write(row); err != nil {
			continue
		}

		rowCount++
		if rowCount%10000 == 0 {
			writer.Flush()
			if f, ok := c.Writer.(http.Flusher); ok {
				f.Flush()
			}
		}
	}
}

// exportToJSON exports query results to JSON
func (h *MultiTableExportHandler) exportToJSON(c *gin.Context, db *gorm.DB, query string) {
	var results []map[string]interface{}
	if err := db.Raw(query).Scan(&results).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Query execution failed"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"data":  results,
		"count": len(results),
	})
}

// buildWhereClause builds a WHERE clause from filters
func (h *MultiTableExportHandler) buildWhereClause(filters map[string]interface{}) string {
	conditions := make([]string, 0)
	for key, value := range filters {
		switch v := value.(type) {
		case string:
			conditions = append(conditions, fmt.Sprintf("%s = '%s'", key, v))
		case float64, int:
			conditions = append(conditions, fmt.Sprintf("%s = %v", key, v))
		case map[string]interface{}:
			// Support operators like {"age": {"$gt": 18}}
			for op, opValue := range v {
				sqlOp := h.convertOperator(op)
				conditions = append(conditions, fmt.Sprintf("%s %s %v", key, sqlOp, opValue))
			}
		}
	}
	return strings.Join(conditions, " AND ")
}

// convertOperator converts MongoDB-style operators to SQL
func (h *MultiTableExportHandler) convertOperator(op string) string {
	switch op {
	case "$gt":
		return ">"
	case "$gte":
		return ">="
	case "$lt":
		return "<"
	case "$lte":
		return "<="
	case "$ne":
		return "!="
	default:
		return "="
	}
}

// ListExportConfigs lists all available export configurations
func (h *MultiTableExportHandler) ListExportConfigs(c *gin.Context) {
	configs, err := h.exportConfigRepo.FindAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to retrieve export configs"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"configs": configs,
		"count":   len(configs),
	})
}

// CreateExportConfig creates a new export configuration
func (h *MultiTableExportHandler) CreateExportConfig(c *gin.Context) {
	var config models.ExportConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	config.CreatedBy = c.GetString("user")

	if err := h.exportConfigRepo.Create(&config); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create export config"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "Export config created successfully",
		"config":  config,
	})
}

// UpdateExportConfig handles PUT /multi-export/configs/:id
func (h *MultiTableExportHandler) UpdateExportConfig(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseUint(idParam, 10, 64)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid config ID"})
		return
	}

	var input models.ExportConfig
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// Fetch existing config
	existing, err := h.exportConfigRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Export configuration not found"})
		return
	}

	// Update fields if provided
	if input.Name != "" {
		existing.Name = input.Name
	}
	if input.SourceType != "" {
		existing.SourceType = input.SourceType
	}
	if input.SourceID != 0 {
		existing.SourceID = input.SourceID
	}
	if input.TargetFormat != "" {
		existing.TargetFormat = input.TargetFormat
	}
	if input.Filters != "" {
		existing.Filters = input.Filters
	}
	if input.OrderBy != "" {
		existing.OrderBy = input.OrderBy
	}
	if input.ColumnList != "" {
		existing.ColumnList = input.ColumnList
	}

	existing.UpdatedAt = time.Now()

	if err := h.exportConfigRepo.Update(existing); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update export config"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Export config updated successfully",
		"config":  existing,
	})
}

// DeleteExportConfig handles DELETE /multi-export/configs/:id
func (h *MultiTableExportHandler) DeleteExportConfig(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseUint(idParam, 10, 64)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid config ID"})
		return
	}

	// Check if exists
	_, err = h.exportConfigRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Export configuration not found"})
		return
	}

	if err := h.exportConfigRepo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete export config"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Export configuration deleted successfully",
		"id":      id,
	})
}
