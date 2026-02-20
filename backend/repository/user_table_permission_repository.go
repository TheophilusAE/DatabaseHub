package repository

import (
	"dataImportDashboard/models"

	"gorm.io/gorm"
)

type UserTablePermissionRepository struct {
	db *gorm.DB
}

func NewUserTablePermissionRepository(db *gorm.DB) *UserTablePermissionRepository {
	return &UserTablePermissionRepository{db: db}
}

// ✅ REMOVED local UserTablePermission struct - now using models.UserTablePermission
// This ensures JSON tags like `json:"table_config_id"` are properly serialized

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
// ✅ Now returns models.UserTablePermission so JSON tags work correctly
func (r *UserTablePermissionRepository) GetUserPermissions(userID uint) ([]models.UserTablePermission, error) {
	var permissions []models.UserTablePermission
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
// ✅ Now uses models.UserTablePermission for proper JSON serialization
func (r *UserTablePermissionRepository) AssignPermission(userID, tableConfigID uint, canView, canEdit, canDelete, canExport, canImport bool) error {
	permission := models.UserTablePermission{
		UserID: userID, TableConfigID: tableConfigID,
		CanView: canView, CanEdit: canEdit, CanDelete: canDelete,
		CanExport: canExport, CanImport: canImport,
	}
	return r.db.Create(&permission).Error
}

// BulkAssignPermissions assigns multiple permissions
// ✅ Now uses models.UserTablePermission for proper JSON serialization
func (r *UserTablePermissionRepository) BulkAssignPermissions(userID uint, tableConfigIDs []uint, canView, canEdit, canDelete, canExport, canImport bool) error {
	var permissions []models.UserTablePermission
	for _, tid := range tableConfigIDs {
		permissions = append(permissions, models.UserTablePermission{
			UserID: userID, TableConfigID: tid,
			CanView: canView, CanEdit: canEdit, CanDelete: canDelete,
			CanExport: canExport, CanImport: canImport,
		})
	}
	return r.db.CreateInBatches(&permissions, 100).Error
}

// RevokePermission removes a specific permission
func (r *UserTablePermissionRepository) RevokePermission(userID, tableConfigID uint) error {
	return r.db.Where("user_id = ? AND table_config_id = ?", userID, tableConfigID).Delete(&models.UserTablePermission{}).Error
}

// RevokeAllPermissions removes all permissions for a user
func (r *UserTablePermissionRepository) RevokeAllPermissions(userID uint) error {
	return r.db.Where("user_id = ?", userID).Delete(&models.UserTablePermission{}).Error
}

// ✅ Optional: Add a helper to get a single permission (useful for debugging)
func (r *UserTablePermissionRepository) GetPermission(userID, tableConfigID uint) (*models.UserTablePermission, error) {
	var perm models.UserTablePermission
	err := r.db.Where("user_id = ? AND table_config_id = ?", userID, tableConfigID).First(&perm).Error
	if err != nil {
		return nil, err
	}
	return &perm, nil
}
