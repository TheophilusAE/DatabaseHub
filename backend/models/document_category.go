package models

import (
	"time"

	"gorm.io/gorm"
)

// DocumentCategory represents admin-managed document categories.
type DocumentCategory struct {
	ID        uint           `gorm:"primaryKey" json:"id"`
	Name      string         `gorm:"size:100;not null;uniqueIndex" json:"name"`
	CreatedAt time.Time      `json:"created_at"`
	UpdatedAt time.Time      `json:"updated_at"`
	DeletedAt gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for DocumentCategory.
func (DocumentCategory) TableName() string {
	return "document_categories"
}
