package models

import "time"

// DatabaseConnectionConfig stores persisted multi-database connection settings.
type DatabaseConnectionConfig struct {
	ID        uint      `gorm:"primaryKey" json:"id"`
	Name      string    `gorm:"size:255;not null;uniqueIndex" json:"name"`
	Type      string    `gorm:"size:50;not null" json:"type"`
	Host      string    `gorm:"size:255;not null" json:"host"`
	Port      string    `gorm:"size:20;not null" json:"port"`
	User      string    `gorm:"size:255;not null" json:"user"`
	Password  string    `gorm:"size:255" json:"password,omitempty"`
	DBName    string    `gorm:"size:255;not null;column:db_name" json:"db_name"`
	SSLMode   string    `gorm:"size:50" json:"ssl_mode,omitempty"`
	IsActive  bool      `gorm:"default:true" json:"is_active"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

func (DatabaseConnectionConfig) TableName() string {
	return "database_connection_configs"
}
