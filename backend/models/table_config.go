package models

import (
	"time"

	"gorm.io/gorm"
)

// TableConfig represents configuration for a table that can be imported/exported
type TableConfig struct {
	ID           uint           `gorm:"primaryKey" json:"id"`
	Name         string         `gorm:"size:255;not null;uniqueIndex:idx_db_table" json:"name"`
	DatabaseName string         `gorm:"size:255;not null;uniqueIndex:idx_db_table" json:"database_name"` // Reference to connection name
	Table        string         `gorm:"size:255;not null;column:table_name" json:"table_name"`
	Description  string         `gorm:"type:text" json:"description"`
	Columns      string         `gorm:"type:text;not null" json:"columns"` // JSON string of column definitions
	PrimaryKey   string         `gorm:"size:255" json:"primary_key"`
	IsActive     bool           `gorm:"default:true" json:"is_active"`
	CreatedBy    string         `gorm:"size:100" json:"created_by"`
	CreatedAt    time.Time      `json:"created_at"`
	UpdatedAt    time.Time      `json:"updated_at"`
	DeletedAt    gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for TableConfig
func (TableConfig) TableName() string {
	return "table_configs"
}

// ColumnDefinition represents a column in a table
type ColumnDefinition struct {
	Name       string `json:"name"`
	Type       string `json:"type"` // varchar, int, float, text, datetime, etc.
	Size       int    `json:"size"` // For varchar, etc.
	Nullable   bool   `json:"nullable"`
	Default    string `json:"default"`
	IsPrimary  bool   `json:"is_primary"`
	IsUnique   bool   `json:"is_unique"`
	ForeignKey string `json:"foreign_key"` // Reference to another table.column
}

// TableJoin represents a join configuration between two tables
type TableJoin struct {
	ID            uint           `gorm:"primaryKey" json:"id"`
	Name          string         `gorm:"size:255;not null;unique" json:"name"`
	Description   string         `gorm:"type:text" json:"description"`
	LeftTableID   uint           `gorm:"not null" json:"left_table_id"`
	RightTableID  uint           `gorm:"not null" json:"right_table_id"`
	JoinType      string         `gorm:"size:50;not null" json:"join_type"`        // INNER, LEFT, RIGHT, FULL
	JoinCondition string         `gorm:"type:text;not null" json:"join_condition"` // e.g., "left.id = right.user_id"
	SelectColumns string         `gorm:"type:text" json:"select_columns"`          // JSON array of columns to select
	TargetTableID *uint          `json:"target_table_id,omitempty"`                // Optional: where to export the joined data
	IsActive      bool           `gorm:"default:true" json:"is_active"`
	CreatedBy     string         `gorm:"size:100" json:"created_by"`
	CreatedAt     time.Time      `json:"created_at"`
	UpdatedAt     time.Time      `json:"updated_at"`
	DeletedAt     gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`

	// Associations
	LeftTable   TableConfig  `gorm:"foreignKey:LeftTableID" json:"left_table,omitempty"`
	RightTable  TableConfig  `gorm:"foreignKey:RightTableID" json:"right_table,omitempty"`
	TargetTable *TableConfig `gorm:"foreignKey:TargetTableID" json:"target_table,omitempty"`
}

// TableName specifies the table name for TableJoin
func (TableJoin) TableName() string {
	return "table_joins"
}

// ImportMapping represents a mapping configuration for importing data
type ImportMapping struct {
	ID            uint           `gorm:"primaryKey" json:"id"`
	Name          string         `gorm:"size:255;not null;unique" json:"name"`
	Description   string         `gorm:"type:text" json:"description"`
	SourceFormat  string         `gorm:"size:50;not null" json:"source_format"` // csv, json, xml, etc.
	TableConfigID uint           `gorm:"not null" json:"table_config_id"`
	ColumnMapping string         `gorm:"type:text;not null" json:"column_mapping"` // JSON: source field -> table column mapping
	Transform     string         `gorm:"type:text" json:"transform"`               // JSON: transformation rules
	IsActive      bool           `gorm:"default:true" json:"is_active"`
	CreatedBy     string         `gorm:"size:100" json:"created_by"`
	CreatedAt     time.Time      `json:"created_at"`
	UpdatedAt     time.Time      `json:"updated_at"`
	DeletedAt     gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`

	// Association
	TableConfig TableConfig `gorm:"foreignKey:TableConfigID" json:"table_config,omitempty"`
}

// TableName specifies the table name for ImportMapping
func (ImportMapping) TableName() string {
	return "import_mappings"
}

// ExportConfig represents configuration for exporting data
type ExportConfig struct {
	ID           uint           `gorm:"primaryKey" json:"id"`
	Name         string         `gorm:"size:255;not null;unique" json:"name"`
	Description  string         `gorm:"type:text" json:"description"`
	SourceType   string         `gorm:"size:50;not null" json:"source_type"`   // table, join
	SourceID     uint           `gorm:"not null" json:"source_id"`             // TableConfigID or TableJoinID
	TargetFormat string         `gorm:"size:50;not null" json:"target_format"` // csv, json, xml, excel
	Filters      string         `gorm:"type:text" json:"filters"`              // JSON: WHERE conditions
	OrderBy      string         `gorm:"type:text" json:"order_by"`             // JSON: ORDER BY rules
	ColumnList   string         `gorm:"type:text" json:"column_list"`          // JSON: columns to export
	IsActive     bool           `gorm:"default:true" json:"is_active"`
	CreatedBy    string         `gorm:"size:100" json:"created_by"`
	CreatedAt    time.Time      `json:"created_at"`
	UpdatedAt    time.Time      `json:"updated_at"`
	DeletedAt    gorm.DeletedAt `gorm:"index" json:"deleted_at,omitempty"`
}

// TableName specifies the table name for ExportConfig
func (ExportConfig) TableName() string {
	return "export_configs"
}
