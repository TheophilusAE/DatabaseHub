package repository

import (
	"dataImportDashboard/models"
	"time"

	"gorm.io/gorm"
)

type UserTablePermissionRepository struct {
	db *gorm.DB
}

func NewUserTablePermissionRepository(db *gorm.DB) *UserTablePermissionRepository {
	return &UserTablePermissionRepository{db: db}
}

// UserTablePermission model
type UserTablePermission struct {
	ID            uint      `gorm:"primaryKey"`
	UserID        uint      `gorm:"not null;index"`
	TableConfigID uint      `gorm:"not null;index"`
	CanView       bool      `gorm:"default:true"`
	CanEdit       bool      `gorm:"default:false"`
	CanDelete     bool      `gorm:"default:false"`
	CanExport     bool      `gorm:"default:false"`
	CanImport     bool      `gorm:"default:false"`
	CreatedAt     time.Time `gorm:"autoCreateTime"`
	UpdatedAt     time.Time `gorm:"autoUpdateTime"`
}

// GetAccessibleTables returns tables the user has permission to access
func (r *UserTablePermissionRepository) GetAccessibleTables(userID uint) ([]models.TableConfig, error) {
	return r.GetTablesByUserID(userID)
}

// GetTablesByUserID returns only tables the user has permission to view
func (r *UserTablePermissionRepository) GetTablesByUserID(userID uint) ([]models.TableConfig, error) {
	var tables []models.TableConfig
	err := r.db.Table("table_configs").
		Select("table_configs.*").
		Joins("INNER JOIN user_table_permissions ON user_table_permissions.table_config_id = table_configs.id").
		Where("user_table_permissions.user_id = ? AND user_table_permissions.can_view = ?", userID, true).
		Find(&tables).Error
	return tables, err
}

// GetUserPermissions returns all permission records for a user
func (r *UserTablePermissionRepository) GetUserPermissions(userID uint) ([]UserTablePermission, error) {
	var permissions []UserTablePermission
	err := r.db.Where("user_id = ?", userID).Find(&permissions).Error
	return permissions, err
}

// HasTableAccess checks if user has access to a specific table
func (r *UserTablePermissionRepository) HasTableAccess(userID uint, tableID uint) (bool, error) {
	var count int64
	err := r.db.Table("user_table_permissions").
		Where("user_id = ? AND table_config_id = ? AND can_view = ?", userID, tableID, true).
		Count(&count).Error
	return count > 0, err
}

// AssignPermission assigns a single table permission
func (r *UserTablePermissionRepository) AssignPermission(userID, tableConfigID uint, canView, canEdit, canDelete, canExport, canImport bool) error {
	permission := UserTablePermission{
		UserID: userID, TableConfigID: tableConfigID,
		CanView: canView, CanEdit: canEdit, CanDelete: canDelete,
		CanExport: canExport, CanImport: canImport,
	}
	return r.db.Create(&permission).Error
}

// BulkAssignPermissions assigns multiple permissions
func (r *UserTablePermissionRepository) BulkAssignPermissions(userID uint, tableConfigIDs []uint, canView, canEdit, canDelete, canExport, canImport bool) error {
	var permissions []UserTablePermission
	for _, tid := range tableConfigIDs {
		permissions = append(permissions, UserTablePermission{
			UserID: userID, TableConfigID: tid,
			CanView: canView, CanEdit: canEdit, CanDelete: canDelete,
			CanExport: canExport, CanImport: canImport,
		})
	}
	return r.db.CreateInBatches(&permissions, 100).Error
}

// RevokePermission removes a specific permission
func (r *UserTablePermissionRepository) RevokePermission(userID, tableConfigID uint) error {
	return r.db.Where("user_id = ? AND table_config_id = ?", userID, tableConfigID).Delete(&UserTablePermission{}).Error
}

// RevokeAllPermissions removes all permissions for a user
func (r *UserTablePermissionRepository) RevokeAllPermissions(userID uint) error {
	return r.db.Where("user_id = ?", userID).Delete(&UserTablePermission{}).Error
}
