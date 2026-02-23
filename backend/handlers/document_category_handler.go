package handlers

import (
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"net/http"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"
)

type DocumentCategoryHandler struct {
	repo *repository.DocumentCategoryRepository
}

func NewDocumentCategoryHandler(repo *repository.DocumentCategoryRepository) *DocumentCategoryHandler {
	return &DocumentCategoryHandler{repo: repo}
}

type createDocumentCategoryRequest struct {
	Name string `json:"name"`
}

type updateDocumentCategoryRequest struct {
	Name string `json:"name"`
}

// GetAll returns all document categories.
func (h *DocumentCategoryHandler) GetAll(c *gin.Context) {
	categories, err := h.repo.FindAll()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": categories})
}

// Create creates a new document category (admin only route).
func (h *DocumentCategoryHandler) Create(c *gin.Context) {
	var req createDocumentCategoryRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid payload"})
		return
	}

	name := strings.TrimSpace(req.Name)
	if name == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Category name is required"})
		return
	}

	exists, err := h.repo.ExistsByName(name)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	if exists {
		c.JSON(http.StatusConflict, gin.H{"error": "Category already exists"})
		return
	}

	category := &models.DocumentCategory{Name: name}
	if err := h.repo.Create(category); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create category"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message":  "Category created successfully",
		"category": category,
	})
}

// Update renames an existing document category (admin only route).
func (h *DocumentCategoryHandler) Update(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid category ID"})
		return
	}

	category, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Category not found"})
		return
	}

	var req updateDocumentCategoryRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid payload"})
		return
	}

	name := strings.TrimSpace(req.Name)
	if name == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Category name is required"})
		return
	}

	if !strings.EqualFold(category.Name, name) {
		exists, err := h.repo.ExistsByName(name)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		if exists {
			c.JSON(http.StatusConflict, gin.H{"error": "Category already exists"})
			return
		}
	}

	oldName := category.Name
	if err := h.repo.UpdateName(uint(id), name); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to update category"})
		return
	}

	if oldName != name {
		if err := h.repo.ReassignDocumentCategory(oldName, name); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Category updated but failed to sync documents"})
			return
		}
	}

	updated, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Category updated but failed to reload category"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message":  "Category updated successfully",
		"category": updated,
	})
}

// Delete removes a document category (admin only route).
// Documents using this category are reassigned to "Other".
func (h *DocumentCategoryHandler) Delete(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid category ID"})
		return
	}

	category, err := h.repo.FindByID(uint(id))
	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Category not found"})
		return
	}

	if strings.EqualFold(category.Name, "Other") {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Default category 'Other' cannot be deleted"})
		return
	}

	otherExists, err := h.repo.ExistsByName("Other")
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	if !otherExists {
		otherCategory := &models.DocumentCategory{Name: "Other"}
		if err := h.repo.Create(otherCategory); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to prepare fallback category"})
			return
		}
	}

	documentCount, err := h.repo.CountDocumentsUsingCategory(category.Name)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	if err := h.repo.ReassignDocumentCategory(category.Name, "Other"); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to reassign documents before deletion"})
		return
	}

	if err := h.repo.DeleteByID(uint(id)); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to delete category"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message":              "Category deleted successfully",
		"reassigned_documents": documentCount,
		"reassigned_to":        "Other",
	})
}
