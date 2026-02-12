package handlers

import (
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/csv"
	"encoding/json"
	"io"
	"net/http"
	"strconv"
	"strings"
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

// ImportCSV handles CSV file import
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

	// Parse CSV file
	reader := csv.NewReader(file)
	reader.TrimLeadingSpace = true // Automatically trim leading spaces

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
	println("CSV Headers detected:", strings.Join(headers, ", "))

	var records []models.DataRecord
	successCount := 0
	failureCount := 0
	totalRecords := 0

	// Read data rows with memory-efficient processing
	for {
		row, err := reader.Read()
		if err == io.EOF {
			break
		}
		if err != nil {
			failureCount++
			continue
		}

		totalRecords++
		record := h.parseCSVRow(headers, row)
		if record != nil {
			records = append(records, *record)

			// Batch insert every 5000 records to manage memory for very large files
			if len(records) >= 5000 {
				if err := h.dataRepo.CreateBatch(records); err != nil {
					failureCount += len(records)
				} else {
					successCount += len(records)
				}
				records = []models.DataRecord{} // Reset slice
			}
		} else {
			failureCount++
		}
	}

	// Batch insert remaining records
	if len(records) > 0 {
		if err := h.dataRepo.CreateBatch(records); err != nil {
			failureCount += len(records)
			importLog.Status = "failed"
			importLog.ErrorMessage = err.Error()
			h.logRepo.Update(importLog)
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to import data"})
			return
		} else {
			successCount += len(records)
		}
	}

	// Update import log
	importLog.Status = "completed"
	importLog.TotalRecords = totalRecords
	importLog.SuccessCount = successCount
	importLog.FailureCount = failureCount
	h.logRepo.Update(importLog)

	// Prepare response message
	responseMessage := "Import completed"
	if successCount == 0 && totalRecords > 0 {
		responseMessage = "Import failed - No records were imported. Please check your CSV format."
	} else if failureCount > 0 {
		responseMessage = "Import partially completed with some failures"
	}

	c.JSON(http.StatusOK, gin.H{
		"message":       responseMessage,
		"total":         totalRecords,
		"success":       successCount,
		"failed":        failureCount,
		"import_log_id": importLog.ID,
	})
}

// ImportJSON handles JSON file import
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

	// Parse JSON file
	var records []models.DataRecord
	decoder := json.NewDecoder(file)

	// Decode the JSON array
	if err := decoder.Decode(&records); err != nil {
		importLog.Status = "failed"
		importLog.ErrorMessage = "Failed to parse JSON: " + err.Error()
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{
			"error":   "Failed to parse JSON file. Ensure it's a valid JSON array of records.",
			"details": err.Error(),
		})
		return
	}

	// Validate we have records
	if len(records) == 0 {
		importLog.Status = "completed"
		importLog.TotalRecords = 0
		importLog.SuccessCount = 0
		importLog.FailureCount = 0
		h.logRepo.Update(importLog)
		c.JSON(http.StatusOK, gin.H{
			"message":       "No records found in JSON file",
			"total":         0,
			"import_log_id": importLog.ID,
		})
		return
	}

	// Process and validate records, then batch insert for better performance
	successCount := 0
	failureCount := 0
	totalRecords := len(records)
	var validRecords []models.DataRecord

	for i := range records {
		// Ensure timestamps are set
		if records[i].CreatedAt.IsZero() {
			records[i].CreatedAt = time.Now()
		}
		if records[i].UpdatedAt.IsZero() {
			records[i].UpdatedAt = time.Now()
		}

		// Ensure required fields
		if records[i].Name == "" {
			failureCount++
			continue
		}

		// Set default status if empty
		if records[i].Status == "" {
			records[i].Status = "active"
		}

		validRecords = append(validRecords, records[i])
	}

	// Batch insert all valid records
	if len(validRecords) > 0 {
		if err := h.dataRepo.CreateBatch(validRecords); err != nil {
			importLog.Status = "failed"
			importLog.ErrorMessage = "Failed to insert records: " + err.Error()
			h.logRepo.Update(importLog)
			c.JSON(http.StatusInternalServerError, gin.H{
				"error":   "Failed to import data",
				"details": err.Error(),
			})
			return
		}
		successCount = len(validRecords)
	}

	// Update import log
	status := "completed"
	if successCount == 0 {
		status = "failed"
	} else if failureCount > 0 {
		status = "partial"
	}

	importLog.Status = status
	importLog.TotalRecords = totalRecords
	importLog.SuccessCount = successCount
	importLog.FailureCount = failureCount
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
