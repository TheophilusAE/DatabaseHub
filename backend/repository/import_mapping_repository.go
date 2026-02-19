package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type ImportMappingRepository struct {
	db *gorm.DB
}

func NewImportMappingRepository(db *gorm.DB) *ImportMappingRepository {
	return &ImportMappingRepository{db: db}
}

// FindAll retrieves all import mappings
func (r *ImportMappingRepository) FindAll() ([]*models.ImportMapping, error) {
	var mappings []*models.ImportMapping
	err := r.db.Find(&mappings).Error
	return mappings, err
}

// FindByID retrieves an import mapping by ID
func (r *ImportMappingRepository) FindByID(id uint) (*models.ImportMapping, error) {
	var mapping models.ImportMapping
	err := r.db.First(&mapping, id).Error
	return &mapping, err
}

// FindByTableConfig retrieves mappings for a specific table config
func (r *ImportMappingRepository) FindByTableConfig(tableConfigID uint) ([]*models.ImportMapping, error) {
	var mappings []*models.ImportMapping
	err := r.db.Where("table_config_id = ?", tableConfigID).Find(&mappings).Error
	return mappings, err
}

// FindByName retrieves an import mapping by name
func (r *ImportMappingRepository) FindByName(name string) (*models.ImportMapping, error) {
	var mapping models.ImportMapping
	err := r.db.Where("name = ?", name).First(&mapping).Error
	return &mapping, err
}

// Create creates a new import mapping
func (r *ImportMappingRepository) Create(mapping *models.ImportMapping) error {
	return r.db.Create(mapping).Error
}

// Update updates an existing import mapping
func (r *ImportMappingRepository) Update(mapping *models.ImportMapping) error {
	return r.db.Save(mapping).Error
}

// Delete removes an import mapping by ID
func (r *ImportMappingRepository) Delete(id uint) error {
	return r.db.Delete(&models.ImportMapping{}, id).Error
}
