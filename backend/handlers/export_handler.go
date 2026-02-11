package handlers

import (
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

type ExportHandler struct {
	dataRepo *repository.DataRecordRepository
}

func NewExportHandler(dataRepo *repository.DataRecordRepository) *ExportHandler {
	return &ExportHandler{dataRepo: dataRepo}
}

// ExportCSV exports data records to CSV
func (h *ExportHandler) ExportCSV(c *gin.Context) {
	// Get optional category filter
	category := c.Query("category")

	var records []models.DataRecord
	var err error

	if category != "" {
		records, err = h.dataRepo.FindByCategory(category)
	} else {
		records, _, err = h.dataRepo.FindAll(1, 10000) // Export max 10k records
	}

	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch data"})
		return
	}

	// Set headers for CSV download
	filename := fmt.Sprintf("data_export_%s.csv", time.Now().Format("20060102_150405"))
	c.Header("Content-Type", "text/csv")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))

	// Create CSV writer
	writer := csv.NewWriter(c.Writer)
	defer writer.Flush()

	// Write header
	header := []string{"ID", "Name", "Description", "Category", "Value", "Status", "Metadata", "Created At", "Updated At"}
	if err := writer.Write(header); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to write CSV"})
		return
	}

	// Write data rows
	for _, record := range records {
		metadata := ""
		if record.Metadata != nil {
			metadata = *record.Metadata
		}
		row := []string{
			strconv.FormatUint(uint64(record.ID), 10),
			record.Name,
			record.Description,
			record.Category,
			fmt.Sprintf("%.2f", record.Value),
			record.Status,
			metadata,
			record.CreatedAt.Format(time.RFC3339),
			record.UpdatedAt.Format(time.RFC3339),
		}
		if err := writer.Write(row); err != nil {
			continue
		}
	}
}

// ExportJSON exports data records to JSON
func (h *ExportHandler) ExportJSON(c *gin.Context) {
	// Get optional category filter
	category := c.Query("category")

	var records []models.DataRecord
	var err error

	if category != "" {
		records, err = h.dataRepo.FindByCategory(category)
	} else {
		records, _, err = h.dataRepo.FindAll(1, 10000) // Export max 10k records
	}

	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to fetch data"})
		return
	}

	// Set headers for JSON download
	filename := fmt.Sprintf("data_export_%s.json", time.Now().Format("20060102_150405"))
	c.Header("Content-Type", "application/json")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))

	// Encode and write JSON
	encoder := json.NewEncoder(c.Writer)
	encoder.SetIndent("", "  ")
	if err := encoder.Encode(records); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to write JSON"})
		return
	}
}

// ExportExcel exports data records to Excel (placeholder)
func (h *ExportHandler) ExportExcel(c *gin.Context) {
	c.JSON(http.StatusNotImplemented, gin.H{
		"message": "Excel export not yet implemented. Use CSV or JSON export for now.",
	})
}
