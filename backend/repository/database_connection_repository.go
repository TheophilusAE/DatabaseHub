package repository

import (
	"dataImportDashboard/config"
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type DatabaseConnectionRepository struct {
	db *gorm.DB
}

func NewDatabaseConnectionRepository(db *gorm.DB) *DatabaseConnectionRepository {
	return &DatabaseConnectionRepository{db: db}
}

func (r *DatabaseConnectionRepository) Upsert(conn *config.DatabaseConnection) error {
	var existing models.DatabaseConnectionConfig
	err := r.db.Where("name = ?", conn.Name).First(&existing).Error
	if err != nil {
		if err == gorm.ErrRecordNotFound {
			record := models.DatabaseConnectionConfig{
				Name:     conn.Name,
				Type:     conn.Type,
				Host:     conn.Host,
				Port:     conn.Port,
				User:     conn.User,
				Password: conn.Password,
				DBName:   conn.DBName,
				SSLMode:  conn.SSLMode,
				IsActive: true,
			}
			return r.db.Create(&record).Error
		}
		return err
	}

	existing.Type = conn.Type
	existing.Host = conn.Host
	existing.Port = conn.Port
	existing.User = conn.User
	existing.Password = conn.Password
	existing.DBName = conn.DBName
	existing.SSLMode = conn.SSLMode
	existing.IsActive = true

	return r.db.Save(&existing).Error
}

func (r *DatabaseConnectionRepository) FindAllActive() ([]models.DatabaseConnectionConfig, error) {
	var records []models.DatabaseConnectionConfig
	err := r.db.Where("is_active = ?", true).Find(&records).Error
	return records, err
}

func (r *DatabaseConnectionRepository) DeleteByName(name string) error {
	return r.db.Where("name = ?", name).Delete(&models.DatabaseConnectionConfig{}).Error
}
