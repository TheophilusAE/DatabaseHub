package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type ExportConfigRepository struct {
	db *gorm.DB
}

func NewExportConfigRepository(db *gorm.DB) *ExportConfigRepository {
	return &ExportConfigRepository{db: db}
}

// FindAll retrieves all export configurations
func (r *ExportConfigRepository) FindAll() ([]*models.ExportConfig, error) {
	var configs []*models.ExportConfig
	err := r.db.Find(&configs).Error
	return configs, err
}

// FindByID retrieves an export configuration by ID
func (r *ExportConfigRepository) FindByID(id uint) (*models.ExportConfig, error) {
	var config models.ExportConfig
	err := r.db.First(&config, id).Error
	return &config, err
}

// FindByName retrieves an export configuration by name
func (r *ExportConfigRepository) FindByName(name string) (*models.ExportConfig, error) {
	var config models.ExportConfig
	err := r.db.Where("name = ?", name).First(&config).Error
	return &config, err
}

// Create creates a new export configuration
func (r *ExportConfigRepository) Create(config *models.ExportConfig) error {
	return r.db.Create(config).Error
}

// Update updates an existing export configuration
func (r *ExportConfigRepository) Update(config *models.ExportConfig) error {
	return r.db.Save(config).Error
}

// Delete removes an export configuration by ID
func (r *ExportConfigRepository) Delete(id uint) error {
	return r.db.Delete(&models.ExportConfig{}, id).Error
}
