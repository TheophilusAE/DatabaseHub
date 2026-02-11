package models

import (
	"time"

	"gorm.io/gorm"
)

// ImportLog tracks import operations
type ImportLog struct {
	ID           uint           `gorm:"primaryKey" json:"id"`
	FileName     string         `gorm:"size:255" json:"file_name"`
	ImportType   string         `gorm:"size:50;not null" json:"import_type"` // csv, json, excel, etc.
	TotalRecords int            `json:"total_records"`
	SuccessCount int            `json:"success_count"`
	FailureCount int            `json:"failure_count"`
	Status       string         `gorm:"size:50;default:'pending'" json:"status"` // pending, processing, completed, failed
	ErrorMessage string         `gorm:"type:text" json:"error_message,omitempty"`
	ImportedBy   string         `gorm:"size:100" json:"imported_by"`
	CreatedAt    time.Time      `json:"created_at"`
	UpdatedAt    time.Time      `json:"updated_at"`
	DeletedAt    gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for ImportLog
func (ImportLog) TableName() string {
	return "import_logs"
}
