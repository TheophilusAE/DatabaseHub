package handlers

import (
	"encoding/csv"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strconv"
	"strings"
	"time"

	"dataImportDashboard/models"
	"dataImportDashboard/repository"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
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
	Name     string `json:"name"`
	Type     string `json:"type"`
	Nullable string `json:"nullable"`
}

// ListTables returns all tables in the current database
func (h *SimpleMultiTableHandler) ListTables(c *gin.Context) {
	var tables []TableInfo

	// Check if user filtering is needed
	userIDStr := c.Query("user_id")
	userRole := c.Query("user_role")

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

	// Get accessible table names for non-admin users
	accessibleTableNames := make(map[string]bool)
	if userRole != "admin" && userIDStr != "" {
		userID, err := strconv.ParseUint(userIDStr, 10, 32)
		if err == nil {
			// Get table configs user has access to
			accessibleTableIDs, err := h.PermRepo.GetAccessibleTables(uint(userID))
			if err == nil {
				// Map table config IDs to actual table names
				for _, tableID := range accessibleTableIDs {
					config, err := h.TableConfigRepo.GetByID(tableID)
					if err == nil {
						accessibleTableNames[config.Table] = true
					}
				}
			}
		}
	}

	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			continue
		}

		// Filter tables if user is not admin
		if userRole != "admin" && userIDStr != "" {
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

	// Get column information from information_schema
	query := `
		SELECT column_name, data_type, is_nullable
		FROM information_schema.columns
		WHERE table_schema = 'public' AND table_name = ?
		ORDER BY ordinal_position
	`

	rows, err := h.DB.Raw(query, tableName).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch columns: " + err.Error()})
		return
	}
	defer rows.Close()

	var columns []ColumnInfo
	for rows.Next() {
		var col ColumnInfo
		if err := rows.Scan(&col.Name, &col.Type, &col.Nullable); err != nil {
			continue
		}
		columns = append(columns, col)
	}

	c.JSON(http.StatusOK, gin.H{
		"table":   tableName,
		"columns": columns,
	})
}

// GetTableData returns paginated data from a specific table
func (h *SimpleMultiTableHandler) GetTableData(c *gin.Context) {
	tableName := c.Param("table")
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	pageSize, _ := strconv.Atoi(c.DefaultQuery("page_size", "50"))

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

// MultiTableUploadRequest represents upload request for multiple tables
type MultiTableUploadRequest struct {
	Files []struct {
		TableName string `json:"table_name"`
		FileData  string `json:"file_data"` // Base64 or direct data
		Format    string `json:"format"`    // csv or json
	} `json:"files"`
}

// UploadToMultipleTables handles uploading data to multiple tables at once
func (h *SimpleMultiTableHandler) UploadToMultipleTables(c *gin.Context) {
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

	results := []map[string]interface{}{}
	totalSuccess := 0
	totalFailed := 0

	// Get user from context
	userID := c.GetUint("user_id")

	// Process each file
	for i, fileHeader := range files {
		tableName := tableNames[i]

		file, err := fileHeader.Open()
		if err != nil {
			results = append(results, map[string]interface{}{
				"table":   tableName,
				"status":  "error",
				"message": "Failed to open file: " + err.Error(),
			})
			continue
		}

		// Determine file format
		format := "csv"
		if strings.HasSuffix(strings.ToLower(fileHeader.Filename), ".json") {
			format = "json"
		}

		// Import data
		successCount, failureCount, importErr := h.importToTable(file, tableName, format)
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
func (h *SimpleMultiTableHandler) importToTable(file io.Reader, tableName string, format string) (int, int, error) {
	if format == "csv" {
		return h.importCSVToTable(file, tableName)
	} else if format == "json" {
		return h.importJSONToTable(file, tableName)
	}

	return 0, 0, fmt.Errorf("unsupported format: %s", format)
}

// importCSVToTable imports CSV data to a table
func (h *SimpleMultiTableHandler) importCSVToTable(file io.Reader, tableName string) (int, int, error) {
	reader := csv.NewReader(file)
	reader.LazyQuotes = true
	reader.TrimLeadingSpace = true

	// Read header
	headers, err := reader.Read()
	if err != nil {
		return 0, 0, fmt.Errorf("failed to read CSV headers: %w", err)
	}

	successCount := 0
	failureCount := 0

	// Read and insert rows
	for {
		record, err := reader.Read()
		if err == io.EOF {
			break
		}
		if err != nil {
			failureCount++
			continue
		}

		// Create a map of column -> value
		rowData := make(map[string]interface{})
		for i, header := range headers {
			if i < len(record) {
				rowData[header] = record[i]
			}
		}

		// Build dynamic insert query
		columns := []string{}
		placeholders := []string{}
		values := []interface{}{}

		for col, val := range rowData {
			columns = append(columns, col)
			placeholders = append(placeholders, "?")
			values = append(values, val)
		}

		query := fmt.Sprintf(
			"INSERT INTO %s (%s) VALUES (%s)",
			tableName,
			strings.Join(columns, ", "),
			strings.Join(placeholders, ", "),
		)

		if err := h.DB.Exec(query, values...).Error; err != nil {
			failureCount++
		} else {
			successCount++
		}
	}

	return successCount, failureCount, nil
}

// importJSONToTable imports JSON data to a table
func (h *SimpleMultiTableHandler) importJSONToTable(file io.Reader, tableName string) (int, int, error) {
	var data []map[string]interface{}

	decoder := json.NewDecoder(file)
	if err := decoder.Decode(&data); err != nil {
		return 0, 0, fmt.Errorf("failed to decode JSON: %w", err)
	}

	successCount := 0
	failureCount := 0

	for _, rowData := range data {
		// Build dynamic insert query
		columns := []string{}
		placeholders := []string{}
		values := []interface{}{}

		for col, val := range rowData {
			columns = append(columns, col)
			placeholders = append(placeholders, "?")
			values = append(values, val)
		}

		query := fmt.Sprintf(
			"INSERT INTO %s (%s) VALUES (%s)",
			tableName,
			strings.Join(columns, ", "),
			strings.Join(placeholders, ", "),
		)

		if err := h.DB.Exec(query, values...).Error; err != nil {
			failureCount++
		} else {
			successCount++
		}
	}

	return successCount, failureCount, nil
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

// ExportSelectedData exports data from selected tables with filters
func (h *SimpleMultiTableHandler) ExportSelectedData(c *gin.Context) {
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

	// Collect all data
	allData := make(map[string]interface{})

	for _, tableReq := range request.Tables {
		// Build SELECT query
		columns := "*"
		if len(tableReq.Columns) > 0 {
			columns = strings.Join(tableReq.Columns, ", ")
		}

		query := fmt.Sprintf("SELECT %s FROM %s", columns, tableReq.TableName)
		if tableReq.Filters != "" {
			query += " WHERE " + tableReq.Filters
		}

		rows, err := h.DB.Raw(query).Rows()
		if err != nil {
			allData[tableReq.TableName] = map[string]interface{}{
				"error": err.Error(),
			}
			continue
		}

		// Get column names
		columnNames, _ := rows.Columns()

		// Collect rows
		var tableData []map[string]interface{}
		for rows.Next() {
			columnValues := make([]interface{}, len(columnNames))
			columnPointers := make([]interface{}, len(columnNames))
			for i := range columnValues {
				columnPointers[i] = &columnValues[i]
			}

			rows.Scan(columnPointers...)

			row := make(map[string]interface{})
			for i, colName := range columnNames {
				val := columnValues[i]
				if b, ok := val.([]byte); ok {
					row[colName] = string(b)
				} else {
					row[colName] = val
				}
			}
			tableData = append(tableData, row)
		}
		rows.Close()

		allData[tableReq.TableName] = tableData
	}

	// Export based on format
	if request.Format == "json" {
		c.Header("Content-Type", "application/json")
		c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=export_%s.json", time.Now().Format("20060102_150405")))
		c.JSON(http.StatusOK, allData)
	} else {
		// CSV export - combine all tables
		c.Header("Content-Type", "text/csv")
		c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=export_%s.csv", time.Now().Format("20060102_150405")))

		writer := csv.NewWriter(c.Writer)
		defer writer.Flush()

		// Write each table's data
		for tableName, data := range allData {
			if tableDataSlice, ok := data.([]map[string]interface{}); ok && len(tableDataSlice) > 0 {
				// Write table name header
				writer.Write([]string{fmt.Sprintf("=== %s ===", tableName)})

				// Write column headers
				var headers []string
				for col := range tableDataSlice[0] {
					headers = append(headers, col)
				}
				writer.Write(headers)

				// Write rows
				for _, row := range tableDataSlice {
					var values []string
					for _, header := range headers {
						values = append(values, fmt.Sprintf("%v", row[header]))
					}
					writer.Write(values)
				}

				// Empty line between tables
				writer.Write([]string{})
			}
		}
	}
}
