package models

import (
	"time"

	"gorm.io/gorm"
)

// DataRecord represents a generic data record in the database
type DataRecord struct {
	ID          uint           `gorm:"primaryKey" json:"id"`
	Name        string         `gorm:"size:255;not null" json:"name"`
	Description string         `gorm:"type:text" json:"description"`
	Category    string         `gorm:"size:100" json:"category"`
	Value       float64        `json:"value"`
	Status      string         `gorm:"size:50;default:'active'" json:"status"`
	Metadata    *string        `gorm:"type:text" json:"metadata,omitempty"`
	CreatedAt   time.Time      `json:"created_at"`
	UpdatedAt   time.Time      `json:"updated_at"`
	DeletedAt   gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for DataRecord
func (DataRecord) TableName() string {
	return "data_records"
}
