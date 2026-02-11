package handlers

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

type DocumentHandler struct {
	repo *repository.DocumentRepository
}

func NewDocumentHandler(repo *repository.DocumentRepository) *DocumentHandler {
	return &DocumentHandler{repo: repo}
}

// Upload handles document upload - supports all file types
func (h *DocumentHandler) Upload(c *gin.Context) {
	file, header, err := c.Request.FormFile("file")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "No file uploaded"})
		return
	}
	defer file.Close()

	// Validate file size
	if header.Size > config.AppConfig.MaxUploadSize {
		c.JSON(http.StatusBadRequest, gin.H{"error": "File size exceeds maximum allowed size"})
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

	// Save file to disk
	out, err := os.Create(filePath)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to save file"})
		return
	}
	defer out.Close()

	if _, err := io.Copy(out, file); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to save file"})
		return
	}

	// Get optional fields from form
	category := c.PostForm("category")
	description := c.PostForm("description")
	uploadedBy := c.GetString("user")
	if uploadedBy == "" {
		uploadedBy = "anonymous"
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
		FileSize:     header.Size,
		FileType:     ext,
		MimeType:     mimeType,
		Category:     category,
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
	})
}

// Download handles document download
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
	if _, err := os.Stat(document.FilePath); os.IsNotExist(err) {
		c.JSON(http.StatusNotFound, gin.H{"error": "File not found on disk"})
		return
	}

	// Set headers for file download
	c.Header("Content-Description", "File Transfer")
	c.Header("Content-Transfer-Encoding", "binary")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", document.OriginalName))
	c.Header("Content-Type", document.MimeType)

	c.File(document.FilePath)
}

// GetAll retrieves all documents with pagination
func (h *DocumentHandler) GetAll(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))

	documents, total, err := h.repo.FindAll(page, limit)
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
		os.Remove(document.FilePath)
	}

	// Delete database record
	if err := h.repo.Delete(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "Document deleted successfully"})
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
