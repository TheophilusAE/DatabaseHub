package models

import (
	"time"

	"gorm.io/gorm"
)

// UserTablePermission represents which tables a user can access
type UserTablePermission struct {
	ID            uint           `gorm:"primaryKey" json:"id"`
	UserID        uint           `gorm:"not null;index:idx_user_table" json:"user_id"`
	TableConfigID uint           `gorm:"not null;index:idx_user_table" json:"table_config_id"`
	CanView       bool           `gorm:"default:true" json:"can_view"`
	CanEdit       bool           `gorm:"default:false" json:"can_edit"`
	CanDelete     bool           `gorm:"default:false" json:"can_delete"`
	CanExport     bool           `gorm:"default:false" json:"can_export"`
	CanImport     bool           `gorm:"default:false" json:"can_import"`
	CreatedAt     time.Time      `json:"created_at"`
	UpdatedAt     time.Time      `json:"updated_at"`
	DeletedAt     gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`

	// Relationships
	User        User        `gorm:"foreignKey:UserID;constraint:OnDelete:CASCADE" json:"user,omitempty"`
	TableConfig TableConfig `gorm:"foreignKey:TableConfigID;constraint:OnDelete:CASCADE" json:"table_config,omitempty"`
}

// TableName specifies the table name
func (UserTablePermission) TableName() string {
	return "user_table_permissions"
}

// UserTablePermissionResponse represents the API response
type UserTablePermissionResponse struct {
	ID            uint      `json:"id"`
	UserID        uint      `json:"user_id"`
	TableConfigID uint      `json:"table_config_id"`
	TableName     string    `json:"table_name"`
	CanView       bool      `json:"can_view"`
	CanEdit       bool      `json:"can_edit"`
	CanDelete     bool      `json:"can_delete"`
	CanExport     bool      `json:"can_export"`
	CanImport     bool      `json:"can_import"`
	CreatedAt     time.Time `json:"created_at"`
}
