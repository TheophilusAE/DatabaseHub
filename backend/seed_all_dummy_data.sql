-- ==========================================================
-- Seed All Dashboard Listing Data (PostgreSQL, idempotent)
-- ==========================================================
-- Purpose:
--   Populate the app with non-production demo data so listing pages
--   (Users, Documents, Data Records, View Tables, Table Configs,
--   Joins, Import Mappings, Export Configs, History) show content.
--
-- Safe to re-run:
--   Uses ON CONFLICT and targeted cleanup of demo rows.

BEGIN;

-- ----------------------------------------------------------
-- 1) Users
-- ----------------------------------------------------------
-- Note: hash value is commonly used demo bcrypt hash in this project.
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES
  ('Administrator', 'admin@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
  ('Demo Analyst', 'analyst@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
  ('Demo Operator', 'operator@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
  ('Demo Finance', 'finance@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
  ('Demo ReadOnly', 'viewer@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW())
ON CONFLICT (email) DO UPDATE
SET name = EXCLUDED.name,
    role = EXCLUDED.role,
    updated_at = NOW();

-- ----------------------------------------------------------
-- 2) Core listing tables (data_records, documents, import_logs)
-- ----------------------------------------------------------
DELETE FROM data_records WHERE name LIKE 'Demo %';
INSERT INTO data_records (name, description, category, value, status, metadata, created_at, updated_at)
VALUES
  ('Demo Revenue Snapshot', 'Monthly revenue KPI row for dashboard testing', 'finance', 182340.75, 'active', '{"source":"seed","period":"2026-01"}', NOW(), NOW()),
  ('Demo User Growth', 'New user signups over last 30 days', 'growth', 1240.00, 'active', '{"source":"seed","metric":"signups_30d"}', NOW(), NOW()),
  ('Demo Churn Alert', 'Customers flagged with churn risk', 'retention', 87.00, 'active', '{"source":"seed","threshold":"high"}', NOW(), NOW()),
  ('Demo Import Throughput', 'Rows imported in latest nightly batch', 'operations', 960000.00, 'active', '{"source":"seed","job":"nightly_ingest"}', NOW(), NOW()),
  ('Demo Storage Usage', 'Used storage in GB', 'infrastructure', 782.40, 'active', '{"source":"seed","unit":"GB"}', NOW(), NOW()),
  ('Demo Inventory Risk', 'Low-stock SKUs count', 'inventory', 43.00, 'active', '{"source":"seed","warehouse":"all"}', NOW(), NOW());

DELETE FROM documents WHERE file_name IN ('demo_policy.pdf', 'demo_inventory.csv', 'demo_customers.xlsx', 'demo_contract.docx');
INSERT INTO documents (file_name, original_name, file_path, file_size, file_type, mime_type, category, description, uploaded_by, status, created_at, updated_at)
VALUES
  ('demo_policy.pdf', 'Company Policy 2026.pdf', './uploads/demo_policy.pdf', 245760, 'pdf', 'application/pdf', 'policies', 'Demo policy document for listing page', 'admin@example.com', 'active', NOW(), NOW()),
  ('demo_inventory.csv', 'Warehouse Inventory.csv', './uploads/demo_inventory.csv', 132480, 'csv', 'text/csv', 'inventory', 'Demo inventory export for document listing', 'operator@example.com', 'active', NOW(), NOW()),
  ('demo_customers.xlsx', 'Customer Master.xlsx', './uploads/demo_customers.xlsx', 398120, 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'customers', 'Demo customer workbook', 'analyst@example.com', 'active', NOW(), NOW()),
  ('demo_contract.docx', 'Vendor Contract.docx', './uploads/demo_contract.docx', 184220, 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'contracts', 'Demo contract file', 'finance@example.com', 'active', NOW(), NOW());

DELETE FROM import_logs WHERE file_name IN ('demo_customers_batch.csv', 'demo_products_batch.csv', 'demo_orders_batch.json', 'demo_inventory_patch.csv');
INSERT INTO import_logs (file_name, import_type, total_records, success_count, failure_count, status, error_message, imported_by, created_at, updated_at)
VALUES
  ('demo_customers_batch.csv', 'csv', 1200, 1189, 11, 'completed_with_errors', '11 rows failed due to invalid email format', 'analyst@example.com', NOW() - INTERVAL '3 days', NOW() - INTERVAL '3 days'),
  ('demo_products_batch.csv', 'csv', 840, 840, 0, 'completed', '', 'operator@example.com', NOW() - INTERVAL '2 days', NOW() - INTERVAL '2 days'),
  ('demo_orders_batch.json', 'json', 3200, 3195, 5, 'completed_with_errors', '5 rows failed due to missing customer reference', 'admin@example.com', NOW() - INTERVAL '1 day', NOW() - INTERVAL '1 day'),
  ('demo_inventory_patch.csv', 'csv', 420, 420, 0, 'completed', '', 'operator@example.com', NOW(), NOW());

-- ----------------------------------------------------------
-- 3) Table Configurations for listing pages
-- ----------------------------------------------------------
-- Uses database_name='default' to match default multi-db connection in main.go
INSERT INTO table_configs (name, database_name, table_name, description, columns, primary_key, is_active, created_by, created_at, updated_at)
VALUES
  ('Users', 'default', 'users', 'Application user accounts',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"email","type":"varchar","size":255},{"name":"role","type":"varchar","size":20}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Data Records', 'default', 'data_records', 'Generic business KPI records',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"category","type":"varchar","size":100},{"name":"value","type":"numeric"},{"name":"status","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Documents', 'default', 'documents', 'Uploaded files and metadata',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"file_name","type":"varchar","size":255},{"name":"category","type":"varchar","size":100},{"name":"status","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Import Logs', 'default', 'import_logs', 'Import history and execution status',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"file_name","type":"varchar","size":255},{"name":"import_type","type":"varchar","size":50},{"name":"status","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Table Configs', 'default', 'table_configs', 'Configured import/export table definitions',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"database_name","type":"varchar","size":255},{"name":"table_name","type":"varchar","size":255}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Table Joins', 'default', 'table_joins', 'Join definitions between table configs',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"join_type","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Import Mappings', 'default', 'import_mappings', 'Column mapping templates for import operations',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"source_format","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('Export Configs', 'default', 'export_configs', 'Export templates for table/join data',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"name","type":"varchar","size":255},{"name":"source_type","type":"varchar","size":50},{"name":"target_format","type":"varchar","size":50}]',
   'id', true, 'admin@example.com', NOW(), NOW()),
  ('User Permissions', 'default', 'user_table_permissions', 'Per-user access controls for table configs',
   '[{"name":"id","type":"integer","is_primary":true},{"name":"user_id","type":"integer"},{"name":"table_config_id","type":"integer"},{"name":"can_view","type":"boolean"}]',
   'id', true, 'admin@example.com', NOW(), NOW())
ON CONFLICT (name, database_name) DO UPDATE
SET table_name = EXCLUDED.table_name,
    description = EXCLUDED.description,
    columns = EXCLUDED.columns,
    primary_key = EXCLUDED.primary_key,
    is_active = EXCLUDED.is_active,
    created_by = EXCLUDED.created_by,
    updated_at = NOW();

-- Optional business tables (if present in public schema)
INSERT INTO table_configs (name, database_name, table_name, description, columns, primary_key, is_active, created_by, created_at, updated_at)
SELECT 'Customers', 'default', 'customers', 'Demo customer master table',
       '[{"name":"id","type":"integer","is_primary":true},{"name":"customer_code","type":"varchar","size":50},{"name":"first_name","type":"varchar","size":100},{"name":"email","type":"varchar","size":255}]',
       'id', true, 'admin@example.com', NOW(), NOW()
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'customers')
ON CONFLICT (name, database_name) DO UPDATE
SET table_name = EXCLUDED.table_name,
    description = EXCLUDED.description,
    columns = EXCLUDED.columns,
    updated_at = NOW();

INSERT INTO table_configs (name, database_name, table_name, description, columns, primary_key, is_active, created_by, created_at, updated_at)
SELECT 'Orders', 'default', 'orders', 'Demo orders table',
       '[{"name":"id","type":"integer","is_primary":true},{"name":"order_number","type":"varchar","size":50},{"name":"customer_id","type":"integer"},{"name":"total_amount","type":"numeric"}]',
       'id', true, 'admin@example.com', NOW(), NOW()
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'orders')
ON CONFLICT (name, database_name) DO UPDATE
SET table_name = EXCLUDED.table_name,
    description = EXCLUDED.description,
    columns = EXCLUDED.columns,
    updated_at = NOW();

-- ----------------------------------------------------------
-- 4) Table joins (for join listing page)
-- ----------------------------------------------------------
INSERT INTO table_joins (name, description, left_table_id, right_table_id, join_type, join_condition, select_columns, target_table_id, is_active, created_by, created_at, updated_at)
SELECT
  'Users With Permissions',
  'Join users with user_table_permissions for access overview',
  ucfg.id,
  pcfg.id,
  'INNER',
  'left_table.id = right_table.user_id',
  '["left_table.id AS user_id","left_table.name AS user_name","left_table.email","right_table.table_config_id","right_table.can_view","right_table.can_edit","right_table.can_export"]',
  NULL,
  true,
  'admin@example.com',
  NOW(),
  NOW()
FROM table_configs ucfg, table_configs pcfg
WHERE ucfg.database_name = 'default' AND ucfg.table_name = 'users'
  AND pcfg.database_name = 'default' AND pcfg.table_name = 'user_table_permissions'
ON CONFLICT (name) DO UPDATE
SET description = EXCLUDED.description,
    left_table_id = EXCLUDED.left_table_id,
    right_table_id = EXCLUDED.right_table_id,
    join_type = EXCLUDED.join_type,
    join_condition = EXCLUDED.join_condition,
    select_columns = EXCLUDED.select_columns,
    is_active = true,
    updated_at = NOW();

-- ----------------------------------------------------------
-- 5) Import mappings (for import mapping listing page)
-- ----------------------------------------------------------
INSERT INTO import_mappings (name, description, source_format, table_config_id, column_mapping, transform, is_active, created_by, created_at, updated_at)
SELECT
  'Demo Data Records CSV',
  'CSV mapping template for data_records table',
  'csv',
  cfg.id,
  '{"RecordName":"name","RecordDescription":"description","Category":"category","MetricValue":"value","Status":"status"}',
  '{"value":"to_float"}',
  true,
  'admin@example.com',
  NOW(),
  NOW()
FROM table_configs cfg
WHERE cfg.database_name = 'default' AND cfg.table_name = 'data_records'
ON CONFLICT (name) DO UPDATE
SET description = EXCLUDED.description,
    source_format = EXCLUDED.source_format,
    table_config_id = EXCLUDED.table_config_id,
    column_mapping = EXCLUDED.column_mapping,
    transform = EXCLUDED.transform,
    is_active = true,
    updated_at = NOW();

INSERT INTO import_mappings (name, description, source_format, table_config_id, column_mapping, transform, is_active, created_by, created_at, updated_at)
SELECT
  'Demo Users CSV',
  'CSV mapping template for users table',
  'csv',
  cfg.id,
  '{"FullName":"name","EmailAddress":"email","UserRole":"role"}',
  '{}',
  true,
  'admin@example.com',
  NOW(),
  NOW()
FROM table_configs cfg
WHERE cfg.database_name = 'default' AND cfg.table_name = 'users'
ON CONFLICT (name) DO UPDATE
SET description = EXCLUDED.description,
    source_format = EXCLUDED.source_format,
    table_config_id = EXCLUDED.table_config_id,
    column_mapping = EXCLUDED.column_mapping,
    transform = EXCLUDED.transform,
    is_active = true,
    updated_at = NOW();

-- ----------------------------------------------------------
-- 6) Export configs (for export config listing page)
-- ----------------------------------------------------------
INSERT INTO export_configs (name, description, source_type, source_id, target_format, filters, order_by, column_list, is_active, created_by, created_at, updated_at)
SELECT
  'Demo Users Export CSV',
  'Export active user account listing',
  'table',
  cfg.id,
  'csv',
  '{"role":"user"}',
  '["name ASC"]',
  '["id","name","email","role","created_at"]',
  true,
  'admin@example.com',
  NOW(),
  NOW()
FROM table_configs cfg
WHERE cfg.database_name = 'default' AND cfg.table_name = 'users'
ON CONFLICT (name) DO UPDATE
SET description = EXCLUDED.description,
    source_type = EXCLUDED.source_type,
    source_id = EXCLUDED.source_id,
    target_format = EXCLUDED.target_format,
    filters = EXCLUDED.filters,
    order_by = EXCLUDED.order_by,
    column_list = EXCLUDED.column_list,
    is_active = true,
    updated_at = NOW();

INSERT INTO export_configs (name, description, source_type, source_id, target_format, filters, order_by, column_list, is_active, created_by, created_at, updated_at)
SELECT
  'Demo User Permissions JSON',
  'Export user permission matrix from configured join',
  'join',
  j.id,
  'json',
  '{}',
  '["left_table.name ASC"]',
  '["left_table.name","left_table.email","right_table.table_config_id","right_table.can_view"]',
  true,
  'admin@example.com',
  NOW(),
  NOW()
FROM table_joins j
WHERE j.name = 'Users With Permissions'
ON CONFLICT (name) DO UPDATE
SET description = EXCLUDED.description,
    source_type = EXCLUDED.source_type,
    source_id = EXCLUDED.source_id,
    target_format = EXCLUDED.target_format,
    filters = EXCLUDED.filters,
    order_by = EXCLUDED.order_by,
    column_list = EXCLUDED.column_list,
    is_active = true,
    updated_at = NOW();

-- ----------------------------------------------------------
-- 7) User table permissions (for permissions listing page)
-- ----------------------------------------------------------
DELETE FROM user_table_permissions
WHERE user_id IN (
  SELECT id FROM users WHERE email IN ('analyst@example.com', 'operator@example.com', 'finance@example.com', 'viewer@example.com')
);

-- Analyst: broad read + export on all configured tables
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, tc.id, true, true, false, true, true, NOW(), NOW()
FROM users u CROSS JOIN table_configs tc
WHERE u.email = 'analyst@example.com' AND tc.is_active = true;

-- Operator: import-focused
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, tc.id, true, true, false, false, true, NOW(), NOW()
FROM users u CROSS JOIN table_configs tc
WHERE u.email = 'operator@example.com' AND tc.table_name IN ('data_records','documents','import_logs','import_mappings');

-- Finance: view/export only
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, tc.id, true, false, false, true, false, NOW(), NOW()
FROM users u CROSS JOIN table_configs tc
WHERE u.email = 'finance@example.com' AND tc.table_name IN ('users','data_records','export_configs','table_joins');

-- Viewer: read-only for all configured tables
INSERT INTO user_table_permissions (user_id, table_config_id, can_view, can_edit, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, tc.id, true, false, false, false, false, NOW(), NOW()
FROM users u CROSS JOIN table_configs tc
WHERE u.email = 'viewer@example.com' AND tc.is_active = true;

COMMIT;

-- ----------------------------------------------------------
-- 8) Summary output
-- ----------------------------------------------------------
DO $$
DECLARE
  v_users INT;
  v_data_records INT;
  v_documents INT;
  v_import_logs INT;
  v_table_configs INT;
  v_table_joins INT;
  v_import_mappings INT;
  v_export_configs INT;
  v_permissions INT;
BEGIN
  SELECT COUNT(*) INTO v_users FROM users;
  SELECT COUNT(*) INTO v_data_records FROM data_records;
  SELECT COUNT(*) INTO v_documents FROM documents;
  SELECT COUNT(*) INTO v_import_logs FROM import_logs;
  SELECT COUNT(*) INTO v_table_configs FROM table_configs WHERE is_active = true;
  SELECT COUNT(*) INTO v_table_joins FROM table_joins WHERE is_active = true;
  SELECT COUNT(*) INTO v_import_mappings FROM import_mappings WHERE is_active = true;
  SELECT COUNT(*) INTO v_export_configs FROM export_configs WHERE is_active = true;
  SELECT COUNT(*) INTO v_permissions FROM user_table_permissions;

  RAISE NOTICE '====================================================';
  RAISE NOTICE 'Dummy seed completed for dashboard listings';
  RAISE NOTICE 'Users: %', v_users;
  RAISE NOTICE 'Data Records: %', v_data_records;
  RAISE NOTICE 'Documents: %', v_documents;
  RAISE NOTICE 'Import Logs: %', v_import_logs;
  RAISE NOTICE 'Table Configs: %', v_table_configs;
  RAISE NOTICE 'Table Joins: %', v_table_joins;
  RAISE NOTICE 'Import Mappings: %', v_import_mappings;
  RAISE NOTICE 'Export Configs: %', v_export_configs;
  RAISE NOTICE 'User Table Permissions: %', v_permissions;
  RAISE NOTICE '====================================================';
  RAISE NOTICE 'Demo login candidates: admin@example.com, analyst@example.com';
END $$;
