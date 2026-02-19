-- ====================================
-- Fix User Table Permissions
-- ====================================
-- This script adds user table permissions with correct column names

-- Clear existing permissions first
DELETE FROM user_table_permissions;

-- ====================================
-- USER TABLE PERMISSIONS (Fixed)
-- ====================================

-- Give all users full access to data_records
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, true, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE t.table_name = 'data_records'
ON CONFLICT DO NOTHING;

-- Give John Smith view/edit/import access to customers and products
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, false, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'john.smith@example.com' AND t.table_name IN ('customers', 'products')
ON CONFLICT DO NOTHING;

-- Give Sarah Johnson full access to sales and orders
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, true, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'sarah.johnson@example.com' AND t.table_name IN ('sales', 'orders')
ON CONFLICT DO NOTHING;

-- Give Mike Davis view/edit/import access to employees and inventory (no delete)
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, false, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'mike.davis@example.com' AND t.table_name IN ('employees', 'inventory')
ON CONFLICT DO NOTHING;

-- Give Emily Chen view-only access to all tables
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, false, false, true, false, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'emily.chen@example.com'
ON CONFLICT DO NOTHING;

-- Display results
DO $$
DECLARE
    permission_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO permission_count FROM user_table_permissions;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'User Table Permissions Fixed!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Total Permissions: %', permission_count;
    RAISE NOTICE '';
    RAISE NOTICE 'Permission Summary:';
    RAISE NOTICE '  - Admin: Has full admin access (no specific permissions needed)';
    RAISE NOTICE '  - All Users: Full access to data_records';
    RAISE NOTICE '  - John Smith: View/Edit/Import customers & products';
    RAISE NOTICE '  - Sarah Johnson: Full access to sales & orders';
    RAISE NOTICE '  - Mike Davis: View/Edit/Import employees & inventory';
    RAISE NOTICE '  - Emily Chen: View-only access to all tables';
    RAISE NOTICE '========================================';
END $$;
