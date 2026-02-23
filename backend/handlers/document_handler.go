package handlers

import (
	"bufio"
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
)

type DocumentHandler struct {
	repo *repository.DocumentRepository
}

func NewDocumentHandler(repo *repository.DocumentRepository) *DocumentHandler {
	return &DocumentHandler{repo: repo}
}

// Upload handles massive document uploads with chunked streaming (supports up to 1TB)
func (h *DocumentHandler) Upload(c *gin.Context) {
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Validate file size (supports up to 1TB)
	if header.Size > config.AppConfig.MaxUploadSize {
		c.JSON(http.StatusBadRequest, gin.H{
			"error": fmt.Sprintf("File size exceeds maximum allowed size of %d bytes", config.AppConfig.MaxUploadSize),
		})
		return
	}

	// Validate file is not empty
	if header.Size == 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Cannot upload empty file"})
		return
	}

	// Create uploads directory if it doesn't exist
	uploadPath := config.AppConfig.UploadPath
	if err := os.MkdirAll(uploadPath, os.ModePerm); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create upload directory"})
		return
	}

	// Generate unique filename
	timestamp := time.Now().Format("20060102_150405")
	ext := filepath.Ext(header.Filename)
	filename := fmt.Sprintf("%s_%s%s", timestamp, strconv.FormatInt(time.Now().UnixNano(), 36), ext)
	filePath := filepath.Join(uploadPath, filename)

	// Save file to disk with chunked buffered writing for large files
	out, err := os.Create(filePath)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create file"})
		return
	}
	defer out.Close()

	// Use buffered writer for better performance with large files
	bufWriter := bufio.NewWriterSize(out, config.AppConfig.StreamBufferSize)
	defer bufWriter.Flush()

	// Stream file in chunks to handle massive files without loading into memory
	chunkSize := config.AppConfig.ChunkSizeBytes
	buffer := make([]byte, chunkSize)
	totalWritten := int64(0)

	for {
		n, err := file.Read(buffer)
		if err != nil && err != io.EOF {
			os.Remove(filePath) // Clean up on error
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to read uploaded file"})
			return
		}

		if n == 0 {
			break
		}

		written, err := bufWriter.Write(buffer[:n])
		if err != nil {
			os.Remove(filePath) // Clean up on error
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to write file"})
			return
		}

		totalWritten += int64(written)

		// Flush periodically for very large files
		if totalWritten%(chunkSize*10) == 0 {
			bufWriter.Flush()
		}
	}

	// Final flush
	if err := bufWriter.Flush(); err != nil {
		os.Remove(filePath) // Clean up on error
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to finalize file"})
		return
	}

	// Get optional fields from form
	category := c.PostForm("category")
	documentType := strings.ToLower(strings.TrimSpace(c.PostForm("document_type")))
	if documentType == "" {
		documentType = strings.TrimPrefix(strings.ToLower(ext), ".")
	}
	if documentType == "" {
		documentType = "other"
	}
	description := c.PostForm("description")
	
	// Get uploaded_by from form (frontend auto-fills with authenticated user's name)
	uploadedBy := strings.TrimSpace(c.PostForm("uploaded_by"))
	if uploadedBy == "" {
		// Fallback to context user if form is empty
		if contextUser := c.GetString("user"); contextUser != "" {
			uploadedBy = contextUser
		} else {
			uploadedBy = "anonymous"
		}
	}

	// Detect MIME type (use header value or default to application/octet-stream)
	mimeType := header.Header.Get("Content-Type")
	if mimeType == "" {
		mimeType = "application/octet-stream"
	}

	// Create document record
	document := &models.Document{
		FileName:     filename,
		OriginalName: header.Filename,
		FilePath:     filePath,
		FileSize:     totalWritten,
		FileType:     ext,
		MimeType:     mimeType,
		Category:     category,
		DocumentType: documentType,
		Description:  description,
		UploadedBy:   uploadedBy,
		Status:       "active",
	}

	if err := h.repo.Create(document); err != nil {
		// Clean up file if database insert fails
		os.Remove(filePath)
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to save document record"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message":  "File uploaded successfully",
		"document": document,
		"size_gb":  float64(totalWritten) / (1024 * 1024 * 1024),
	})
}

// Download handles massive document downloads with chunked streaming
func (h *DocumentHandler) Download(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	document, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Document not found"})
		return
	}

	// Check if file exists
	fileInfo, err := os.Stat(document.FilePath)
	if os.IsNotExist(err) {
		c.JSON(http.StatusNotFound, gin.H{"error": "File not found on disk"})
		return
	}

	// Open file for reading
	file, err := os.Open(document.FilePath)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to open file"})
		return
	}
	defer file.Close()

	// Set headers for chunked file download
	c.Header("Content-Description", "File Transfer")
	c.Header("Content-Transfer-Encoding", "binary")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", document.OriginalName))
	c.Header("Content-Type", document.MimeType)
	c.Header("Content-Length", strconv.FormatInt(fileInfo.Size(), 10))

	// Stream file in chunks for large files
	bufReader := bufio.NewReaderSize(file, config.AppConfig.StreamBufferSize)
	buffer := make([]byte, config.AppConfig.ChunkSizeBytes)

	for {
		n, err := bufReader.Read(buffer)
		if err != nil && err != io.EOF {
			fmt.Printf("Error reading file: %v\n", err)
			return
		}

		if n == 0 {
			break
		}

		if _, err := c.Writer.Write(buffer[:n]); err != nil {
			fmt.Printf("Error writing response: %v\n", err)
			return
		}

		// Flush periodically for large files
		if flusher, ok := c.Writer.(http.Flusher); ok {
			flusher.Flush()
		}
	}
}

// GetAll retrieves all documents with pagination
func (h *DocumentHandler) GetAll(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	category := c.Query("category")
	documentType := c.Query("document_type")

	documents, total, err := h.repo.FindAll(page, limit, category, documentType)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"data":        documents,
		"total":       total,
		"page":        page,
		"limit":       limit,
		"total_pages": (total + int64(limit) - 1) / int64(limit),
	})
}

// GetByID retrieves a single document by ID
func (h *DocumentHandler) GetByID(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	document, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Document not found"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": document})
}

// Delete deletes a document and its file
func (h *DocumentHandler) Delete(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid ID"})
		return
	}

	document, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Document not found"})
		return
	}

	// Delete file from disk
	if _, err := os.Stat(document.FilePath); err == nil {
		if delErr := os.Remove(document.FilePath); delErr != nil {
			// Log error but continue with database deletion
			fmt.Printf("Warning: Failed to delete file %s: %v\n", document.FilePath, delErr)
		}
	}

	// Delete database record (soft delete)
	if err := h.repo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "Document deleted successfully", "id": id})
}

// GetByCategory retrieves documents by category
func (h *DocumentHandler) GetByCategory(c *gin.Context) {
	category := c.Param("category")

	documents, err := h.repo.FindByCategory(category)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": documents})
}
