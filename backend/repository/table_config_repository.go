package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type TableConfigRepository struct {
	db *gorm.DB
}

func NewTableConfigRepository(db *gorm.DB) *TableConfigRepository {
	return &TableConfigRepository{db: db}
}

// FindAll returns all active table configurations
func (r *TableConfigRepository) FindAll() ([]*models.TableConfig, error) {
	var configs []*models.TableConfig
	err := r.db.Where("is_active = ?", true).Find(&configs).Error
	return configs, err
}

// FindByID finds a table config by ID
func (r *TableConfigRepository) FindByID(id uint) (*models.TableConfig, error) {
	var table models.TableConfig
	err := r.db.First(&table, id).Error
	if err != nil {
		return nil, err
	}
	return &table, nil
}

// FindByDatabaseAndTable finds by database name and table name
func (r *TableConfigRepository) FindByDatabaseAndTable(databaseName, tableName string) (*models.TableConfig, error) {
	var table models.TableConfig
	err := r.db.Where("database_name = ? AND table_name = ?", databaseName, tableName).First(&table).Error
	if err != nil {
		return nil, err
	}
	return &table, nil
}

// Create creates a new table configuration
func (r *TableConfigRepository) Create(table *models.TableConfig) error {
	return r.db.Create(table).Error
}

// Update updates an existing table configuration
func (r *TableConfigRepository) Update(table *models.TableConfig) error {
	return r.db.Save(table).Error
}

// Delete deletes a table configuration by ID
func (r *TableConfigRepository) Delete(id uint) error {
	return r.db.Delete(&models.TableConfig{}, id).Error
}

// GetAll returns all active table configurations (alias for FindAll)
func (r *TableConfigRepository) GetAll() ([]models.TableConfig, error) {
	var tables []models.TableConfig
	err := r.db.Where("is_active = ?", true).Find(&tables).Error
	return tables, err
}
