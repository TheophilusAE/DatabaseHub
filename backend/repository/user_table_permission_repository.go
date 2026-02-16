package repository

import (
	"dataImportDashboard/models"
	"errors"

	"gorm.io/gorm"
)

// UserTablePermissionRepository handles database operations for user table permissions
type UserTablePermissionRepository struct {
	db *gorm.DB
}

// NewUserTablePermissionRepository creates a new repository
func NewUserTablePermissionRepository(db *gorm.DB) *UserTablePermissionRepository {
	return &UserTablePermissionRepository{db: db}
}

// GetUserPermissions retrieves all table permissions for a user
func (r *UserTablePermissionRepository) GetUserPermissions(userID uint) ([]models.UserTablePermission, error) {
	var permissions []models.UserTablePermission
	err := r.db.Preload("TableConfig").Where("user_id = ?", userID).Find(&permissions).Error
	return permissions, err
}

// GetPermissionByUserAndTable retrieves a specific permission
func (r *UserTablePermissionRepository) GetPermissionByUserAndTable(userID, tableConfigID uint) (*models.UserTablePermission, error) {
	var permission models.UserTablePermission
	err := r.db.Where("user_id = ? AND table_config_id = ?", userID, tableConfigID).First(&permission).Error
	if err != nil {
		return nil, err
	}
	return &permission, nil
}

// Create creates a new permission
func (r *UserTablePermissionRepository) Create(permission *models.UserTablePermission) error {
	// Check if permission already exists
	var existing models.UserTablePermission
	err := r.db.Where("user_id = ? AND table_config_id = ?", permission.UserID, permission.TableConfigID).First(&existing).Error
	if err == nil {
		return errors.New("permission already exists for this user and table")
	}

	return r.db.Create(permission).Error
}

// Update updates an existing permission
func (r *UserTablePermissionRepository) Update(permission *models.UserTablePermission) error {
	return r.db.Save(permission).Error
}

// Delete removes a permission
func (r *UserTablePermissionRepository) Delete(id uint) error {
	return r.db.Delete(&models.UserTablePermission{}, id).Error
}

// DeleteByUserAndTable removes a specific permission
func (r *UserTablePermissionRepository) DeleteByUserAndTable(userID, tableConfigID uint) error {
	return r.db.Where("user_id = ? AND table_config_id = ?", userID, tableConfigID).Delete(&models.UserTablePermission{}).Error
}

// HasTableAccess checks if a user has access to a table
func (r *UserTablePermissionRepository) HasTableAccess(userID, tableConfigID uint) (bool, error) {
	var count int64
	err := r.db.Model(&models.UserTablePermission{}).
		Where("user_id = ? AND table_config_id = ? AND can_view = ?", userID, tableConfigID, true).
		Count(&count).Error
	return count > 0, err
}

// GetAccessibleTables returns all table IDs that a user can access
func (r *UserTablePermissionRepository) GetAccessibleTables(userID uint) ([]uint, error) {
	var permissions []models.UserTablePermission
	err := r.db.Where("user_id = ? AND can_view = ?", userID, true).Find(&permissions).Error
	if err != nil {
		return nil, err
	}

	tableIDs := make([]uint, len(permissions))
	for i, p := range permissions {
		tableIDs[i] = p.TableConfigID
	}
	return tableIDs, nil
}

// BulkCreatePermissions creates multiple permissions for a user
func (r *UserTablePermissionRepository) BulkCreatePermissions(userID uint, tableConfigIDs []uint, permissions models.UserTablePermission) error {
	return r.db.Transaction(func(tx *gorm.DB) error {
		for _, tableID := range tableConfigIDs {
			perm := models.UserTablePermission{
				UserID:        userID,
				TableConfigID: tableID,
				CanView:       permissions.CanView,
				CanEdit:       permissions.CanEdit,
				CanDelete:     permissions.CanDelete,
				CanExport:     permissions.CanExport,
				CanImport:     permissions.CanImport,
			}

			// Check if exists
			var existing models.UserTablePermission
			err := tx.Where("user_id = ? AND table_config_id = ?", userID, tableID).First(&existing).Error
			if err == gorm.ErrRecordNotFound {
				// Create new
				if err := tx.Create(&perm).Error; err != nil {
					return err
				}
			} else if err == nil {
				// Update existing
				existing.CanView = permissions.CanView
				existing.CanEdit = permissions.CanEdit
				existing.CanDelete = permissions.CanDelete
				existing.CanExport = permissions.CanExport
				existing.CanImport = permissions.CanImport
				if err := tx.Save(&existing).Error; err != nil {
					return err
				}
			} else {
				return err
			}
		}
		return nil
	})
}

// RevokeAllUserPermissions removes all permissions for a user
func (r *UserTablePermissionRepository) RevokeAllUserPermissions(userID uint) error {
	return r.db.Where("user_id = ?", userID).Delete(&models.UserTablePermission{}).Error
}
