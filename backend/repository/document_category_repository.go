package repository

import (
	"dataImportDashboard/models"
	"errors"
	"strings"

	"gorm.io/gorm"
)

type DocumentCategoryRepository struct {
	db *gorm.DB
}

var defaultDocumentCategories = []string{"Class A", "Class B", "Class C", "Other"}

func NewDocumentCategoryRepository(db *gorm.DB) *DocumentCategoryRepository {
	return &DocumentCategoryRepository{db: db}
}

func (r *DocumentCategoryRepository) FindAll() ([]models.DocumentCategory, error) {
	var categories []models.DocumentCategory
	if err := r.db.Order("name asc").Find(&categories).Error; err != nil {
		return nil, err
	}
	return categories, nil
}

func (r *DocumentCategoryRepository) Create(category *models.DocumentCategory) error {
	category.Name = strings.TrimSpace(category.Name)
	return r.db.Create(category).Error
}

func (r *DocumentCategoryRepository) ExistsByName(name string) (bool, error) {
	var count int64
	if err := r.db.Model(&models.DocumentCategory{}).Where("LOWER(name) = LOWER(?)", strings.TrimSpace(name)).Count(&count).Error; err != nil {
		return false, err
	}
	return count > 0, nil
}

func (r *DocumentCategoryRepository) FindByID(id uint) (*models.DocumentCategory, error) {
	var category models.DocumentCategory
	if err := r.db.First(&category, id).Error; err != nil {
		return nil, err
	}
	return &category, nil
}

func (r *DocumentCategoryRepository) UpdateName(id uint, name string) error {
	return r.db.Model(&models.DocumentCategory{}).Where("id = ?", id).Update("name", strings.TrimSpace(name)).Error
}

func (r *DocumentCategoryRepository) CountDocumentsUsingCategory(name string) (int64, error) {
	var count int64
	if err := r.db.Model(&models.Document{}).Where("category = ?", name).Count(&count).Error; err != nil {
		return 0, err
	}
	return count, nil
}

func (r *DocumentCategoryRepository) ReassignDocumentCategory(fromCategory, toCategory string) error {
	return r.db.Model(&models.Document{}).Where("category = ?", fromCategory).Update("category", toCategory).Error
}

func (r *DocumentCategoryRepository) DeleteByID(id uint) error {
	return r.db.Delete(&models.DocumentCategory{}, id).Error
}

func (r *DocumentCategoryRepository) EnsureDefaults() error {
	for _, name := range defaultDocumentCategories {
		normalizedName := strings.TrimSpace(name)

		var existing models.DocumentCategory
		err := r.db.Unscoped().Where("LOWER(name) = LOWER(?)", normalizedName).First(&existing).Error
		if err == nil {
			if existing.DeletedAt.Valid {
				if restoreErr := r.db.Unscoped().Model(&models.DocumentCategory{}).Where("id = ?", existing.ID).Updates(map[string]interface{}{
					"name":       normalizedName,
					"deleted_at": nil,
				}).Error; restoreErr != nil {
					return restoreErr
				}
			}
			continue
		}

		if !errors.Is(err, gorm.ErrRecordNotFound) {
			return err
		}

		category := &models.DocumentCategory{Name: normalizedName}
		if createErr := r.Create(category); createErr != nil {
			lowerErr := strings.ToLower(createErr.Error())
			if strings.Contains(lowerErr, "duplicate key") || strings.Contains(lowerErr, "unique constraint") {
				continue
			}
			return createErr
		}
	}

	return nil
}
