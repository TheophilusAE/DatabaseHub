package handlers

import (
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/csv"
	"encoding/json"
	"io"
	"net/http"
	"strconv"
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

	// Read header
	headers, err := reader.Read()
	if err != nil {
		importLog.Status = "failed"
		importLog.ErrorMessage = err.Error()
		h.logRepo.Update(importLog)
		c.JSON(http.StatusBadRequest, gin.H{"error": "Failed to read CSV headers"})
		return
	}

	var records []models.DataRecord
	successCount := 0
	failureCount := 0
	totalRecords := 0

	// Read data rows
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
			successCount++
		} else {
			failureCount++
		}
	}

	// Batch insert records
	if len(records) > 0 {
		if err := h.dataRepo.CreateBatch(records); err != nil {
			importLog.Status = "failed"
			importLog.ErrorMessage = err.Error()
			h.logRepo.Update(importLog)
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to import data"})
			return
		}
	}

	// Update import log
	importLog.Status = "completed"
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

	// Process and insert records one by one for better error handling
	successCount := 0
	failureCount := 0
	totalRecords := len(records)

	for i, record := range records {
		// Ensure timestamps are set
		if record.CreatedAt.IsZero() {
			record.CreatedAt = time.Now()
		}
		if record.UpdatedAt.IsZero() {
			record.UpdatedAt = time.Now()
		}

		// Ensure required fields
		if record.Name == "" {
			failureCount++
			continue
		}

		// Set default status if empty
		if record.Status == "" {
			record.Status = "active"
		}

		// Insert record
		if err := h.dataRepo.Create(&records[i]); err != nil {
			failureCount++
			continue
		}
		successCount++
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

// parseCSVRow parses a CSV row into a DataRecord
func (h *ImportHandler) parseCSVRow(headers, row []string) *models.DataRecord {
	if len(headers) != len(row) {
		return nil
	}

	record := &models.DataRecord{
		Status:    "active",
		CreatedAt: time.Now(),
		UpdatedAt: time.Now(),
	}

	for i, header := range headers {
		value := row[i]
		switch header {
		case "name":
			record.Name = value
		case "description":
			record.Description = value
		case "category":
			record.Category = value
		case "value":
			if v, err := strconv.ParseFloat(value, 64); err == nil {
				record.Value = v
			}
		case "status":
			if value != "" {
				record.Status = value
			}
		case "metadata":
			if value != "" {
				record.Metadata = &value
			}
		}
	}

	// Validate required fields
	if record.Name == "" {
		return nil
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
