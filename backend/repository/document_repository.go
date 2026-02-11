package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type DocumentRepository struct {
	db *gorm.DB
}

func NewDocumentRepository(db *gorm.DB) *DocumentRepository {
	return &DocumentRepository{db: db}
}

// Create creates a new document record
func (r *DocumentRepository) Create(document *models.Document) error {
	return r.db.Create(document).Error
}

// FindAll retrieves all documents with pagination
func (r *DocumentRepository) FindAll(page, limit int) ([]models.Document, int64, error) {
	var documents []models.Document
	var total int64

	offset := (page - 1) * limit

	if err := r.db.Model(&models.Document{}).Count(&total).Error; err != nil {
		return nil, 0, err
	}

	if err := r.db.Offset(offset).Limit(limit).Order("created_at desc").Find(&documents).Error; err != nil {
		return nil, 0, err
	}

	return documents, total, nil
}

// FindByID retrieves a document by ID
func (r *DocumentRepository) FindByID(id uint) (*models.Document, error) {
	var document models.Document
	if err := r.db.First(&document, id).Error; err != nil {
		return nil, err
	}
	return &document, nil
}

// Update updates a document record
func (r *DocumentRepository) Update(document *models.Document) error {
	return r.db.Save(document).Error
}

// Delete soft deletes a document record
func (r *DocumentRepository) Delete(id uint) error {
	return r.db.Delete(&models.Document{}, id).Error
}

// FindByCategory retrieves documents by category
func (r *DocumentRepository) FindByCategory(category string) ([]models.Document, error) {
	var documents []models.Document
	if err := r.db.Where("category = ?", category).Find(&documents).Error; err != nil {
		return nil, err
	}
	return documents, nil
}
