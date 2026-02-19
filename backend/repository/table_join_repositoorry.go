package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type TableJoinRepository struct {
	db *gorm.DB
}

func NewTableJoinRepository(db *gorm.DB) *TableJoinRepository {
	return &TableJoinRepository{db: db}
}

// FindAll retrieves all table join configurations
func (r *TableJoinRepository) FindAll() ([]*models.TableJoin, error) {
	var joins []*models.TableJoin
	err := r.db.Find(&joins).Error
	return joins, err
}

// FindByID retrieves a table join by ID
func (r *TableJoinRepository) FindByID(id uint) (*models.TableJoin, error) {
	var join models.TableJoin
	err := r.db.First(&join, id).Error
	return &join, err
}

// FindByName retrieves a table join by name
func (r *TableJoinRepository) FindByName(name string) (*models.TableJoin, error) {
	var join models.TableJoin
	err := r.db.Where("name = ?", name).First(&join).Error
	return &join, err
}

// Create creates a new table join configuration
func (r *TableJoinRepository) Create(join *models.TableJoin) error {
	return r.db.Create(join).Error
}

// Update updates an existing table join
func (r *TableJoinRepository) Update(join *models.TableJoin) error {
	return r.db.Save(join).Error
}

// Delete removes a table join by ID
func (r *TableJoinRepository) Delete(id uint) error {
	return r.db.Delete(&models.TableJoin{}, id).Error
}
