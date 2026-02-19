package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strconv"
	"strings"
	"sync"
	"sync/atomic"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type MultiTableImportHandler struct {
	tableConfigRepo   *repository.TableConfigRepository
	importMappingRepo *repository.ImportMappingRepository
	logRepo           *repository.ImportLogRepository
	dbManager         *config.MultiDatabaseManager
}

func NewMultiTableImportHandler(
	tableConfigRepo *repository.TableConfigRepository,
	importMappingRepo *repository.ImportMappingRepository,
	logRepo *repository.ImportLogRepository,
	dbManager *config.MultiDatabaseManager,
) *MultiTableImportHandler {
	return &MultiTableImportHandler{
		tableConfigRepo:   tableConfigRepo,
		importMappingRepo: importMappingRepo,
		logRepo:           logRepo,
		dbManager:         dbManager,
	}
}

// ImportToTable imports data to a specific configured table
func (h *MultiTableImportHandler) ImportToTable(c *gin.Context) {
	// Get mapping name from form
	mappingName := c.PostForm("mapping_name")
	if mappingName == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "mapping_name is required"})
		return
	}

	// Get the import mapping configuration
	mapping, err := h.importMappingRepo.FindByName(mappingName)
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Import mapping not found"})
		return
	}

	// Get table configuration
	tableConfig, err := h.tableConfigRepo.FindByID(mapping.TableConfigID)
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

	// Get uploaded file
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Create import log
	importLog := &models.ImportLog{
		FileName:   header.Filename,
		ImportType: mapping.SourceFormat,
		Status:     "processing",
		ImportedBy: c.GetString("user"),
	}

	if err := h.logRepo.Create(importLog); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create import log"})
		return
	}

	// Parse column mapping
	var columnMapping map[string]string
	if err := json.Unmarshal([]byte(mapping.ColumnMapping), &columnMapping); err != nil {
		importLog.Status = "failed"
		importLog.ErrorMessage = "Invalid column mapping configuration"
		h.logRepo.Update(importLog)
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Invalid column mapping"})
		return
	}

	// Process based on source format
	var totalRecords, successCount, failureCount int64

	switch mapping.SourceFormat {
	case "csv":
		totalRecords, successCount, failureCount = h.importCSVToTable(
			file, tableConfig, columnMapping, db,
		)
	case "json":
		totalRecords, successCount, failureCount = h.importJSONToTable(
			file, tableConfig, columnMapping, db,
		)
	default:
		importLog.Status = "failed"
		importLog.ErrorMessage = "Unsupported source format"
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{"error": "Unsupported source format"})
		return
	}

	// Update import log
	importLog.TotalRecords = int(totalRecords)
	importLog.SuccessCount = int(successCount)
	importLog.FailureCount = int(failureCount)
	if failureCount == 0 {
		importLog.Status = "completed"
	} else {
		importLog.Status = "completed_with_errors"
	}
	h.logRepo.Update(importLog)

	c.JSON(http.StatusOK, gin.H{
		"message":         "Import completed",
		"total_records":   totalRecords,
		"success_records": successCount,
		"failed_records":  failureCount,
		"import_log_id":   importLog.ID,
	})
}

// importCSVToTable imports CSV data to a specific table
func (h *MultiTableImportHandler) importCSVToTable(
	file io.Reader,
	tableConfig *models.TableConfig,
	columnMapping map[string]string,
	db *gorm.DB,
) (int64, int64, int64) {
	reader := csv.NewReader(file)
	reader.TrimLeadingSpace = true
	reader.ReuseRecord = true

	// Read headers
	headersTemp, err := reader.Read()
	if err != nil {
		return 0, 0, 0
	}

	headers := make([]string, len(headersTemp))
	copy(headers, headersTemp)

	// Map source columns to target columns
	targetColumns := make([]string, 0)
	sourceIndices := make([]int, 0)

	for targetCol, sourceCol := range columnMapping {
		for i, h := range headers {
			if strings.EqualFold(h, sourceCol) {
				targetColumns = append(targetColumns, targetCol)
				sourceIndices = append(sourceIndices, i)
				break
			}
		}
	}

	if len(targetColumns) == 0 {
		return 0, 0, 0
	}

	// Atomic counters
	var totalRecords, successCount, failureCount int64

	// Worker pool setup
	numWorkers := config.AppConfig.ImportWorkers
	batchSize := config.AppConfig.ImportBatchSize

	type ImportJob struct {
		Records [][]interface{}
		JobID   int
	}

	jobQueue := make(chan ImportJob, numWorkers*2)
	var wg sync.WaitGroup

	// Start workers
	for i := 0; i < numWorkers; i++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()
			dynamicRepo := repository.NewDynamicTableRepository(db)

			for job := range jobQueue {
				if err := dynamicRepo.InsertBatch(tableConfig.Table, targetColumns, job.Records); err != nil {
					atomic.AddInt64(&failureCount, int64(len(job.Records)))
					fmt.Printf("Worker %d failed to insert batch: %v\n", workerID, err)
				} else {
					atomic.AddInt64(&successCount, int64(len(job.Records)))
				}
			}
		}(i)
	}

	// Read and batch records
	batch := make([][]interface{}, 0, batchSize)
	jobID := 0

	for {
		record, err := reader.Read()
		if err == io.EOF {
			break
		}
		if err != nil {
			atomic.AddInt64(&failureCount, 1)
			continue
		}

		// Extract values based on mapping
		values := make([]interface{}, len(sourceIndices))
		for i, idx := range sourceIndices {
			if idx < len(record) {
				values[i] = record[idx]
			} else {
				values[i] = nil
			}
		}

		batch = append(batch, values)
		atomic.AddInt64(&totalRecords, 1)

		// Send batch when full
		if len(batch) >= batchSize {
			jobQueue <- ImportJob{Records: batch, JobID: jobID}
			jobID++
			batch = make([][]interface{}, 0, batchSize)
		}
	}

	// Send remaining records
	if len(batch) > 0 {
		jobQueue <- ImportJob{Records: batch, JobID: jobID}
	}

	close(jobQueue)
	wg.Wait()

	return totalRecords, successCount, failureCount
}

// importJSONToTable imports JSON data to a specific table
func (h *MultiTableImportHandler) importJSONToTable(
	file io.Reader,
	tableConfig *models.TableConfig,
	columnMapping map[string]string,
	db *gorm.DB,
) (int64, int64, int64) {
	var data []map[string]interface{}
	decoder := json.NewDecoder(file)
	if err := decoder.Decode(&data); err != nil {
		return 0, 0, 0
	}

	// Get target columns
	targetColumns := make([]string, 0, len(columnMapping))
	for targetCol := range columnMapping {
		targetColumns = append(targetColumns, targetCol)
	}

	// Atomic counters
	var totalRecords, successCount, failureCount int64
	totalRecords = int64(len(data))

	// Worker pool setup
	numWorkers := config.AppConfig.ImportWorkers
	batchSize := config.AppConfig.ImportBatchSize

	type ImportJob struct {
		Records [][]interface{}
		JobID   int
	}

	jobQueue := make(chan ImportJob, numWorkers*2)
	var wg sync.WaitGroup

	// Start workers
	for i := 0; i < numWorkers; i++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()
			dynamicRepo := repository.NewDynamicTableRepository(db)

			for job := range jobQueue {
				if err := dynamicRepo.InsertBatch(tableConfig.Table, targetColumns, job.Records); err != nil {
					atomic.AddInt64(&failureCount, int64(len(job.Records)))
					fmt.Printf("Worker %d failed to insert batch: %v\n", workerID, err)
				} else {
					atomic.AddInt64(&successCount, int64(len(job.Records)))
				}
			}
		}(i)
	}

	// Process records in batches
	batch := make([][]interface{}, 0, batchSize)
	jobID := 0

	for _, record := range data {
		values := make([]interface{}, len(targetColumns))
		for i, targetCol := range targetColumns {
			sourceCol := columnMapping[targetCol]
			if val, ok := record[sourceCol]; ok {
				values[i] = val
			} else {
				values[i] = nil
			}
		}

		batch = append(batch, values)

		if len(batch) >= batchSize {
			jobQueue <- ImportJob{Records: batch, JobID: jobID}
			jobID++
			batch = make([][]interface{}, 0, batchSize)
		}
	}

	// Send remaining records
	if len(batch) > 0 {
		jobQueue <- ImportJob{Records: batch, JobID: jobID}
	}

	close(jobQueue)
	wg.Wait()

	return totalRecords, successCount, failureCount
}

// ListImportMappings lists all available import mappings
func (h *MultiTableImportHandler) ListImportMappings(c *gin.Context) {
	mappings, err := h.importMappingRepo.FindAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to retrieve import mappings"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"mappings": mappings,
		"count":    len(mappings),
	})
}

// CreateImportMapping creates a new import mapping
func (h *MultiTableImportHandler) CreateImportMapping(c *gin.Context) {
	var mapping models.ImportMapping
	if err := c.ShouldBindJSON(&mapping); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	mapping.CreatedBy = c.GetString("user")

	if err := h.importMappingRepo.Create(&mapping); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create import mapping"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "Import mapping created successfully",
		"mapping": mapping,
	})
}

func (h *MultiTableImportHandler) UpdateImportMapping(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseUint(idParam, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid mapping ID"})
		return
	}

	var updates models.ImportMapping
	if err := c.ShouldBindJSON(&updates); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// Get existing mapping first
	existing, err := h.importMappingRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Import mapping not found"})
		return
	}

	// Update fields
	existing.Name = updates.Name
	existing.TableConfigID = updates.TableConfigID
	existing.ColumnMapping = updates.ColumnMapping
	existing.SourceFormat = updates.SourceFormat
	existing.Description = updates.Description
	existing.Transform = updates.Transform
	existing.IsActive = updates.IsActive
	// existing.UpdatedBy = c.GetString("user")

	if err := h.importMappingRepo.Update(existing); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update import mapping: " + err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Import mapping updated successfully",
		"mapping": existing,
	})
}

func (h *MultiTableImportHandler) DeleteImportMapping(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseUint(idParam, 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid mapping ID"})
		return
	}

	// Check if mapping exists
	_, err = h.importMappingRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Import mapping not found"})
		return
	}

	if err := h.importMappingRepo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete import mapping: " + err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Import mapping deleted successfully",
	})
}
