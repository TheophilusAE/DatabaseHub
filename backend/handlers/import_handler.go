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
	"time"

	"github.com/gin-gonic/gin"
)

type ImportHandler struct {
	dataRepo *repository.DataRecordRepository
	logRepo  *repository.ImportLogRepository
}

func NewImportHandler(dataRepo *repository.DataRecordRepository, logRepo *repository.ImportLogRepository) *ImportHandler {
	return &ImportHandler{
		dataRepo: dataRepo,
		logRepo:  logRepo,
	}
}

// Progress tracking structure
type ImportProgress struct {
	TotalProcessed int64
	SuccessCount   int64
	FailureCount   int64
	IsComplete     bool
	ErrorMessage   string
}

// Worker job structure for parallel processing
type ImportJob struct {
	Records []models.DataRecord
	JobID   int
}

// ImportCSV handles massive CSV file imports with streaming and worker pools
func (h *ImportHandler) ImportCSV(c *gin.Context) {
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Create import log
	importLog := &models.ImportLog{
		FileName:   header.Filename,
		ImportType: "csv",
		Status:     "processing",
		ImportedBy: c.GetString("user"),
	}

	if err := h.logRepo.Create(importLog); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create import log"})
		return
	}

	// Use streaming CSV parser
	reader := csv.NewReader(file)
	reader.TrimLeadingSpace = true
	reader.ReuseRecord = true // Reuse memory for better performance

	// Read header
	headers, err := reader.Read()
	if err != nil {
		importLog.Status = "failed"
		importLog.ErrorMessage = "Failed to read CSV headers: " + err.Error()
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{"error": "Failed to read CSV headers", "details": err.Error()})
		return
	}

	// Log headers for debugging
	fmt.Println("CSV Headers detected:", strings.Join(headers, ", "))

	// Atomic counters for thread-safe statistics
	var totalRecords, successCount, failureCount int64

	// Worker pool setup
	numWorkers := config.AppConfig.ImportWorkers
	batchSize := config.AppConfig.ImportBatchSize

	jobQueue := make(chan ImportJob, numWorkers*2)
	var wg sync.WaitGroup

	// Start worker goroutines
	for w := 0; w < numWorkers; w++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()
			h.importWorker(jobQueue, &successCount, &failureCount)
		}(w)
	}

	// Stream and batch records
	recordBatch := make([]models.DataRecord, 0, batchSize)
	jobID := 0

	for {
		row, err := reader.Read()
		if err == io.EOF {
			break
		}
		if err != nil {
			atomic.AddInt64(&failureCount, 1)
			continue
		}

		atomic.AddInt64(&totalRecords, 1)
		record := h.parseCSVRow(headers, row)

		if record != nil {
			recordBatch = append(recordBatch, *record)

			// Send batch to workers when full
			if len(recordBatch) >= batchSize {
				job := ImportJob{
					Records: make([]models.DataRecord, len(recordBatch)),
					JobID:   jobID,
				}
				copy(job.Records, recordBatch)
				jobQueue <- job
				recordBatch = recordBatch[:0] // Reset slice
				jobID++
			}
		} else {
			atomic.AddInt64(&failureCount, 1)
		}
	}

	// Send remaining records
	if len(recordBatch) > 0 {
		job := ImportJob{
			Records: make([]models.DataRecord, len(recordBatch)),
			JobID:   jobID,
		}
		copy(job.Records, recordBatch)
		jobQueue <- job
	}

	// Close job queue and wait for workers to finish
	close(jobQueue)
	wg.Wait()

	// Update import log
	importLog.Status = "completed"
	importLog.TotalRecords = int(totalRecords)
	importLog.SuccessCount = int(successCount)
	importLog.FailureCount = int(failureCount)
	h.logRepo.Update(importLog)

	// Prepare response message
	responseMessage := "Import completed successfully"
	if successCount == 0 && totalRecords > 0 {
		responseMessage = "Import failed - No records were imported. Please check your CSV format."
	} else if failureCount > 0 {
		responseMessage = fmt.Sprintf("Import completed with %d successes and %d failures", successCount, failureCount)
	}

	c.JSON(http.StatusOK, gin.H{
		"message":       responseMessage,
		"total":         totalRecords,
		"success":       successCount,
		"failed":        failureCount,
		"import_log_id": importLog.ID,
	})
}

// importWorker processes import jobs from the queue
func (h *ImportHandler) importWorker(jobs <-chan ImportJob, successCount, failureCount *int64) {
	for job := range jobs {
		if err := h.dataRepo.CreateBatch(job.Records); err != nil {
			atomic.AddInt64(failureCount, int64(len(job.Records)))
			fmt.Printf("Worker failed to insert batch %d: %v\n", job.JobID, err)
		} else {
			atomic.AddInt64(successCount, int64(len(job.Records)))
		}
	}
}

// ImportJSON handles massive JSON file imports with streaming decoder
func (h *ImportHandler) ImportJSON(c *gin.Context) {
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Create import log
	importLog := &models.ImportLog{
		FileName:   header.Filename,
		ImportType: "json",
		Status:     "processing",
		ImportedBy: c.GetString("user"),
	}

	if err := h.logRepo.Create(importLog); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create import log"})
		return
	}

	// Atomic counters
	var totalRecords, successCount, failureCount int64

	// Worker pool setup
	numWorkers := config.AppConfig.ImportWorkers
	batchSize := config.AppConfig.ImportBatchSize

	jobQueue := make(chan ImportJob, numWorkers*2)
	var wg sync.WaitGroup

	// Start worker goroutines
	for w := 0; w < numWorkers; w++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()
			h.importWorker(jobQueue, &successCount, &failureCount)
		}(w)
	}

	// Use streaming JSON decoder
	decoder := json.NewDecoder(file)

	// Expect array start
	t, err := decoder.Token()
	if err != nil {
		importLog.Status = "failed"
		importLog.ErrorMessage = "Invalid JSON format: " + err.Error()
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid JSON format", "details": err.Error()})
		return
	}

	if delim, ok := t.(json.Delim); !ok || delim != '[' {
		importLog.Status = "failed"
		importLog.ErrorMessage = "JSON must be an array of records"
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{"error": "JSON must be an array of records"})
		return
	}

	// Stream records in batches
	recordBatch := make([]models.DataRecord, 0, batchSize)
	jobID := 0

	for decoder.More() {
		var record models.DataRecord
		if err := decoder.Decode(&record); err != nil {
			atomic.AddInt64(&failureCount, 1)
			continue
		}

		atomic.AddInt64(&totalRecords, 1)

		// Set defaults
		if record.CreatedAt.IsZero() {
			record.CreatedAt = time.Now()
		}
		if record.UpdatedAt.IsZero() {
			record.UpdatedAt = time.Now()
		}
		if record.Status == "" {
			record.Status = "active"
		}

		// Validate required fields
		if record.Name == "" {
			atomic.AddInt64(&failureCount, 1)
			continue
		}

		recordBatch = append(recordBatch, record)

		// Send batch to workers when full
		if len(recordBatch) >= batchSize {
			job := ImportJob{
				Records: make([]models.DataRecord, len(recordBatch)),
				JobID:   jobID,
			}
			copy(job.Records, recordBatch)
			jobQueue <- job
			recordBatch = recordBatch[:0]
			jobID++
		}
	}

	// Send remaining records
	if len(recordBatch) > 0 {
		job := ImportJob{
			Records: make([]models.DataRecord, len(recordBatch)),
			JobID:   jobID,
		}
		copy(job.Records, recordBatch)
		jobQueue <- job
	}

	// Close job queue and wait for workers
	close(jobQueue)
	wg.Wait()

	// Update import log
	status := "completed"
	if successCount == 0 {
		status = "failed"
	} else if failureCount > 0 {
		status = "partial"
	}

	importLog.Status = status
	importLog.TotalRecords = int(totalRecords)
	importLog.SuccessCount = int(successCount)
	importLog.FailureCount = int(failureCount)
	h.logRepo.Update(importLog)

	c.JSON(http.StatusOK, gin.H{
		"message":       "Import completed",
		"total":         totalRecords,
		"success":       successCount,
		"failed":        failureCount,
		"import_log_id": importLog.ID,
	})
}

// GetImportLogs retrieves import logs with pagination
func (h *ImportHandler) GetImportLogs(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))

	logs, total, err := h.logRepo.FindAll(page, limit)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"data":        logs,
		"total":       total,
		"page":        page,
		"limit":       limit,
		"total_pages": (total + int64(limit) - 1) / int64(limit),
	})
}

// parseCSVRow parses a CSV row into a DataRecord with flexible header matching
func (h *ImportHandler) parseCSVRow(headers, row []string) *models.DataRecord {
	if len(headers) != len(row) {
		return nil
	}

	record := &models.DataRecord{
		Status:    "active",
		CreatedAt: time.Now(),
		UpdatedAt: time.Now(),
	}

	// Create a map of lowercase headers to values for case-insensitive matching
	dataMap := make(map[string]string)
	for i, header := range headers {
		// Trim whitespace and convert to lowercase for case-insensitive matching
		cleanHeader := strings.ToLower(strings.TrimSpace(header))
		if i < len(row) {
			dataMap[cleanHeader] = strings.TrimSpace(row[i])
		}
	}

	// Parse fields with case-insensitive matching
	if val, ok := dataMap["name"]; ok && val != "" {
		record.Name = val
	}

	if val, ok := dataMap["description"]; ok {
		record.Description = val
	}

	if val, ok := dataMap["category"]; ok && val != "" {
		record.Category = val
	}

	if val, ok := dataMap["value"]; ok && val != "" {
		if v, err := strconv.ParseFloat(val, 64); err == nil {
			record.Value = v
		}
	}

	if val, ok := dataMap["status"]; ok && val != "" {
		// Normalize status to lowercase
		record.Status = strings.ToLower(val)
	}

	if val, ok := dataMap["metadata"]; ok && val != "" {
		record.Metadata = &val
	}

	// Validate required fields: name is mandatory
	if record.Name == "" {
		return nil
	}

	// Set default category if empty
	if record.Category == "" {
		record.Category = "other"
	}

	return record
}

// GetImportLogByID retrieves a single import log
func (h *ImportHandler) GetImportLogByID(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	log, err := h.logRepo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Import log not found"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": log})
}
