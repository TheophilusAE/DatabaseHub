package repository

import (
	"dataImportDashboard/models"
	"fmt"

	"gorm.io/gorm"
)

// TableConfigRepository handles operations for table configurations
type TableConfigRepository struct {
	db *gorm.DB
}

func NewTableConfigRepository(db *gorm.DB) *TableConfigRepository {
	return &TableConfigRepository{db: db}
}

// Create creates a new table configuration
func (r *TableConfigRepository) Create(config *models.TableConfig) error {
	return r.db.Create(config).Error
}

// FindAll retrieves all active table configurations
func (r *TableConfigRepository) FindAll() ([]models.TableConfig, error) {
	var configs []models.TableConfig
	if err := r.db.Where("is_active = ?", true).Find(&configs).Error; err != nil {
		return nil, err
	}
	return configs, nil
}

// FindByID retrieves a table configuration by ID
func (r *TableConfigRepository) FindByID(id uint) (*models.TableConfig, error) {
	var config models.TableConfig
	if err := r.db.First(&config, id).Error; err != nil {
		return nil, err
	}
	return &config, nil
}

// GetByID is an alias for FindByID for consistency
func (r *TableConfigRepository) GetByID(id uint) (*models.TableConfig, error) {
	return r.FindByID(id)
}

// GetByIDs retrieves multiple table configurations by IDs
func (r *TableConfigRepository) GetByIDs(ids []uint, result *[]models.TableConfig) error {
	return r.db.Where("id IN ? AND is_active = ?", ids, true).Find(result).Error
}

// FindByName retrieves a table configuration by name
func (r *TableConfigRepository) FindByName(name string) (*models.TableConfig, error) {
	var config models.TableConfig
	if err := r.db.Where("name = ? AND is_active = ?", name, true).First(&config).Error; err != nil {
		return nil, err
	}
	return &config, nil
}

// FindByDatabase retrieves all table configurations for a specific database
func (r *TableConfigRepository) FindByDatabase(dbName string) ([]models.TableConfig, error) {
	var configs []models.TableConfig
	if err := r.db.Where("database_name = ? AND is_active = ?", dbName, true).Find(&configs).Error; err != nil {
		return nil, err
	}
	return configs, nil
}

// FindByDatabaseAndTable retrieves table configurations for a specific database and table
func (r *TableConfigRepository) FindByDatabaseAndTable(dbName string, tableName string) ([]models.TableConfig, error) {
	var configs []models.TableConfig
	if err := r.db.Where("database_name = ? AND table_name = ? AND is_active = ?", dbName, tableName, true).Find(&configs).Error; err != nil {
		return nil, err
	}
	return configs, nil
}

// Update updates a table configuration
func (r *TableConfigRepository) Update(config *models.TableConfig) error {
	return r.db.Save(config).Error
}

// Delete soft deletes a table configuration
func (r *TableConfigRepository) Delete(id uint) error {
	return r.db.Delete(&models.TableConfig{}, id).Error
}

// TableJoinRepository handles operations for table joins
type TableJoinRepository struct {
	db *gorm.DB
}

func NewTableJoinRepository(db *gorm.DB) *TableJoinRepository {
	return &TableJoinRepository{db: db}
}

// Create creates a new table join configuration
func (r *TableJoinRepository) Create(join *models.TableJoin) error {
	return r.db.Create(join).Error
}

// FindAll retrieves all active table joins with associations
func (r *TableJoinRepository) FindAll() ([]models.TableJoin, error) {
	var joins []models.TableJoin
	if err := r.db.Preload("LeftTable").Preload("RightTable").Preload("TargetTable").
		Where("is_active = ?", true).Find(&joins).Error; err != nil {
		return nil, err
	}
	return joins, nil
}

// FindByID retrieves a table join by ID with associations
func (r *TableJoinRepository) FindByID(id uint) (*models.TableJoin, error) {
	var join models.TableJoin
	if err := r.db.Preload("LeftTable").Preload("RightTable").Preload("TargetTable").
		First(&join, id).Error; err != nil {
		return nil, err
	}
	return &join, nil
}

// FindByName retrieves a table join by name
func (r *TableJoinRepository) FindByName(name string) (*models.TableJoin, error) {
	var join models.TableJoin
	if err := r.db.Preload("LeftTable").Preload("RightTable").Preload("TargetTable").
		Where("name = ? AND is_active = ?", name, true).First(&join).Error; err != nil {
		return nil, err
	}
	return &join, nil
}

// Update updates a table join configuration
func (r *TableJoinRepository) Update(join *models.TableJoin) error {
	return r.db.Save(join).Error
}

// Delete soft deletes a table join
func (r *TableJoinRepository) Delete(id uint) error {
	return r.db.Delete(&models.TableJoin{}, id).Error
}

// ImportMappingRepository handles operations for import mappings
type ImportMappingRepository struct {
	db *gorm.DB
}

func NewImportMappingRepository(db *gorm.DB) *ImportMappingRepository {
	return &ImportMappingRepository{db: db}
}

// Create creates a new import mapping
func (r *ImportMappingRepository) Create(mapping *models.ImportMapping) error {
	return r.db.Create(mapping).Error
}

// FindAll retrieves all active import mappings with associations
func (r *ImportMappingRepository) FindAll() ([]models.ImportMapping, error) {
	var mappings []models.ImportMapping
	if err := r.db.Preload("TableConfig").Where("is_active = ?", true).Find(&mappings).Error; err != nil {
		return nil, err
	}
	return mappings, nil
}

// FindByID retrieves an import mapping by ID
func (r *ImportMappingRepository) FindByID(id uint) (*models.ImportMapping, error) {
	var mapping models.ImportMapping
	if err := r.db.Preload("TableConfig").First(&mapping, id).Error; err != nil {
		return nil, err
	}
	return &mapping, nil
}

// FindByName retrieves an import mapping by name
func (r *ImportMappingRepository) FindByName(name string) (*models.ImportMapping, error) {
	var mapping models.ImportMapping
	if err := r.db.Preload("TableConfig").
		Where("name = ? AND is_active = ?", name, true).First(&mapping).Error; err != nil {
		return nil, err
	}
	return &mapping, nil
}

// Update updates an import mapping
func (r *ImportMappingRepository) Update(mapping *models.ImportMapping) error {
	return r.db.Save(mapping).Error
}

// Delete soft deletes an import mapping
func (r *ImportMappingRepository) Delete(id uint) error {
	return r.db.Delete(&models.ImportMapping{}, id).Error
}

// ExportConfigRepository handles operations for export configurations
type ExportConfigRepository struct {
	db *gorm.DB
}

func NewExportConfigRepository(db *gorm.DB) *ExportConfigRepository {
	return &ExportConfigRepository{db: db}
}

// Create creates a new export config
func (r *ExportConfigRepository) Create(config *models.ExportConfig) error {
	return r.db.Create(config).Error
}

// FindAll retrieves all active export configs
func (r *ExportConfigRepository) FindAll() ([]models.ExportConfig, error) {
	var configs []models.ExportConfig
	if err := r.db.Where("is_active = ?", true).Find(&configs).Error; err != nil {
		return nil, err
	}
	return configs, nil
}

// FindByID retrieves an export config by ID
func (r *ExportConfigRepository) FindByID(id uint) (*models.ExportConfig, error) {
	var config models.ExportConfig
	if err := r.db.First(&config, id).Error; err != nil {
		return nil, err
	}
	return &config, nil
}

// FindByName retrieves an export config by name
func (r *ExportConfigRepository) FindByName(name string) (*models.ExportConfig, error) {
	var config models.ExportConfig
	if err := r.db.Where("name = ? AND is_active = ?", name, true).First(&config).Error; err != nil {
		return nil, err
	}
	return &config, nil
}

// Update updates an export config
func (r *ExportConfigRepository) Update(config *models.ExportConfig) error {
	return r.db.Save(config).Error
}

// Delete soft deletes an export config
func (r *ExportConfigRepository) Delete(id uint) error {
	return r.db.Delete(&models.ExportConfig{}, id).Error
}

// DynamicTableRepository provides operations on any table using raw queries
type DynamicTableRepository struct {
	db *gorm.DB
}

func NewDynamicTableRepository(db *gorm.DB) *DynamicTableRepository {
	return &DynamicTableRepository{db: db}
}

// InsertBatch inserts a batch of records into a table
func (r *DynamicTableRepository) InsertBatch(tableName string, columns []string, values [][]interface{}) error {
	if len(values) == 0 {
		return nil
	}

	// Build INSERT query
	query := fmt.Sprintf("INSERT INTO %s (%s) VALUES ", tableName, joinColumns(columns))

	placeholders := make([]string, len(values))
	allValues := make([]interface{}, 0, len(values)*len(columns))

	for i := range values {
		placeholderGroup := make([]string, len(columns))
		for j := range columns {
			placeholderGroup[j] = "?"
			allValues = append(allValues, values[i][j])
		}
		placeholders[i] = "(" + joinStrings(placeholderGroup, ", ") + ")"
	}

	query += joinStrings(placeholders, ", ")

	return r.db.Exec(query, allValues...).Error
}

// SelectWithJoin executes a SELECT query with JOIN
func (r *DynamicTableRepository) SelectWithJoin(query string, args ...interface{}) ([]map[string]interface{}, error) {
	var results []map[string]interface{}
	if err := r.db.Raw(query, args...).Scan(&results).Error; err != nil {
		return nil, err
	}
	return results, nil
}

// Helper functions
func joinColumns(columns []string) string {
	return joinStrings(columns, ", ")
}

func joinStrings(strs []string, sep string) string {
	if len(strs) == 0 {
		return ""
	}
	result := strs[0]
	for i := 1; i < len(strs); i++ {
		result += sep + strs[i]
	}
	return result
}
