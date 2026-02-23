package models

import (
	"time"

	"gorm.io/gorm"
)

// Document represents an uploaded document/file
type Document struct {
	ID           uint           `gorm:"primaryKey" json:"id"`
	FileName     string         `gorm:"size:255;not null" json:"file_name"`
	OriginalName string         `gorm:"size:255;not null" json:"original_name"`
	FilePath     string         `gorm:"size:500;not null" json:"file_path"`
	FileSize     int64          `json:"file_size"`
	FileType     string         `gorm:"size:100" json:"file_type"`
	MimeType     string         `gorm:"size:100" json:"mime_type"`
	Category     string         `gorm:"size:100" json:"category"`
	DocumentType string         `gorm:"size:100;default:'other'" json:"document_type"`
	Description  string         `gorm:"type:text" json:"description"`
	UploadedBy   string         `gorm:"size:100" json:"uploaded_by"`
	Status       string         `gorm:"size:50;default:'active'" json:"status"`
	CreatedAt    time.Time      `json:"created_at"`
	UpdatedAt    time.Time      `json:"updated_at"`
	DeletedAt    gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for Document
func (Document) TableName() string {
	return "documents"
}
