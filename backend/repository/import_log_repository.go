package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type ImportLogRepository struct {
	db *gorm.DB
}

func NewImportLogRepository(db *gorm.DB) *ImportLogRepository {
	return &ImportLogRepository{db: db}
}

// Create creates a new import log
func (r *ImportLogRepository) Create(log *models.ImportLog) error {
	return r.db.Create(log).Error
}

// FindAll retrieves all import logs with pagination
func (r *ImportLogRepository) FindAll(page, limit int) ([]models.ImportLog, int64, error) {
	var logs []models.ImportLog
	var total int64

	offset := (page - 1) * limit

	if err := r.db.Model(&models.ImportLog{}).Count(&total).Error; err != nil {
		return nil, 0, err
	}

	if err := r.db.Offset(offset).Limit(limit).Order("created_at desc").Find(&logs).Error; err != nil {
		return nil, 0, err
	}

	return logs, total, nil
}

// FindByID retrieves an import log by ID
func (r *ImportLogRepository) FindByID(id uint) (*models.ImportLog, error) {
	var log models.ImportLog
	if err := r.db.First(&log, id).Error; err != nil {
		return nil, err
	}
	return &log, nil
}

// Update updates an import log
func (r *ImportLogRepository) Update(log *models.ImportLog) error {
	return r.db.Save(log).Error
}
