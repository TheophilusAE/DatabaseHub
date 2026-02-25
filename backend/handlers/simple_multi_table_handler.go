package handlers

import (
	"context"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"regexp"
	"sort"
	"strconv"
	"strings"
	"sync"
	"time"

	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"

	"github.com/gin-gonic/gin"
	"github.com/jackc/pgx/v5/pgxpool"
	"gorm.io/gorm"
)

var (
	postgresCopyPoolOnce sync.Once
	postgresCopyPool     *pgxpool.Pool
	postgresCopyPoolErr  error
)

type SimpleMultiTableHandler struct {
	DB              *gorm.DB
	PermRepo        *repository.UserTablePermissionRepository
	TableConfigRepo *repository.TableConfigRepository
}

func NewSimpleMultiTableHandler(db *gorm.DB, permRepo *repository.UserTablePermissionRepository, tableConfigRepo *repository.TableConfigRepository) *SimpleMultiTableHandler {
	return &SimpleMultiTableHandler{
		DB:              db,
		PermRepo:        permRepo,
		TableConfigRepo: tableConfigRepo,
	}
}

// TableInfo represents a table in the database
type TableInfo struct {
	TableName string `json:"table_name"`
	RowCount  int64  `json:"row_count"`
}

// ColumnInfo represents column metadata
type ColumnInfo struct {
	Name         string `json:"name"`
	Type         string `json:"type"`
	Nullable     string `json:"nullable"`
	IsPrimaryKey bool   `json:"is_primary_key"`
	HasDefault   bool   `json:"has_default"`
	IsIdentity   bool   `json:"is_identity"`
}

type CreateTableRowRequest struct {
	Data map[string]interface{} `json:"data"`
}

type UpdateTableRowRequest struct {
	PrimaryKeyColumn string                 `json:"primary_key_column"`
	PrimaryKeyValue  interface{}            `json:"primary_key_value"`
	Data             map[string]interface{} `json:"data"`
}

type DeleteTableRowRequest struct {
	PrimaryKeyColumn string      `json:"primary_key_column"`
	PrimaryKeyValue  interface{} `json:"primary_key_value"`
}

// ListTables returns all tables in the current database (with permission filtering)
func (h *SimpleMultiTableHandler) ListTables(c *gin.Context) {
	var tables []TableInfo

	//  Get user from context (set by AuthRequired middleware)
	userID := c.GetUint("user_id")
	userRole, _ := c.Get("user_role")

	// Fallback: check query params for testing/transition
	if userID == 0 {
		userIDStr := c.Query("user_id")
		if userIDStr != "" {
			if uid, err := strconv.ParseUint(userIDStr, 10, 32); err == nil {
				userID = uint(uid)
			}
		}
	}
	if userRole == nil || userRole == "" {
		userRole = c.Query("user_role")
	}

	// Final auth check
	if userID == 0 {
		c.JSON(http.StatusUnauthorized, gin.H{"error": "Missing user identity for table permission check"})
		return
	}

	roleStr, _ := userRole.(string)
	if roleStr == "" {
		roleStr = "user" // default role
	}
	isAdmin := roleStr == "admin"

	// Get all table names from information_schema
	query := `
		SELECT table_name 
		FROM information_schema.tables 
		WHERE table_schema = 'public' 
		AND table_type = 'BASE TABLE'
		ORDER BY table_name
	`

	rows, err := h.DB.Raw(query).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch tables: " + err.Error()})
		return
	}
	defer rows.Close()

	//  Get accessible table names for non-admin users
	accessibleTableNames := make(map[string]bool)

	if !isAdmin {
		accessibleTables, err := h.PermRepo.GetAccessibleTables(userID)
		if err == nil {
			for _, tableConfig := range accessibleTables {
				accessibleTableNames[tableConfig.Table] = true
			}
		}
	}

	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			continue
		}

		//  Filter tables if user is not admin
		if !isAdmin {
			if !accessibleTableNames[tableName] {
				continue // Skip tables user doesn't have access to
			}
		}

		// Get row count for each table
		var count int64
		h.DB.Raw(fmt.Sprintf("SELECT COUNT(*) FROM %s", tableName)).Scan(&count)

		tables = append(tables, TableInfo{
			TableName: tableName,
			RowCount:  count,
		})
	}

	c.JSON(http.StatusOK, gin.H{
		"tables": tables,
		"count":  len(tables),
	})
}

// GetTableColumns returns column information for a specific table
func (h *SimpleMultiTableHandler) GetTableColumns(c *gin.Context) {
	tableName := c.Param("table")

	if tableName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Table name is required"})
		return
	}

	columns, err := h.getTableColumnsMetadata(tableName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch columns: " + err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"table":   tableName,
		"columns": columns,
	})
}

// CreateTableRow inserts a new row into a selected table
// Admin: can create in any table
// User: can create only in tables they are allowed to access
func (h *SimpleMultiTableHandler) CreateTableRow(c *gin.Context) {
	tableName := c.Param("table")
	if !isValidIdentifier(tableName) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table name"})
		return
	}

	isAdmin := h.isAdminRequest(c)
	if !isAdmin {
		userID := c.GetUint("user_id")
		if userID == 0 {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "User not authenticated"})
			return
		}

		hasAccess, err := h.userCanAccessTable(userID, tableName)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to verify table access"})
			return
		}
		if !hasAccess {
			c.JSON(http.StatusForbidden, gin.H{"error": "Access denied for this table"})
			return
		}
	}

	var request CreateTableRowRequest
	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid request body"})
		return
	}

	if len(request.Data) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No data provided"})
		return
	}

	columns, err := h.getTableColumnsMetadata(tableName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to read table columns"})
		return
	}
	if len(columns) == 0 {
		c.JSON(http.StatusNotFound, gin.H{"error": "Table not found or has no columns"})
		return
	}

	columnMap := make(map[string]ColumnInfo)
	for _, col := range columns {
		columnMap[col.Name] = col
	}

	insertColumns := make([]string, 0)
	insertValues := make([]interface{}, 0)
	placeholders := make([]string, 0)

	for key, value := range request.Data {
		if !isValidIdentifier(key) {
			continue
		}

		if isAutoManagedTimestampColumn(key) {
			continue
		}

		column, exists := columnMap[key]
		if !exists {
			continue
		}

		if column.IsIdentity {
			continue
		}

		insertColumns = append(insertColumns, quoteIdentifier(key))
		insertValues = append(insertValues, value)
		placeholders = append(placeholders, "?")
	}

	now := time.Now()
	if column, exists := columnMap["created_at"]; exists && isTimestampType(column.Type) {
		insertColumns = append(insertColumns, quoteIdentifier("created_at"))
		insertValues = append(insertValues, now)
		placeholders = append(placeholders, "?")
	}
	if column, exists := columnMap["updated_at"]; exists && isTimestampType(column.Type) {
		insertColumns = append(insertColumns, quoteIdentifier("updated_at"))
		insertValues = append(insertValues, now)
		placeholders = append(placeholders, "?")
	}

	if len(insertColumns) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No editable columns provided"})
		return
	}

	query := fmt.Sprintf("INSERT INTO %s (%s) VALUES (%s)",
		quoteIdentifier(tableName),
		strings.Join(insertColumns, ", "),
		strings.Join(placeholders, ", "),
	)

	if err := h.DB.Exec(query, insertValues...).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create row: " + err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "Row created successfully"})
}

// UpdateTableRow updates an existing row in a selected table (admin only)
func (h *SimpleMultiTableHandler) UpdateTableRow(c *gin.Context) {
	if !h.isAdminRequest(c) {
		c.JSON(http.StatusForbidden, gin.H{"error": "Only admin can update table rows"})
		return
	}

	tableName := c.Param("table")
	if !isValidIdentifier(tableName) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table name"})
		return
	}

	var request UpdateTableRowRequest
	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid request body"})
		return
	}

	if len(request.Data) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No data provided for update"})
		return
	}

	pkColumns, err := h.getPrimaryKeyColumns(tableName)
	if err != nil || len(pkColumns) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Primary key not found for this table"})
		return
	}

	pkColumn := strings.TrimSpace(request.PrimaryKeyColumn)
	if pkColumn == "" {
		pkColumn = pkColumns[0]
	}

	if !isValidIdentifier(pkColumn) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid primary key column"})
		return
	}

	columns, err := h.getTableColumnsMetadata(tableName)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to read table columns"})
		return
	}

	columnMap := make(map[string]ColumnInfo)
	for _, col := range columns {
		columnMap[col.Name] = col
	}

	setParts := make([]string, 0)
	setValues := make([]interface{}, 0)

	for key, value := range request.Data {
		if key == pkColumn || !isValidIdentifier(key) {
			continue
		}

		if isAutoManagedTimestampColumn(key) {
			continue
		}

		column, exists := columnMap[key]
		if !exists || column.IsIdentity {
			continue
		}

		setParts = append(setParts, fmt.Sprintf("%s = ?", quoteIdentifier(key)))
		setValues = append(setValues, value)
	}

	if column, exists := columnMap["updated_at"]; exists && isTimestampType(column.Type) {
		setParts = append(setParts, fmt.Sprintf("%s = ?", quoteIdentifier("updated_at")))
		setValues = append(setValues, time.Now())
	}

	if len(setParts) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No editable columns provided for update"})
		return
	}

	setValues = append(setValues, request.PrimaryKeyValue)
	query := fmt.Sprintf("UPDATE %s SET %s WHERE %s = ?", quoteIdentifier(tableName), strings.Join(setParts, ", "), quoteIdentifier(pkColumn))

	result := h.DB.Exec(query, setValues...)
	if result.Error != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update row: " + result.Error.Error()})
		return
	}

	if result.RowsAffected == 0 {
		c.JSON(http.StatusNotFound, gin.H{"error": "Row not found"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "Row updated successfully"})
}

// DeleteTableRow deletes a row from a selected table (admin only)
func (h *SimpleMultiTableHandler) DeleteTableRow(c *gin.Context) {
	if !h.isAdminRequest(c) {
		c.JSON(http.StatusForbidden, gin.H{"error": "Only admin can delete table rows"})
		return
	}

	tableName := c.Param("table")
	if !isValidIdentifier(tableName) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid table name"})
		return
	}

	var request DeleteTableRowRequest
	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid request body"})
		return
	}

	pkColumns, err := h.getPrimaryKeyColumns(tableName)
	if err != nil || len(pkColumns) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Primary key not found for this table"})
		return
	}

	pkColumn := strings.TrimSpace(request.PrimaryKeyColumn)
	if pkColumn == "" {
		pkColumn = pkColumns[0]
	}

	if !isValidIdentifier(pkColumn) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid primary key column"})
		return
	}

	query := fmt.Sprintf("DELETE FROM %s WHERE %s = ?", quoteIdentifier(tableName), quoteIdentifier(pkColumn))
	result := h.DB.Exec(query, request.PrimaryKeyValue)
	if result.Error != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete row: " + result.Error.Error()})
		return
	}

	if result.RowsAffected == 0 {
		c.JSON(http.StatusNotFound, gin.H{"error": "Row not found"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "Row deleted successfully"})
}

// GetTableData returns paginated data from a specific table
func (h *SimpleMultiTableHandler) GetTableData(c *gin.Context) {
	tableName := c.Param("table")
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	pageSize, _ := strconv.Atoi(c.DefaultQuery("page_size", "50"))

	//  Get user from context (set by AuthRequired middleware)
	userID := c.GetUint("user_id")
	userRole, _ := c.Get("user_role")

	// Fallback to query params
	if userID == 0 {
		userIDStr := c.Query("user_id")
		if userIDStr != "" {
			if parsedID, err := strconv.ParseUint(userIDStr, 10, 32); err == nil {
				userID = uint(parsedID)
			}
		}
	}
	if userRole == nil || userRole == "" {
		userRole = c.Query("user_role")
	}

	roleStr, _ := userRole.(string)
	if roleStr == "" {
		roleStr = "user"
	}
	roleStr = strings.ToLower(roleStr)
	isAdmin := roleStr == "admin"

	// Permission check for non-admins
	if !isAdmin {
		if userID == 0 {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "Missing user identity for table access"})
			return
		}

		accessibleTables, err := h.PermRepo.GetAccessibleTables(userID)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to verify table permission"})
			return
		}

		hasAccess := false
		for _, tableConfig := range accessibleTables {
			if tableConfig.Table == tableName {
				hasAccess = true
				break
			}
		}

		if !hasAccess {
			c.JSON(http.StatusForbidden, gin.H{"error": "Access denied for this table"})
			return
		}
	}

	// Validate table name
	if tableName == "" || tableName == "undefined" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Table name is required"})
		return
	}

	// Check if table exists
	var exists bool
	query := `
		SELECT EXISTS (
			SELECT 1 FROM information_schema.tables 
			WHERE table_schema = 'public' AND table_name = ?
		)
	`
	if err := h.DB.Raw(query, tableName).Scan(&exists).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to validate table: " + err.Error()})
		return
	}

	if !exists {
		c.JSON(http.StatusNotFound, gin.H{"error": fmt.Sprintf("Table '%s' does not exist", tableName)})
		return
	}

	if page < 1 {
		page = 1
	}
	if pageSize < 1 || pageSize > 1000 {
		pageSize = 50
	}

	offset := (page - 1) * pageSize

	// Get total count
	var totalCount int64
	h.DB.Raw(fmt.Sprintf("SELECT COUNT(*) FROM %s", tableName)).Scan(&totalCount)

	// Get paginated data
	rows, err := h.DB.Raw(fmt.Sprintf("SELECT * FROM %s LIMIT ? OFFSET ?", tableName), pageSize, offset).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch data: " + err.Error()})
		return
	}
	defer rows.Close()

	// Get column names
	columns, err := rows.Columns()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get columns: " + err.Error()})
		return
	}

	// Prepare data container
	var data []map[string]interface{}

	for rows.Next() {
		// Create a slice of interface{}'s to represent each column
		columnValues := make([]interface{}, len(columns))
		columnPointers := make([]interface{}, len(columns))
		for i := range columnValues {
			columnPointers[i] = &columnValues[i]
		}

		// Scan the result into the column pointers
		if err := rows.Scan(columnPointers...); err != nil {
			continue
		}

		// Create a map for this row
		row := make(map[string]interface{})
		for i, colName := range columns {
			val := columnValues[i]
			// Convert byte arrays to strings
			if b, ok := val.([]byte); ok {
				row[colName] = string(b)
			} else {
				row[colName] = val
			}
		}

		data = append(data, row)
	}

	c.JSON(http.StatusOK, gin.H{
		"table":       tableName,
		"data":        data,
		"page":        page,
		"page_size":   pageSize,
		"total_count": totalCount,
		"total_pages": (totalCount + int64(pageSize) - 1) / int64(pageSize),
	})
}

// UploadToMultipleTables handles uploading data to multiple tables at once
func (h *SimpleMultiTableHandler) UploadToMultipleTables(c *gin.Context) {
	truncateBeforeImport := strings.EqualFold(strings.TrimSpace(c.Query("truncate_before_import")), "true")
	if truncateBeforeImport && !h.isAdminRequest(c) {
		c.JSON(http.StatusForbidden, gin.H{"error": "Only admin can truncate tables before import"})
		return
	}

	// Parse multipart form
	form, err := c.MultipartForm()
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Failed to parse form: " + err.Error()})
		return
	}

	files := form.File["files"]
	tableNames := form.Value["table_names"]

	if len(files) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No files provided"})
		return
	}

	if len(tableNames) != len(files) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Number of table names must match number of files"})
		return
	}

	//  Get authenticated user from context
	userID := c.GetUint("user_id")
	if userID == 0 {
		c.JSON(http.StatusUnauthorized, gin.H{"error": "User not authenticated"})
		return
	}

	results := []map[string]interface{}{}
	totalSuccess := 0
	totalFailed := 0

	// Process each file
	for i, fileHeader := range files {
		tableName := tableNames[i]

		if !isValidIdentifier(tableName) {
			results = append(results, map[string]interface{}{
				"table":   tableName,
				"status":  "error",
				"message": "Invalid table name",
			})
			continue
		}

		// Determine file format
		format := "csv"
		if strings.HasSuffix(strings.ToLower(fileHeader.Filename), ".json") {
			format = "json"
		}

		isPostgresCSV := config.AppConfig != nil && strings.EqualFold(config.AppConfig.DBType, "postgres") && format == "csv"

		if truncateBeforeImport && !isPostgresCSV {
			truncateQuery := fmt.Sprintf("TRUNCATE TABLE %s", quoteIdentifier(tableName))
			if config.AppConfig != nil && strings.EqualFold(config.AppConfig.DBType, "postgres") {
				truncateQuery += " RESTART IDENTITY"
			}

			if truncateErr := h.DB.Exec(truncateQuery).Error; truncateErr != nil {
				results = append(results, map[string]interface{}{
					"table":   tableName,
					"status":  "error",
					"message": "Failed to truncate table before import: " + truncateErr.Error(),
				})
				continue
			}
		}

		file, err := fileHeader.Open()
		if err != nil {
			results = append(results, map[string]interface{}{
				"table":   tableName,
				"status":  "error",
				"message": "Failed to open file: " + err.Error(),
			})
			continue
		}

		// Import data
		successCount, failureCount, importErr := h.importToTable(file, tableName, format, truncateBeforeImport)
		file.Close()

		// Log the import
		importLog := models.ImportLog{
			FileName:     fileHeader.Filename,
			ImportType:   format,
			SuccessCount: successCount,
			FailureCount: failureCount,
			TotalRecords: successCount + failureCount,
			ImportedBy:   fmt.Sprintf("%d", userID),
		}
		if importErr != nil {
			importLog.Status = "failed"
			importLog.ErrorMessage = importErr.Error()
		} else {
			importLog.Status = "completed"
		}
		h.DB.Create(&importLog)

		totalSuccess += successCount
		totalFailed += failureCount

		results = append(results, map[string]interface{}{
			"table":         tableName,
			"filename":      fileHeader.Filename,
			"status":        importLog.Status,
			"success_count": successCount,
			"failure_count": failureCount,
			"error":         importLog.ErrorMessage,
		})
	}

	c.JSON(http.StatusOK, gin.H{
		"message":       "Multi-table upload completed",
		"results":       results,
		"total_success": totalSuccess,
		"total_failed":  totalFailed,
	})
}

// importToTable handles importing data to a specific table
func (h *SimpleMultiTableHandler) importToTable(file io.Reader, tableName string, format string, truncateBeforeImport bool) (int, int, error) {
	if format == "csv" {
		return h.importCSVToTable(file, tableName, truncateBeforeImport)
	} else if format == "json" {
		return h.importJSONToTable(file, tableName)
	}

	return 0, 0, fmt.Errorf("unsupported format: %s", format)
}

// importCSVToTable imports CSV data to a table
func (h *SimpleMultiTableHandler) importCSVToTable(file io.Reader, tableName string, truncateBeforeImport bool) (int, int, error) {
	if !isValidIdentifier(tableName) {
		return 0, 0, fmt.Errorf("invalid table name")
	}

	if config.AppConfig != nil && strings.EqualFold(config.AppConfig.DBType, "postgres") {
		if readSeeker, ok := file.(io.ReadSeeker); ok {
			return h.importCSVToTablePostgresCopyStream(readSeeker, tableName, truncateBeforeImport)
		}
	}

	return h.importCSVToTableBatched(file, tableName)
}

func (h *SimpleMultiTableHandler) importCSVToTableBatched(file io.Reader, tableName string) (int, int, error) {
	if !isValidIdentifier(tableName) {
		return 0, 0, fmt.Errorf("invalid table name")
	}

	reader := csv.NewReader(file)
	reader.LazyQuotes = true
	reader.TrimLeadingSpace = true

	// Read header
	headers, err := reader.Read()
	if err != nil {
		return 0, 0, fmt.Errorf("failed to read CSV headers: %w", err)
	}

	columns := make([]string, 0, len(headers))
	columnIndexes := make([]int, 0, len(headers))
	for i, header := range headers {
		column := strings.TrimSpace(header)
		if column == "" || !isValidIdentifier(column) {
			continue
		}
		columns = append(columns, column)
		columnIndexes = append(columnIndexes, i)
	}

	if len(columns) == 0 {
		return 0, 0, fmt.Errorf("no valid CSV columns found")
	}

	successCount := 0
	failureCount := 0
	batchSize := getOptimizedImportBatchSize()
	rowsBatch := make([][]interface{}, 0, batchSize)

	flushBatch := func() {
		if len(rowsBatch) == 0 {
			return
		}
		success, failed := h.insertRowsBatch(tableName, columns, rowsBatch)
		successCount += success
		failureCount += failed
		rowsBatch = rowsBatch[:0]
	}

	// Read and insert rows in batches
	for {
		record, err := reader.Read()
		if err == io.EOF {
			break
		}
		if err != nil {
			failureCount++
			continue
		}

		values := make([]interface{}, len(columns))
		for i, idx := range columnIndexes {
			if idx < len(record) {
				cell := strings.TrimSpace(record[idx])
				if cell == "" {
					values[i] = nil
				} else {
					values[i] = cell
				}
			} else {
				values[i] = nil
			}
		}

		rowsBatch = append(rowsBatch, values)
		if len(rowsBatch) >= batchSize {
			flushBatch()
		}
	}

	flushBatch()

	return successCount, failureCount, nil
}

func (h *SimpleMultiTableHandler) importCSVToTablePostgresCopyStream(file io.ReadSeeker, tableName string, truncateBeforeImport bool) (int, int, error) {
	if _, err := file.Seek(0, io.SeekStart); err != nil {
		return 0, 0, fmt.Errorf("failed to seek CSV stream: %w", err)
	}

	reader := csv.NewReader(file)
	reader.LazyQuotes = true
	reader.TrimLeadingSpace = true

	headers, err := reader.Read()
	if err != nil {
		return 0, 0, fmt.Errorf("failed to read CSV headers: %w", err)
	}

	columns := make([]string, 0, len(headers))
	for _, header := range headers {
		column := strings.TrimSpace(header)
		if column == "" || !isValidIdentifier(column) {
			return 0, 0, fmt.Errorf("invalid CSV column name: %s", header)
		}
		columns = append(columns, column)
	}

	if len(columns) == 0 {
		return 0, 0, fmt.Errorf("no valid CSV columns found")
	}

	if _, err := file.Seek(0, io.SeekStart); err != nil {
		return 0, 0, fmt.Errorf("failed to reset CSV stream for COPY: %w", err)
	}

	pool, err := getPostgresCopyPool()
	if err != nil {
		if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
			return h.importCSVToTableBatched(file, tableName)
		}
		return 0, 0, err
	}

	ctx := context.Background()
	conn, err := pool.Acquire(ctx)
	if err != nil {
		if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
			return h.importCSVToTableBatched(file, tableName)
		}
		return 0, 0, fmt.Errorf("failed to acquire postgres copy connection: %w", err)
	}
	defer conn.Release()

	tx, err := conn.Conn().Begin(ctx)
	if err != nil {
		if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
			return h.importCSVToTableBatched(file, tableName)
		}
		return 0, 0, fmt.Errorf("failed to begin postgres copy transaction: %w", err)
	}

	rollback := true
	defer func() {
		if rollback {
			_ = tx.Rollback(ctx)
		}
	}()

	_, _ = tx.Exec(ctx, "SET LOCAL synchronous_commit = OFF")

	if truncateBeforeImport {
		truncateSQL := fmt.Sprintf("TRUNCATE TABLE %s RESTART IDENTITY", quoteIdentifier(tableName))
		if _, truncateErr := tx.Exec(ctx, truncateSQL); truncateErr != nil {
			if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
				return h.importCSVToTableBatched(file, tableName)
			}
			return 0, 0, fmt.Errorf("failed to truncate table before copy: %w", truncateErr)
		}
	}

	quotedColumns := make([]string, len(columns))
	for i, col := range columns {
		quotedColumns[i] = quoteIdentifier(col)
	}

copyOptions := "FORMAT csv, HEADER true"
	if truncateBeforeImport {
		copyOptions += ", FREEZE true"
	}

	copySQL := fmt.Sprintf(
		"COPY %s (%s) FROM STDIN WITH (%s)",
		quoteIdentifier(tableName),
		strings.Join(quotedColumns, ", "),
		copyOptions,
	)

	commandTag, copyErr := tx.Conn().PgConn().CopyFrom(ctx, file, copySQL)
	if copyErr != nil {
		_ = tx.Rollback(ctx)
		rollback = false
		if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
			return h.importCSVToTableBatched(file, tableName)
		}
		return 0, 0, fmt.Errorf("postgres COPY failed: %w", copyErr)
	}

	if commitErr := tx.Commit(ctx); commitErr != nil {
		rollback = false
		if _, seekErr := file.Seek(0, io.SeekStart); seekErr == nil {
			return h.importCSVToTableBatched(file, tableName)
		}
		return 0, 0, fmt.Errorf("failed to commit copy transaction: %w", commitErr)
	}

	rollback = false

	return int(commandTag.RowsAffected()), 0, nil
}

// importJSONToTable imports JSON data to a table
func (h *SimpleMultiTableHandler) importJSONToTable(file io.Reader, tableName string) (int, int, error) {
	if !isValidIdentifier(tableName) {
		return 0, 0, fmt.Errorf("invalid table name")
	}

	var data []map[string]interface{}

	decoder := json.NewDecoder(file)
	if err := decoder.Decode(&data); err != nil {
		return 0, 0, fmt.Errorf("failed to decode JSON: %w", err)
	}

	successCount := 0
	failureCount := 0
	batches := make(map[string][][]interface{})
	batchColumns := make(map[string][]string)
	batchSize := getOptimizedImportBatchSize()

	flushBatchKey := func(key string) {
		rows := batches[key]
		if len(rows) == 0 {
			return
		}
		success, failed := h.insertRowsBatch(tableName, batchColumns[key], rows)
		successCount += success
		failureCount += failed
		batches[key] = rows[:0]
	}

	for _, rowData := range data {
		if len(rowData) == 0 {
			failureCount++
			continue
		}

		columns := make([]string, 0, len(rowData))
		for col := range rowData {
			if isValidIdentifier(col) {
				columns = append(columns, col)
			}
		}
		if len(columns) == 0 {
			failureCount++
			continue
		}
		sort.Strings(columns)

		key := strings.Join(columns, "|")
		if _, exists := batchColumns[key]; !exists {
			batchColumns[key] = columns
			batches[key] = make([][]interface{}, 0, batchSize)
		}

		values := make([]interface{}, len(columns))
		for i, col := range columns {
			if value, ok := rowData[col]; ok {
				values[i] = value
			}
		}

		batches[key] = append(batches[key], values)
		if len(batches[key]) >= batchSize {
			flushBatchKey(key)
		}
	}

	for key := range batches {
		flushBatchKey(key)
	}

	return successCount, failureCount, nil
}

func getOptimizedImportBatchSize() int {
	defaultSize := 1000
	if config.AppConfig == nil || config.AppConfig.ImportBatchSize <= 0 {
		return defaultSize
	}

	size := config.AppConfig.ImportBatchSize
	if size < 100 {
		return 100
	}
	if size > 2000 {
		return 2000
	}

	return size
}

func getPostgresCopyPool() (*pgxpool.Pool, error) {
	postgresCopyPoolOnce.Do(func() {
		if config.AppConfig == nil {
			postgresCopyPoolErr = fmt.Errorf("application config not initialized")
			return
		}
		if !strings.EqualFold(config.AppConfig.DBType, "postgres") {
			postgresCopyPoolErr = fmt.Errorf("postgres copy is only available for postgres")
			return
		}

		pool, err := pgxpool.New(context.Background(), config.AppConfig.GetDSN())
		if err != nil {
			postgresCopyPoolErr = fmt.Errorf("failed to create postgres copy pool: %w", err)
			return
		}

		postgresCopyPool = pool
	})

	if postgresCopyPoolErr != nil {
		return nil, postgresCopyPoolErr
	}

	if postgresCopyPool == nil {
		return nil, fmt.Errorf("postgres copy pool is not initialized")
	}

	return postgresCopyPool, nil
}

func (h *SimpleMultiTableHandler) insertRowsBatch(tableName string, columns []string, rows [][]interface{}) (int, int) {
	if len(rows) == 0 || len(columns) == 0 {
		return 0, 0
	}

	quotedColumns := make([]string, len(columns))
	for i, col := range columns {
		quotedColumns[i] = quoteIdentifier(col)
	}

	singleRowPlaceholder := "(" + strings.TrimRight(strings.Repeat("?,", len(columns)), ",") + ")"
	placeholders := make([]string, len(rows))
	args := make([]interface{}, 0, len(rows)*len(columns))

	for i, row := range rows {
		placeholders[i] = singleRowPlaceholder
		args = append(args, row...)
	}

	query := fmt.Sprintf(
		"INSERT INTO %s (%s) VALUES %s",
		quoteIdentifier(tableName),
		strings.Join(quotedColumns, ", "),
		strings.Join(placeholders, ","),
	)

	if err := h.DB.Exec(query, args...).Error; err == nil {
		return len(rows), 0
	}

	fallbackQuery := fmt.Sprintf(
		"INSERT INTO %s (%s) VALUES %s",
		quoteIdentifier(tableName),
		strings.Join(quotedColumns, ", "),
		singleRowPlaceholder,
	)

	successCount := 0
	failureCount := 0
	for _, row := range rows {
		if err := h.DB.Exec(fallbackQuery, row...).Error; err != nil {
			failureCount++
		} else {
			successCount++
		}
	}

	return successCount, failureCount
}

// ExportSelectedDataRequest represents the request for selective export
type ExportSelectedDataRequest struct {
	Tables []struct {
		TableName string   `json:"table_name"`
		Columns   []string `json:"columns"` // empty means all columns
		Filters   string   `json:"filters"` // SQL WHERE clause
	} `json:"tables"`
	Format string `json:"format"` // csv or json
}

type tableRelation struct {
	FKTable   string
	FKColumn  string
	RefTable  string
	RefColumn string
}

// ExportSelectedData exports data from selected tables with filters
func (h *SimpleMultiTableHandler) ExportSelectedData(c *gin.Context) {
	userID := c.GetUint("user_id")
	if userID == 0 {
		c.JSON(http.StatusUnauthorized, gin.H{"error": "User not authenticated"})
		return
	}

	var request ExportSelectedDataRequest
	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid request: " + err.Error()})
		return
	}

	if len(request.Tables) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No tables selected"})
		return
	}

	// Default to CSV
	if request.Format == "" {
		request.Format = "csv"
	}

	identifierPattern := regexp.MustCompile(`^[a-zA-Z_][a-zA-Z0-9_]*$`)

	aliases := make([]string, len(request.Tables))
	selectedColumnsPerTable := make([][]string, len(request.Tables))
	outputHeaders := []string{}
	columnNameCounts := map[string]int{}
	selectExpressions := []string{}

	for tableIndex, tableReq := range request.Tables {
		if !identifierPattern.MatchString(tableReq.TableName) {
			c.JSON(http.StatusBadRequest, gin.H{"error": fmt.Sprintf("Invalid table name: %s", tableReq.TableName)})
			return
		}

		aliases[tableIndex] = fmt.Sprintf("t%d", tableIndex)

		columns := tableReq.Columns
		if len(columns) == 0 {
			discoveredColumns, err := h.getTableColumnNames(tableReq.TableName)
			if err != nil {
				c.JSON(http.StatusInternalServerError, gin.H{"error": fmt.Sprintf("Failed to read columns for %s: %v", tableReq.TableName, err)})
				return
			}
			columns = discoveredColumns
		}

		validatedColumns := make([]string, 0, len(columns))
		for _, col := range columns {
			if !identifierPattern.MatchString(col) {
				c.JSON(http.StatusBadRequest, gin.H{"error": fmt.Sprintf("Invalid column name '%s' in table %s", col, tableReq.TableName)})
				return
			}
			validatedColumns = append(validatedColumns, col)
		}
		selectedColumnsPerTable[tableIndex] = validatedColumns

		for _, col := range validatedColumns {
			headerName := col
			if existingCount, exists := columnNameCounts[col]; exists {
				headerName = fmt.Sprintf("%s_%s", tableReq.TableName, col)
				columnNameCounts[col] = existingCount + 1
			} else {
				columnNameCounts[col] = 1
			}

			outputHeaders = append(outputHeaders, headerName)
			selectExpressions = append(selectExpressions,
				fmt.Sprintf("%s.%s AS %s", aliases[tableIndex], quoteIdentifier(col), quoteIdentifier(headerName)),
			)
		}
	}

	if len(selectExpressions) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No columns selected for export"})
		return
	}

	queryBuilder := strings.Builder{}
	queryBuilder.WriteString("SELECT ")
	queryBuilder.WriteString(strings.Join(selectExpressions, ", "))
	queryBuilder.WriteString(" FROM ")
	queryBuilder.WriteString(fmt.Sprintf("%s %s", quoteIdentifier(request.Tables[0].TableName), aliases[0]))

	for i := 1; i < len(request.Tables); i++ {
		relation, err := h.findTableRelation(request.Tables[i-1].TableName, request.Tables[i].TableName)
		if err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"error": fmt.Sprintf("No direct relationship found between %s and %s. Configure foreign keys or select related tables.", request.Tables[i-1].TableName, request.Tables[i].TableName),
			})
			return
		}

		prevAlias := aliases[i-1]
		currAlias := aliases[i]

		joinCondition := ""
		if relation.FKTable == request.Tables[i-1].TableName && relation.RefTable == request.Tables[i].TableName {
			joinCondition = fmt.Sprintf("%s.%s = %s.%s", prevAlias, quoteIdentifier(relation.FKColumn), currAlias, quoteIdentifier(relation.RefColumn))
		} else {
			joinCondition = fmt.Sprintf("%s.%s = %s.%s", currAlias, quoteIdentifier(relation.FKColumn), prevAlias, quoteIdentifier(relation.RefColumn))
		}

		queryBuilder.WriteString(fmt.Sprintf(" INNER JOIN %s %s ON %s", quoteIdentifier(request.Tables[i].TableName), currAlias, joinCondition))
	}

	whereClauses := []string{}
	for i, tableReq := range request.Tables {
		if strings.TrimSpace(tableReq.Filters) == "" {
			continue
		}
		whereClauses = append(whereClauses, qualifyFilterWithAlias(tableReq.Filters, aliases[i], selectedColumnsPerTable[i]))
	}

	if len(whereClauses) > 0 {
		queryBuilder.WriteString(" WHERE ")
		queryBuilder.WriteString(strings.Join(whereClauses, " AND "))
	}

	rows, err := h.DB.Raw(queryBuilder.String()).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to export related data: " + err.Error()})
		return
	}
	defer rows.Close()

	columnNames, err := rows.Columns()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to read export columns: " + err.Error()})
		return
	}

	jsonRows := make([]map[string]interface{}, 0)
	csvRows := make([][]string, 0)

	for rows.Next() {
		columnValues := make([]interface{}, len(columnNames))
		columnPointers := make([]interface{}, len(columnNames))
		for i := range columnValues {
			columnPointers[i] = &columnValues[i]
		}

		if err := rows.Scan(columnPointers...); err != nil {
			continue
		}

		jsonRow := make(map[string]interface{}, len(columnNames))
		csvRow := make([]string, len(columnNames))
		for i, colName := range columnNames {
			val := columnValues[i]
			if b, ok := val.([]byte); ok {
				jsonRow[colName] = string(b)
				csvRow[i] = string(b)
			} else if val == nil {
				jsonRow[colName] = nil
				csvRow[i] = ""
			} else {
				jsonRow[colName] = val
				csvRow[i] = fmt.Sprintf("%v", val)
			}
		}

		jsonRows = append(jsonRows, jsonRow)
		csvRows = append(csvRows, csvRow)
	}

	// Export based on format
	if request.Format == "json" {
		c.Header("Content-Type", "application/json")
		c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=export_%s.json", time.Now().Format("20060102_150405")))
		c.JSON(http.StatusOK, gin.H{
			"rows":    jsonRows,
			"columns": columnNames,
			"count":   len(jsonRows),
		})
	} else {
		// CSV export - relationship-processed flat output
		c.Header("Content-Type", "text/csv")
		c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=export_%s.csv", time.Now().Format("20060102_150405")))

		writer := csv.NewWriter(c.Writer)
		defer writer.Flush()

		writer.Write(columnNames)
		for _, row := range csvRows {
			writer.Write(row)
		}
	}
}

func quoteIdentifier(identifier string) string {
	return `"` + identifier + `"`
}

func isValidIdentifier(identifier string) bool {
	pattern := regexp.MustCompile(`^[a-zA-Z_][a-zA-Z0-9_]*$`)
	return pattern.MatchString(identifier)
}

func isAutoManagedTimestampColumn(columnName string) bool {
	name := strings.ToLower(strings.TrimSpace(columnName))
	return name == "created_at" || name == "updated_at"
}

func isTimestampType(dataType string) bool {
	typeName := strings.ToLower(strings.TrimSpace(dataType))
	return strings.Contains(typeName, "timestamp")
}

func (h *SimpleMultiTableHandler) isAdminRequest(c *gin.Context) bool {
	role := strings.ToLower(strings.TrimSpace(c.GetString("user_role")))
	if role == "" {
		role = strings.ToLower(strings.TrimSpace(c.GetHeader("X-User-Role")))
	}
	if role == "" {
		role = strings.ToLower(strings.TrimSpace(c.Query("user_role")))
	}
	return role == "admin"
}

func (h *SimpleMultiTableHandler) userCanAccessTable(userID uint, tableName string) (bool, error) {
	accessibleTables, err := h.PermRepo.GetAccessibleTables(userID)
	if err != nil {
		return false, err
	}

	for _, tableConfig := range accessibleTables {
		if strings.EqualFold(tableConfig.Table, tableName) {
			return true, nil
		}
	}

	return false, nil
}

func (h *SimpleMultiTableHandler) getPrimaryKeyColumns(tableName string) ([]string, error) {
	query := `
		SELECT kcu.column_name
		FROM information_schema.table_constraints tc
		JOIN information_schema.key_column_usage kcu
			ON tc.constraint_name = kcu.constraint_name
			AND tc.table_schema = kcu.table_schema
		WHERE tc.table_schema = 'public'
			AND tc.table_name = ?
			AND tc.constraint_type = 'PRIMARY KEY'
		ORDER BY kcu.ordinal_position
	`

	rows, err := h.DB.Raw(query, tableName).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	columns := []string{}
	for rows.Next() {
		var column string
		if scanErr := rows.Scan(&column); scanErr != nil {
			continue
		}
		columns = append(columns, column)
	}

	return columns, nil
}

func (h *SimpleMultiTableHandler) getTableColumnsMetadata(tableName string) ([]ColumnInfo, error) {
	query := `
		SELECT
			c.column_name,
			c.data_type,
			c.is_nullable,
			CASE WHEN pk.column_name IS NOT NULL THEN true ELSE false END AS is_primary_key,
			CASE WHEN c.column_default IS NOT NULL THEN true ELSE false END AS has_default,
			CASE WHEN c.is_identity = 'YES' THEN true ELSE false END AS is_identity
		FROM information_schema.columns c
		LEFT JOIN (
			SELECT kcu.table_name, kcu.column_name
			FROM information_schema.table_constraints tc
			JOIN information_schema.key_column_usage kcu
				ON tc.constraint_name = kcu.constraint_name
				AND tc.table_schema = kcu.table_schema
			WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_schema = 'public'
		) pk
			ON pk.table_name = c.table_name AND pk.column_name = c.column_name
		WHERE c.table_schema = 'public' AND c.table_name = ?
		ORDER BY c.ordinal_position
	`

	rows, err := h.DB.Raw(query, tableName).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	columns := []ColumnInfo{}
	for rows.Next() {
		var col ColumnInfo
		if scanErr := rows.Scan(&col.Name, &col.Type, &col.Nullable, &col.IsPrimaryKey, &col.HasDefault, &col.IsIdentity); scanErr != nil {
			continue
		}
		columns = append(columns, col)
	}

	return columns, nil
}

func qualifyFilterWithAlias(filterExpr string, alias string, tableColumns []string) string {
	qualified := strings.TrimSpace(filterExpr)
	if qualified == "" {
		return qualified
	}

	for _, col := range tableColumns {
		pattern := regexp.MustCompile(`\b` + regexp.QuoteMeta(col) + `\b`)
		replacement := fmt.Sprintf("%s.%s", alias, quoteIdentifier(col))
		qualified = pattern.ReplaceAllString(qualified, replacement)
	}

	return "(" + qualified + ")"
}

func (h *SimpleMultiTableHandler) getTableColumnNames(tableName string) ([]string, error) {
	query := `
		SELECT column_name
		FROM information_schema.columns
		WHERE table_schema = 'public' AND table_name = ?
		ORDER BY ordinal_position
	`

	rows, err := h.DB.Raw(query, tableName).Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	columns := []string{}
	for rows.Next() {
		var column string
		if scanErr := rows.Scan(&column); scanErr != nil {
			continue
		}
		columns = append(columns, column)
	}

	return columns, nil
}

func (h *SimpleMultiTableHandler) findTableRelation(tableA, tableB string) (*tableRelation, error) {
	query := `
		SELECT
			tc.table_name AS fk_table,
			kcu.column_name AS fk_column,
			ccu.table_name AS ref_table,
			ccu.column_name AS ref_column
		FROM information_schema.table_constraints tc
		JOIN information_schema.key_column_usage kcu
			ON tc.constraint_name = kcu.constraint_name
			AND tc.table_schema = kcu.table_schema
		JOIN information_schema.constraint_column_usage ccu
			ON ccu.constraint_name = tc.constraint_name
			AND ccu.table_schema = tc.table_schema
		WHERE tc.constraint_type = 'FOREIGN KEY'
			AND tc.table_schema = 'public'
			AND (
				(tc.table_name = ? AND ccu.table_name = ?)
				OR
				(tc.table_name = ? AND ccu.table_name = ?)
			)
		LIMIT 1
	`

	var relation tableRelation
	if err := h.DB.Raw(query, tableA, tableB, tableB, tableA).Scan(&relation).Error; err != nil {
		return nil, err
	}

	if relation.FKTable == "" {
		return h.inferTableRelationByNaming(tableA, tableB)
	}

	return &relation, nil
}

func (h *SimpleMultiTableHandler) inferTableRelationByNaming(tableA, tableB string) (*tableRelation, error) {
	columnsA, err := h.getTableColumnNames(tableA)
	if err != nil {
		return nil, err
	}
	columnsB, err := h.getTableColumnNames(tableB)
	if err != nil {
		return nil, err
	}

	columnSetA := map[string]bool{}
	for _, col := range columnsA {
		columnSetA[strings.ToLower(col)] = true
	}
	columnSetB := map[string]bool{}
	for _, col := range columnsB {
		columnSetB[strings.ToLower(col)] = true
	}

	singularA := singularizeTableName(tableA)
	singularB := singularizeTableName(tableB)

	// Convention: child.<parent>_id -> parent.id
	candidateBToA := singularA + "_id"
	if columnSetA["id"] && columnSetB[candidateBToA] {
		return &tableRelation{
			FKTable:   tableB,
			FKColumn:  candidateBToA,
			RefTable:  tableA,
			RefColumn: "id",
		}, nil
	}

	candidateAToB := singularB + "_id"
	if columnSetB["id"] && columnSetA[candidateAToB] {
		return &tableRelation{
			FKTable:   tableA,
			FKColumn:  candidateAToB,
			RefTable:  tableB,
			RefColumn: "id",
		}, nil
	}

	// Convention: exact common non-id key (e.g., customer_id exists in both)
	for _, col := range columnsA {
		lowerCol := strings.ToLower(col)
		if lowerCol == "id" {
			continue
		}
		if columnSetB[lowerCol] {
			return &tableRelation{
				FKTable:   tableA,
				FKColumn:  col,
				RefTable:  tableB,
				RefColumn: col,
			}, nil
		}
	}

	// Last-resort convention: id=id
	if columnSetA["id"] && columnSetB["id"] {
		return &tableRelation{
			FKTable:   tableA,
			FKColumn:  "id",
			RefTable:  tableB,
			RefColumn: "id",
		}, nil
	}

	return nil, fmt.Errorf("no relationship found")
}

func singularizeTableName(tableName string) string {
	lower := strings.ToLower(strings.TrimSpace(tableName))
	if strings.HasSuffix(lower, "ies") && len(lower) > 3 {
		return lower[:len(lower)-3] + "y"
	}
	if strings.HasSuffix(lower, "ses") && len(lower) > 3 {
		return lower[:len(lower)-2]
	}
	if strings.HasSuffix(lower, "s") && len(lower) > 1 {
		return lower[:len(lower)-1]
	}
	return lower
}
