package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type DataRecordRepository struct {
	db *gorm.DB
}

func NewDataRecordRepository(db *gorm.DB) *DataRecordRepository {
	return &DataRecordRepository{db: db}
}

// Create creates a new data record
func (r *DataRecordRepository) Create(record *models.DataRecord) error {
	return r.db.Create(record).Error
}

// CreateBatch creates multiple data records in optimized batches
func (r *DataRecordRepository) CreateBatch(records []models.DataRecord) error {
	// Use larger batch size for better performance with large datasets
	batchSize := 1000 // Optimized for large imports
	if len(records) < 100 {
		batchSize = 50
	} else if len(records) < 1000 {
		batchSize = 250
	}
	return r.db.CreateInBatches(records, batchSize).Error
}

// FindAll retrieves all data records with pagination
func (r *DataRecordRepository) FindAll(page, limit int) ([]models.DataRecord, int64, error) {
	var records []models.DataRecord
	var total int64

	offset := (page - 1) * limit

	if err := r.db.Model(&models.DataRecord{}).Count(&total).Error; err != nil {
		return nil, 0, err
	}

	if err := r.db.Offset(offset).Limit(limit).Order("created_at desc").Find(&records).Error; err != nil {
		return nil, 0, err
	}

	return records, total, nil
}

// FindByID retrieves a data record by ID
func (r *DataRecordRepository) FindByID(id uint) (*models.DataRecord, error) {
	var record models.DataRecord
	if err := r.db.First(&record, id).Error; err != nil {
		return nil, err
	}
	return &record, nil
}

// Update updates a data record
func (r *DataRecordRepository) Update(record *models.DataRecord) error {
	return r.db.Save(record).Error
}

// Delete soft deletes a data record
func (r *DataRecordRepository) Delete(id uint) error {
	return r.db.Delete(&models.DataRecord{}, id).Error
}

// FindByCategory retrieves data records by category
func (r *DataRecordRepository) FindByCategory(category string) ([]models.DataRecord, error) {
	var records []models.DataRecord
	if err := r.db.Where("category = ?", category).Find(&records).Error; err != nil {
		return nil, err
	}
	return records, nil
}

// DeleteAll deletes all data records (for testing/cleanup)
func (r *DataRecordRepository) DeleteAll() error {
	return r.db.Exec("DELETE FROM data_records").Error
}

// FindAllNoPagination retrieves data records with offset/limit but no total count (for streaming exports)
func (r *DataRecordRepository) FindAllNoPagination(offset, limit int) ([]models.DataRecord, int64, error) {
	var records []models.DataRecord

	if err := r.db.Offset(offset).Limit(limit).Order("id asc").Find(&records).Error; err != nil {
		return nil, 0, err
	}

	return records, 0, nil
}

// FindByCategoryPaginated retrieves data records by category with pagination (for streaming exports)
func (r *DataRecordRepository) FindByCategoryPaginated(category string, offset, limit int) ([]models.DataRecord, error) {
	var records []models.DataRecord
	if err := r.db.Where("category = ?", category).Offset(offset).Limit(limit).Order("id asc").Find(&records).Error; err != nil {
		return nil, err
	}
	return records, nil
}

// CreateBatchOptimized creates multiple data records with optimized transaction handling
func (r *DataRecordRepository) CreateBatchOptimized(records []models.DataRecord, batchSize int) error {
	return r.db.CreateInBatches(records, batchSize).Error
}
