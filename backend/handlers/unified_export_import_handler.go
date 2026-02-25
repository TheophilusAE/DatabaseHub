package handlers

import (
	"dataImportDashboard/config"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"sort"
	"strings"
	"time"

	"dataImportDashboard/models"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type UnifiedExportImportHandler struct {
	DB *gorm.DB
}

func NewUnifiedExportImportHandler(db *gorm.DB) *UnifiedExportImportHandler {
	return &UnifiedExportImportHandler{
		DB: db,
	}
}

// UnifiedExportRequest represents request for unified export
type UnifiedExportRequest struct {
	Tables []struct {
		TableName string   `json:"table_name"`
		Columns   []string `json:"columns"` // specific columns to export
		Filters   string   `json:"filters"` // SQL WHERE clause (optional)
	} `json:"tables"`
	Format string `json:"format"` // csv or json
}

// UnifiedExport exports data from multiple tables merged into a single output
func (h *UnifiedExportImportHandler) UnifiedExport(c *gin.Context) {
	var request UnifiedExportRequest
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

	// If only one table, no joining needed
	if len(request.Tables) == 1 {
		h.exportSingleTable(c, request.Tables[0], request.Format)
		return
	}

	// Multiple tables - need to join them
	h.exportJoinedTables(c, request.Tables, request.Format)
}

// exportSingleTable exports data from a single table
func (h *UnifiedExportImportHandler) exportSingleTable(c *gin.Context, tableReq struct {
	TableName string   `json:"table_name"`
	Columns   []string `json:"columns"`
	Filters   string   `json:"filters"`
}, format string) {
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
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Query failed: " + err.Error()})
		return
	}
	defer rows.Close()

	columnNames, _ := rows.Columns()
	h.streamExportFromRows(c, rows, columnNames, format)
}

// exportJoinedTables exports data by joining multiple tables
func (h *UnifiedExportImportHandler) exportJoinedTables(c *gin.Context, tables []struct {
	TableName string   `json:"table_name"`
	Columns   []string `json:"columns"`
	Filters   string   `json:"filters"`
}, format string) {
	
	// Detect join conditions between tables
	joinConditions, err := h.detectJoinConditions(tables)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Could not detect join relationships: " + err.Error()})
		return
	}

	// Build unified SELECT query
	query, selectedColumns := h.buildJoinQuery(tables, joinConditions)

	rows, err := h.DB.Raw(query).Rows()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Query failed: " + err.Error()})
		return
	}
	defer rows.Close()

	h.streamExportFromRows(c, rows, selectedColumns, format)
}

// detectJoinConditions finds relationships between tables
func (h *UnifiedExportImportHandler) detectJoinConditions(tables []struct {
	TableName string   `json:"table_name"`
	Columns   []string `json:"columns"`
	Filters   string   `json:"filters"`
}) ([]string, error) {
	var joins []string

	// Query to find foreign key relationships
	fkQuery := `
		SELECT
			tc.table_name,
			kcu.column_name,
			ccu.table_name AS foreign_table_name,
			ccu.column_name AS foreign_column_name
		FROM information_schema.table_constraints AS tc
		JOIN information_schema.key_column_usage AS kcu
			ON tc.constraint_name = kcu.constraint_name
			AND tc.table_schema = kcu.table_schema
		JOIN information_schema.constraint_column_usage AS ccu
			ON ccu.constraint_name = tc.constraint_name
			AND ccu.table_schema = tc.table_schema
		WHERE tc.constraint_type = 'FOREIGN KEY'
			AND tc.table_schema = 'public'
	`

	rows, err := h.DB.Raw(fkQuery).Rows()
	if err != nil {
		// Fallback: try to join on common column names (id, _id pattern)
		return h.detectCommonColumnJoins(tables)
	}
	defer rows.Close()

	type foreignKey struct {
		tableName        string
		columnName       string
		foreignTableName string
		foreignColumnName string
	}

	var foreignKeys []foreignKey
	for rows.Next() {
		var fk foreignKey
		rows.Scan(&fk.tableName, &fk.columnName, &fk.foreignTableName, &fk.foreignColumnName)
		foreignKeys = append(foreignKeys, fk)
	}

	// Build join conditions based on foreign keys
	for i := 1; i < len(tables); i++ {
		leftTable := tables[i-1].TableName
		rightTable := tables[i].TableName
		
		// Find foreign key relationship
		joinFound := false
		for _, fk := range foreignKeys {
			if (fk.tableName == leftTable && fk.foreignTableName == rightTable) {
				joins = append(joins, fmt.Sprintf("%s.%s = %s.%s", 
					leftTable, fk.columnName, rightTable, fk.foreignColumnName))
				joinFound = true
				break
			} else if (fk.tableName == rightTable && fk.foreignTableName == leftTable) {
				joins = append(joins, fmt.Sprintf("%s.%s = %s.%s", 
					rightTable, fk.columnName, leftTable, fk.foreignColumnName))
				joinFound = true
				break
			}
		}

		// Fallback: look for common id columns
		if !joinFound {
			commonJoin := h.findCommonColumnJoin(leftTable, rightTable)
			if commonJoin != "" {
				joins = append(joins, commonJoin)
			}
		}
	}

	if len(joins) == 0 {
		return nil, fmt.Errorf("no join relationships found between selected tables")
	}

	return joins, nil
}

// detectCommonColumnJoins finds joins based on common column names
func (h *UnifiedExportImportHandler) detectCommonColumnJoins(tables []struct {
	TableName string   `json:"table_name"`
	Columns   []string `json:"columns"`
	Filters   string   `json:"filters"`
}) ([]string, error) {
	var joins []string

	for i := 1; i < len(tables); i++ {
		leftTable := tables[i-1].TableName
		rightTable := tables[i].TableName
		
		commonJoin := h.findCommonColumnJoin(leftTable, rightTable)
		if commonJoin != "" {
			joins = append(joins, commonJoin)
		}
	}

	if len(joins) == 0 {
		return nil, fmt.Errorf("no common columns found for joining")
	}

	return joins, nil
}

// findCommonColumnJoin finds a common column to join two tables
func (h *UnifiedExportImportHandler) findCommonColumnJoin(leftTable, rightTable string) string {
	// Get columns for both tables
	leftCols := h.getTableColumns(leftTable)
	rightCols := h.getTableColumns(rightTable)

	// Look for common patterns
	patterns := []string{
		"id",
		leftTable + "_id",
		rightTable + "_id",
		"customer_id",
		"user_id",
		"employee_id",
		"product_id",
	}

	for _, pattern := range patterns {
		leftHas := false
		rightHas := false
		
		for _, col := range leftCols {
			if col == pattern {
				leftHas = true
				break
			}
		}
		
		for _, col := range rightCols {
			if col == pattern {
				rightHas = true
				break
			}
		}

		if leftHas && rightHas {
			return fmt.Sprintf("%s.%s = %s.%s", leftTable, pattern, rightTable, pattern)
		}
	}

	// Try matching rightTable_id in leftTable with id in rightTable
	rightTableIDCol := rightTable + "_id"
	for _, col := range leftCols {
		if col == rightTableIDCol {
			for _, rcol := range rightCols {
				if rcol == "id" {
					return fmt.Sprintf("%s.%s = %s.id", leftTable, rightTableIDCol, rightTable)
				}
			}
		}
	}

	// Try matching leftTable_id in rightTable with id in leftTable
	leftTableIDCol := leftTable + "_id"
	for _, col := range rightCols {
		if col == leftTableIDCol {
			for _, lcol := range leftCols {
				if lcol == "id" {
					return fmt.Sprintf("%s.id = %s.%s", leftTable, rightTable, leftTableIDCol)
				}
			}
		}
	}

	return ""
}

// getTableColumns gets column names for a table
func (h *UnifiedExportImportHandler) getTableColumns(tableName string) []string {
	query := `
		SELECT column_name 
		FROM information_schema.columns 
		WHERE table_schema = 'public' AND table_name = ?
		ORDER BY ordinal_position
	`
	
	rows, err := h.DB.Raw(query, tableName).Rows()
	if err != nil {
		return []string{}
	}
	defer rows.Close()

	var columns []string
	for rows.Next() {
		var col string
		rows.Scan(&col)
		columns = append(columns, col)
	}

	return columns
}

// buildJoinQuery builds the SQL query with joins
func (h *UnifiedExportImportHandler) buildJoinQuery(tables []struct {
	TableName string   `json:"table_name"`
	Columns   []string `json:"columns"`
	Filters   string   `json:"filters"`
}, joinConditions []string) (string, []string) {
	
	// Build SELECT clause with all selected columns
	var selectCols []string
	var selectedColNames []string
	
	for _, table := range tables {
		if len(table.Columns) == 0 {
			// Get all columns from table
			cols := h.getTableColumns(table.TableName)
			for _, col := range cols {
				alias := fmt.Sprintf("%s.%s", table.TableName, col)
				selectCols = append(selectCols, fmt.Sprintf("%s.%s AS \"%s\"", table.TableName, col, alias))
				selectedColNames = append(selectedColNames, alias)
			}
		} else {
			// Use specified columns
			for _, col := range table.Columns {
				alias := fmt.Sprintf("%s.%s", table.TableName, col)
				selectCols = append(selectCols, fmt.Sprintf("%s.%s AS \"%s\"", table.TableName, col, alias))
				selectedColNames = append(selectedColNames, alias)
			}
		}
	}

	// Build FROM and JOIN clauses
	query := fmt.Sprintf("SELECT %s FROM %s", strings.Join(selectCols, ", "), tables[0].TableName)

	for i := 1; i < len(tables); i++ {
		query += fmt.Sprintf(" LEFT JOIN %s ON %s", tables[i].TableName, joinConditions[i-1])
	}

	// Add WHERE clause if any filters
	var whereConditions []string
	for _, table := range tables {
		if table.Filters != "" {
			whereConditions = append(whereConditions, "("+table.Filters+")")
		}
	}

	if len(whereConditions) > 0 {
		query += " WHERE " + strings.Join(whereConditions, " AND ")
	}

	return query, selectedColNames
}

func (h *UnifiedExportImportHandler) streamExportFromRows(c *gin.Context, rows ioCloserRows, columnNames []string, format string) {
	if strings.EqualFold(format, "json") {
		c.Header("Content-Type", "application/json")
		c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=unified_export_%s.json", time.Now().Format("20060102_150405")))
		c.Header("Transfer-Encoding", "chunked")

		_, _ = c.Writer.Write([]byte(`{"data":[`))
		rowsWritten := 0
		encoder := json.NewEncoder(c.Writer)

		for rows.Next() {
			rowMap, err := scanRowToMap(rows, columnNames)
			if err != nil {
				continue
			}

			if rowsWritten > 0 {
				_, _ = c.Writer.Write([]byte(","))
			}

			if err := encoder.Encode(rowMap); err != nil {
				continue
			}
			rowsWritten++

			if rowsWritten%5000 == 0 {
				if f, ok := c.Writer.(http.Flusher); ok {
					f.Flush()
				}
			}
		}

		_, _ = c.Writer.Write([]byte(fmt.Sprintf(`],"count":%d}`, rowsWritten)))
		if f, ok := c.Writer.(http.Flusher); ok {
			f.Flush()
		}
		return
	}

	c.Header("Content-Type", "text/csv")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=unified_export_%s.csv", time.Now().Format("20060102_150405")))
	c.Header("Transfer-Encoding", "chunked")

	writer := csv.NewWriter(c.Writer)
	defer writer.Flush()

	if err := writer.Write(columnNames); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "failed to write CSV header"})
		return
	}

	rowsWritten := 0
	for rows.Next() {
		rowMap, err := scanRowToMap(rows, columnNames)
		if err != nil {
			continue
		}

		values := make([]string, len(columnNames))
		for i, colName := range columnNames {
			if val, exists := rowMap[colName]; exists && val != nil {
				values[i] = fmt.Sprintf("%v", val)
			}
		}

		if err := writer.Write(values); err != nil {
			continue
		}
		rowsWritten++

		if rowsWritten%5000 == 0 {
			writer.Flush()
			if f, ok := c.Writer.(http.Flusher); ok {
				f.Flush()
			}
		}
	}
}

type ioCloserRows interface {
	Next() bool
	Scan(dest ...interface{}) error
}

func scanRowToMap(rows ioCloserRows, columnNames []string) (map[string]interface{}, error) {
	columnValues := make([]interface{}, len(columnNames))
	columnPointers := make([]interface{}, len(columnNames))
	for i := range columnValues {
		columnPointers[i] = &columnValues[i]
	}

	if err := rows.Scan(columnPointers...); err != nil {
		return nil, err
	}

	row := make(map[string]interface{}, len(columnNames))
	for i, colName := range columnNames {
		val := columnValues[i]
		if b, ok := val.([]byte); ok {
			row[colName] = string(b)
		} else {
			row[colName] = val
		}
	}

	return row, nil
}

// SimpleExport provides a simple one-click export of selected tables
func (h *UnifiedExportImportHandler) SimpleExport(c *gin.Context) {
	tableNames := c.QueryArray("tables[]")
	format := c.DefaultQuery("format", "csv")

	if len(tableNames) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No tables selected"})
		return
	}

	// Build request
	request := UnifiedExportRequest{
		Tables: make([]struct {
			TableName string   `json:"table_name"`
			Columns   []string `json:"columns"`
			Filters   string   `json:"filters"`
		}, len(tableNames)),
		Format: format,
	}

	for i, tableName := range tableNames {
		request.Tables[i].TableName = tableName
		request.Tables[i].Columns = []string{} // All columns
		request.Tables[i].Filters = ""         // No filters
	}

	// Use JSON request handler through context
	c.Set("request", request)
	h.UnifiedExport(c)
}

// SimpleImport provides a simple import interface
func (h *UnifiedExportImportHandler) SimpleImport(c *gin.Context) {
	tableName := c.PostForm("table")
	if tableName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Table name is required"})
		return
	}

	// Get uploaded file
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Determine format from file extension
	format := "csv"
	if strings.HasSuffix(strings.ToLower(header.Filename), ".json") {
		format = "json"
	}

	// Get user from context
	userID := c.GetUint("user_id")

	// Import data
	successCount, failureCount, importErr := h.importToTable(file, tableName, format)

	// Create import log
	importLog := models.ImportLog{
		FileName:     header.Filename,
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

	c.JSON(http.StatusOK, gin.H{
		"message":       "Import completed",
		"table":         tableName,
		"filename":      header.Filename,
		"success_count": successCount,
		"failure_count": failureCount,
		"status":        importLog.Status,
		"error":         importLog.ErrorMessage,
	})
}

// importToTable handles importing data to a specific table
func (h *UnifiedExportImportHandler) importToTable(file io.Reader, tableName string, format string) (int, int, error) {
	if format == "csv" {
		return h.importCSVToTable(file, tableName)
	} else if format == "json" {
		return h.importJSONToTable(file, tableName)
	}

	return 0, 0, fmt.Errorf("unsupported format: %s", format)
}

// importCSVToTable imports CSV data to a table
func (h *UnifiedExportImportHandler) importCSVToTable(file io.Reader, tableName string) (int, int, error) {
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

	successCount := 0
	failureCount := 0

	columns := make([]string, 0, len(headers))
	for _, header := range headers {
		column := strings.TrimSpace(header)
		if column == "" || !isValidIdentifier(column) {
			continue
		}
		columns = append(columns, column)
	}
	if len(columns) == 0 {
		return 0, 0, fmt.Errorf("no valid CSV columns found")
	}

	columnIndex := make(map[string]int, len(headers))
	for i, header := range headers {
		columnIndex[strings.TrimSpace(header)] = i
	}

	batchRows := make([][]interface{}, 0, getOptimizedBatchSize())
	batchSize := getOptimizedBatchSize()

	flushBatch := func() {
		if len(batchRows) == 0 {
			return
		}
		success, failures := h.insertBatch(tableName, columns, batchRows)
		successCount += success
		failureCount += failures
		batchRows = batchRows[:0]
	}

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
		for i, column := range columns {
			idx := columnIndex[column]
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

		batchRows = append(batchRows, values)
		if len(batchRows) >= batchSize {
			flushBatch()
		}
	}

	flushBatch()

	return successCount, failureCount, nil
}

// importJSONToTable imports JSON data to a table
func (h *UnifiedExportImportHandler) importJSONToTable(file io.Reader, tableName string) (int, int, error) {
	if !isValidIdentifier(tableName) {
		return 0, 0, fmt.Errorf("invalid table name")
	}

	decoder := json.NewDecoder(file)
	t, err := decoder.Token()
	if err != nil {
		return 0, 0, fmt.Errorf("failed to decode JSON: %w", err)
	}
	if delim, ok := t.(json.Delim); !ok || delim != '[' {
		return 0, 0, fmt.Errorf("json must be an array")
	}

	successCount := 0
	failureCount := 0
	batchSize := getOptimizedBatchSize()
	batchRows := make([][]interface{}, 0, batchSize)
	columns := make([]string, 0)

	flushBatch := func() {
		if len(batchRows) == 0 || len(columns) == 0 {
			return
		}
		success, failures := h.insertBatch(tableName, columns, batchRows)
		successCount += success
		failureCount += failures
		batchRows = batchRows[:0]
	}

	for decoder.More() {
		var rowData map[string]interface{}
		if err := decoder.Decode(&rowData); err != nil {
			failureCount++
			continue
		}

		if len(rowData) == 0 {
			failureCount++
			continue
		}

		if len(columns) == 0 {
			for column := range rowData {
				if isValidIdentifier(column) {
					columns = append(columns, column)
				}
			}
			sort.Strings(columns)
			if len(columns) == 0 {
				failureCount++
				continue
			}
		}

		values := make([]interface{}, len(columns))
		for i, column := range columns {
			if value, exists := rowData[column]; exists {
				values[i] = value
			} else {
				values[i] = nil
			}
		}

		batchRows = append(batchRows, values)
		if len(batchRows) >= batchSize {
			flushBatch()
		}
	}

	flushBatch()

	if _, err := decoder.Token(); err != nil {
		return successCount, failureCount, fmt.Errorf("invalid JSON array termination: %w", err)
	}

	return successCount, failureCount, nil
}

// insertBatch inserts a batch of rows
func (h *UnifiedExportImportHandler) insertBatch(tableName string, columns []string, rows [][]interface{}) (int, int) {
	if len(columns) == 0 || len(rows) == 0 {
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

	singleInsertQuery := fmt.Sprintf(
		"INSERT INTO %s (%s) VALUES %s",
		quoteIdentifier(tableName),
		strings.Join(quotedColumns, ", "),
		singleRowPlaceholder,
	)

	successCount := 0
	failureCount := 0
	for _, row := range rows {
		if err := h.DB.Exec(singleInsertQuery, row...).Error; err != nil {
			failureCount++
		} else {
			successCount++
		}
	}

	return successCount, failureCount
}

func getOptimizedBatchSize() int {
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
