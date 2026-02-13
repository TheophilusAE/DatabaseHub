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
	"time"

	"github.com/gin-gonic/gin"
)

type ExportHandler struct {
	dataRepo *repository.DataRecordRepository
}

func NewExportHandler(dataRepo *repository.DataRecordRepository) *ExportHandler {
	return &ExportHandler{dataRepo: dataRepo}
}

// ExportCSV exports data records to CSV with streaming for massive datasets
func (h *ExportHandler) ExportCSV(c *gin.Context) {
	// Get optional category filter
	category := c.Query("category")

	// Set headers for CSV download
	filename := fmt.Sprintf("data_export_%s.csv", time.Now().Format("20060102_150405"))
	c.Header("Content-Type", "text/csv")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))
	c.Header("Transfer-Encoding", "chunked")

	// Create buffered CSV writer
	writer := csv.NewWriter(c.Writer)
	defer writer.Flush()

	// Write header
	header := []string{"ID", "Name", "Description", "Category", "Value", "Status", "Metadata", "Created At", "Updated At"}
	if err := writer.Write(header); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to write CSV header"})
		return
	}

	// Stream records in batches to handle billions of rows
	batchSize := config.AppConfig.ExportBatchSize
	offset := 0
	recordsWritten := 0

	for {
		var records []models.DataRecord
		var err error

		// Fetch batch from repository
		if category != "" {
			records, err = h.dataRepo.FindByCategoryPaginated(category, offset, batchSize)
		} else {
			records, _, err = h.dataRepo.FindAllNoPagination(offset, batchSize)
		}

		if err != nil {
			fmt.Printf("Error fetching batch at offset %d: %v\n", offset, err)
			break
		}

		// No more records
		if len(records) == 0 {
			break
		}

		// Write batch to CSV
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
			recordsWritten++

			// Flush periodically for streaming
			if recordsWritten%10000 == 0 {
				writer.Flush()
				if f, ok := c.Writer.(http.Flusher); ok {
					f.Flush()
				}
			}
		}

		offset += batchSize

		// If we got fewer records than batch size, we're done
		if len(records) < batchSize {
			break
		}
	}

	writer.Flush()
	fmt.Printf("CSV export completed: %d records\n", recordsWritten)
}

// ExportJSON exports data records to JSON with streaming for massive datasets
func (h *ExportHandler) ExportJSON(c *gin.Context) {
	// Get optional category filter
	category := c.Query("category")

	// Set headers for JSON download
	filename := fmt.Sprintf("data_export_%s.json", time.Now().Format("20060102_150405"))
	c.Header("Content-Type", "application/json")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))
	c.Header("Transfer-Encoding", "chunked")

	// Manually write JSON array with streaming
	c.Writer.Write([]byte("[\n"))

	// Stream records in batches
	batchSize := config.AppConfig.ExportBatchSize
	offset := 0
	recordsWritten := 0
	encoder := json.NewEncoder(c.Writer)

	for {
		var records []models.DataRecord
		var err error

		// Fetch batch from repository
		if category != "" {
			records, err = h.dataRepo.FindByCategoryPaginated(category, offset, batchSize)
		} else {
			records, _, err = h.dataRepo.FindAllNoPagination(offset, batchSize)
		}

		if err != nil {
			fmt.Printf("Error fetching batch at offset %d: %v\n", offset, err)
			break
		}

		// No more records
		if len(records) == 0 {
			break
		}

		// Write batch to JSON
		for i, record := range records {
			if recordsWritten > 0 || i > 0 {
				c.Writer.Write([]byte(",\n"))
			}

			if err := encoder.Encode(record); err != nil {
				continue
			}
			recordsWritten++

			// Flush periodically for streaming
			if recordsWritten%10000 == 0 {
				if f, ok := c.Writer.(http.Flusher); ok {
					f.Flush()
				}
			}
		}

		offset += batchSize

		// If we got fewer records than batch size, we're done
		if len(records) < batchSize {
			break
		}
	}

	c.Writer.Write([]byte("\n]"))

	if f, ok := c.Writer.(http.Flusher); ok {
		f.Flush()
	}

	fmt.Printf("JSON export completed: %d records\n", recordsWritten)
}

// ExportExcel exports data records to Excel (placeholder)
func (h *ExportHandler) ExportExcel(c *gin.Context) {
	c.JSON(http.StatusNotImplemented, gin.H{
		"message": "Excel export not yet implemented. Use CSV or JSON export for now.",
	})
}
