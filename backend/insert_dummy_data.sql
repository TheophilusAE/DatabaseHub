-- ====================================
-- Insert Dummy Data for Data Import Dashboard
-- ====================================
-- Run this script AFTER test_tables_setup.sql
-- This populates the database with realistic test data

-- ====================================
-- 1. USERS
-- ====================================
-- Password for all users: "password123" (bcrypt hashed)
INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES
('Admin User', 'admin@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
('John Smith', 'john.smith@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Sarah Johnson', 'sarah.johnson@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Mike Davis', 'mike.davis@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Emily Chen', 'emily.chen@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW())
ON CONFLICT (email) DO NOTHING;

-- ====================================
-- 2. TABLE CONFIGURATIONS
-- ====================================
INSERT INTO table_configs (name, database_name, table_name, description, columns, primary_key, is_active, created_by, created_at, updated_at) VALUES
('Data Records', 'postgres', 'data_records', 'Main data records table for general imports', 
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"name","type":"varchar","size":255,"nullable":false},{"name":"description","type":"text","nullable":true},{"name":"category","type":"varchar","size":100,"nullable":true},{"name":"value","type":"numeric","nullable":true},{"name":"status","type":"varchar","size":50,"nullable":true},{"name":"metadata","type":"text","nullable":true},{"name":"created_at","type":"timestamp","nullable":true},{"name":"updated_at","type":"timestamp","nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Customers', 'postgres', 'customers', 'Customer information and contact details',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"customer_code","type":"varchar","size":50,"nullable":false,"is_unique":true},{"name":"first_name","type":"varchar","size":100,"nullable":false},{"name":"last_name","type":"varchar","size":100,"nullable":false},{"name":"email","type":"varchar","size":255,"nullable":false,"is_unique":true},{"name":"phone","type":"varchar","size":20,"nullable":true},{"name":"address","type":"text","nullable":true},{"name":"city","type":"varchar","size":100,"nullable":true},{"name":"state","type":"varchar","size":50,"nullable":true},{"name":"zip_code","type":"varchar","size":20,"nullable":true},{"name":"country","type":"varchar","size":100,"nullable":true},{"name":"customer_since","type":"date","nullable":true},{"name":"status","type":"varchar","size":20,"nullable":true},{"name":"total_purchases","type":"numeric","nullable":true},{"name":"notes","type":"text","nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Products', 'postgres', 'products', 'Product catalog with pricing and inventory',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"sku","type":"varchar","size":50,"nullable":false,"is_unique":true},{"name":"product_name","type":"varchar","size":255,"nullable":false},{"name":"description","type":"text","nullable":true},{"name":"category","type":"varchar","size":100,"nullable":true},{"name":"subcategory","type":"varchar","size":100,"nullable":true},{"name":"brand","type":"varchar","size":100,"nullable":true},{"name":"unit_price","type":"numeric","nullable":false},{"name":"cost_price","type":"numeric","nullable":true},{"name":"quantity_in_stock","type":"integer","nullable":true},{"name":"reorder_level","type":"integer","nullable":true},{"name":"supplier","type":"varchar","size":200,"nullable":true},{"name":"barcode","type":"varchar","size":100,"nullable":true},{"name":"weight_kg","type":"numeric","nullable":true},{"name":"is_active","type":"boolean","nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Orders', 'postgres', 'orders', 'Customer orders and transactions',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"order_number","type":"varchar","size":50,"nullable":false,"is_unique":true},{"name":"customer_id","type":"integer","nullable":true},{"name":"order_date","type":"date","nullable":false},{"name":"ship_date","type":"date","nullable":true},{"name":"status","type":"varchar","size":50,"nullable":true},{"name":"subtotal","type":"numeric","nullable":false},{"name":"tax_amount","type":"numeric","nullable":true},{"name":"shipping_cost","type":"numeric","nullable":true},{"name":"total_amount","type":"numeric","nullable":false},{"name":"payment_method","type":"varchar","size":50,"nullable":true},{"name":"shipping_address","type":"text","nullable":true},{"name":"notes","type":"text","nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Employees', 'postgres', 'employees', 'Employee records and HR information',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"employee_id","type":"varchar","size":50,"nullable":false,"is_unique":true},{"name":"first_name","type":"varchar","size":100,"nullable":false},{"name":"last_name","type":"varchar","size":100,"nullable":false},{"name":"email","type":"varchar","size":255,"nullable":false,"is_unique":true},{"name":"phone","type":"varchar","size":20,"nullable":true},{"name":"department","type":"varchar","size":100,"nullable":true},{"name":"position","type":"varchar","size":100,"nullable":true},{"name":"hire_date","type":"date","nullable":false},{"name":"salary","type":"numeric","nullable":true},{"name":"office_location","type":"varchar","size":100,"nullable":true},{"name":"employment_status","type":"varchar","size":50,"nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Sales', 'postgres', 'sales', 'Sales transactions and revenue tracking',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"transaction_id","type":"varchar","size":50,"nullable":false,"is_unique":true},{"name":"sale_date","type":"date","nullable":false},{"name":"product_id","type":"integer","nullable":true},{"name":"customer_id","type":"integer","nullable":true},{"name":"quantity","type":"integer","nullable":false},{"name":"unit_price","type":"numeric","nullable":false},{"name":"discount_percent","type":"numeric","nullable":true},{"name":"tax_percent","type":"numeric","nullable":true},{"name":"total_amount","type":"numeric","nullable":false},{"name":"payment_method","type":"varchar","size":50,"nullable":true},{"name":"salesperson","type":"varchar","size":100,"nullable":true},{"name":"region","type":"varchar","size":100,"nullable":true}]',
'id', true, 'admin', NOW(), NOW()),

('Inventory', 'postgres', 'inventory', 'Warehouse inventory tracking',
'[{"name":"id","type":"integer","nullable":false,"is_primary":true},{"name":"product_id","type":"integer","nullable":true},{"name":"sku","type":"varchar","size":50,"nullable":false},{"name":"warehouse_location","type":"varchar","size":100,"nullable":true},{"name":"quantity_available","type":"integer","nullable":true},{"name":"quantity_reserved","type":"integer","nullable":true},{"name":"quantity_damaged","type":"integer","nullable":true},{"name":"last_counted_date","type":"date","nullable":true},{"name":"reorder_point","type":"integer","nullable":true},{"name":"max_stock_level","type":"integer","nullable":true}]',
'id', true, 'admin', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ====================================
-- 3. IMPORT MAPPINGS
-- ====================================
INSERT INTO import_mappings (name, description, source_format, table_config_id, column_mapping, is_active, created_by, created_at, updated_at) VALUES
('Customer CSV Import', 'Standard mapping for customer CSV files', 'csv', 
(SELECT id FROM table_configs WHERE table_name='customers' LIMIT 1),
'{"CustomerCode":"customer_code","FirstName":"first_name","LastName":"last_name","Email":"email","Phone":"phone","Address":"address","City":"city","State":"state","ZipCode":"zip_code","Country":"country","Status":"status"}',
true, 'admin', NOW(), NOW()),

('Product Catalog Import', 'Standard mapping for product CSV files', 'csv',
(SELECT id FROM table_configs WHERE table_name='products' LIMIT 1),
'{"SKU":"sku","ProductName":"product_name","Description":"description","Category":"category","Brand":"brand","UnitPrice":"unit_price","CostPrice":"cost_price","Stock":"quantity_in_stock","Supplier":"supplier","Barcode":"barcode"}',
true, 'admin', NOW(), NOW()),

('Order Import', 'Standard mapping for order data', 'csv',
(SELECT id FROM table_configs WHERE table_name='orders' LIMIT 1),
'{"OrderNumber":"order_number","CustomerID":"customer_id","OrderDate":"order_date","ShipDate":"ship_date","Status":"status","Subtotal":"subtotal","Tax":"tax_amount","Shipping":"shipping_cost","Total":"total_amount","PaymentMethod":"payment_method"}',
true, 'admin', NOW(), NOW()),

('Employee Data Import', 'Standard mapping for employee records', 'csv',
(SELECT id FROM table_configs WHERE table_name='employees' LIMIT 1),
'{"EmployeeID":"employee_id","FirstName":"first_name","LastName":"last_name","Email":"email","Phone":"phone","Department":"department","Position":"position","HireDate":"hire_date","Salary":"salary","Status":"employment_status"}',
true, 'admin', NOW(), NOW()),

('Sales Transaction Import', 'Standard mapping for sales data', 'csv',
(SELECT id FROM table_configs WHERE table_name='sales' LIMIT 1),
'{"TransactionID":"transaction_id","SaleDate":"sale_date","ProductID":"product_id","CustomerID":"customer_id","Quantity":"quantity","UnitPrice":"unit_price","Discount":"discount_percent","Tax":"tax_percent","Total":"total_amount","PaymentMethod":"payment_method","Salesperson":"salesperson","Region":"region"}',
true, 'admin', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ====================================
-- 4. USER TABLE PERMISSIONS
-- ====================================
-- Give all users full access to data_records
INSERT INTO user_table_permissions (user_id, table_config_id, can_read, can_create, can_update, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, true, true, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE t.table_name = 'data_records'
ON CONFLICT DO NOTHING;

-- Give John Smith read/create access to customers and products
INSERT INTO user_table_permissions (user_id, table_config_id, can_read, can_create, can_update, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, false, false, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'john.smith@example.com' AND t.table_name IN ('customers', 'products')
ON CONFLICT DO NOTHING;

-- Give Sarah Johnson full access to sales and orders
INSERT INTO user_table_permissions (user_id, table_config_id, can_read, can_create, can_update, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, true, true, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'sarah.johnson@example.com' AND t.table_name IN ('sales', 'orders')
ON CONFLICT DO NOTHING;

-- Give Mike Davis access to employees and inventory
INSERT INTO user_table_permissions (user_id, table_config_id, can_read, can_create, can_update, can_delete, can_export, can_import, created_at, updated_at)
SELECT u.id, t.id, true, true, true, false, true, true, NOW(), NOW()
FROM users u, table_configs t
WHERE u.email = 'mike.davis@example.com' AND t.table_name IN ('employees', 'inventory')
ON CONFLICT DO NOTHING;

-- ====================================
-- 5. SAMPLE BUSINESS DATA - CUSTOMERS
-- ====================================
INSERT INTO customers (customer_code, first_name, last_name, email, phone, address, city, state, zip_code, country, customer_since, status, total_purchases, notes) VALUES
('CUST001', 'Robert', 'Anderson', 'robert.anderson@email.com', '555-0101', '123 Main St', 'New York', 'NY', '10001', 'USA', '2023-01-15', 'active', 15234.50, 'VIP customer'),
('CUST002', 'Jennifer', 'Martinez', 'jennifer.m@email.com', '555-0102', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'USA', '2023-02-20', 'active', 8942.30, 'Prefers email contact'),
('CUST003', 'David', 'Wilson', 'david.wilson@email.com', '555-0103', '789 Pine Rd', 'Chicago', 'IL', '60601', 'USA', '2023-03-10', 'active', 12456.80, 'Wholesale customer'),
('CUST004', 'Lisa', 'Taylor', 'lisa.taylor@email.com', '555-0104', '321 Elm St', 'Houston', 'TX', '77001', 'USA', '2023-04-05', 'active', 5678.90, NULL),
('CUST005', 'Michael', 'Brown', 'michael.brown@email.com', '555-0105', '654 Maple Dr', 'Phoenix', 'AZ', '85001', 'USA', '2023-05-15', 'active', 9345.20, 'Monthly recurring orders'),
('CUST006', 'Amanda', 'Garcia', 'amanda.garcia@email.com', '555-0106', '987 Cedar Ln', 'Philadelphia', 'PA', '19101', 'USA', '2023-06-01', 'active', 3456.70, NULL),
('CUST007', 'James', 'Miller', 'james.miller@email.com', '555-0107', '147 Birch Ave', 'San Antonio', 'TX', '78201', 'USA', '2023-07-12', 'active', 7890.40, 'Corporate account'),
('CUST008', 'Patricia', 'Davis', 'patricia.davis@email.com', '555-0108', '258 Spruce St', 'San Diego', 'CA', '92101', 'USA', '2023-08-20', 'active', 4567.80, NULL),
('CUST009', 'Christopher', 'Rodriguez', 'chris.rodriguez@email.com', '555-0109', '369 Ash Blvd', 'Dallas', 'TX', '75201', 'USA', '2023-09-05', 'active', 11234.60, 'Prefers phone contact'),
('CUST010', 'Michelle', 'Martinez', 'michelle.martinez@email.com', '555-0110', '741 Walnut Way', 'San Jose', 'CA', '95101', 'USA', '2023-10-18', 'inactive', 2345.90, 'Account on hold');

-- ====================================
-- 6. SAMPLE BUSINESS DATA - PRODUCTS
-- ====================================
INSERT INTO products (sku, product_name, description, category, subcategory, brand, unit_price, cost_price, quantity_in_stock, reorder_level, supplier, barcode, weight_kg, is_active) VALUES
('ELEC001', 'Wireless Mouse', 'Ergonomic wireless mouse with USB receiver', 'Electronics', 'Computer Accessories', 'TechPro', 29.99, 15.00, 150, 20, 'TechSupply Inc', '1234567890123', 0.15, true),
('ELEC002', 'Mechanical Keyboard', 'RGB backlit mechanical gaming keyboard', 'Electronics', 'Computer Accessories', 'GameGear', 89.99, 45.00, 75, 15, 'TechSupply Inc', '1234567890124', 1.20, true),
('ELEC003', '27" Monitor', '4K Ultra HD LED monitor with HDR', 'Electronics', 'Displays', 'ViewMax', 349.99, 200.00, 45, 10, 'Display Distributors', '1234567890125', 5.50, true),
('ELEC004', 'USB Webcam', '1080p HD webcam with built-in microphone', 'Electronics', 'Computer Accessories', 'ClearView', 59.99, 30.00, 120, 25, 'TechSupply Inc', '1234567890126', 0.25, true),
('FURN001', 'Office Chair', 'Ergonomic mesh office chair with lumbar support', 'Furniture', 'Seating', 'ComfortPlus', 199.99, 100.00, 30, 5, 'Furniture Wholesale', '2234567890123', 15.00, true),
('FURN002', 'Standing Desk', 'Electric height-adjustable standing desk', 'Furniture', 'Desks', 'DeskPro', 449.99, 250.00, 20, 5, 'Furniture Wholesale', '2234567890124', 35.00, true),
('FURN003', 'Bookshelf', '5-tier wooden bookshelf', 'Furniture', 'Storage', 'WoodCraft', 129.99, 65.00, 40, 8, 'Furniture Wholesale', '2234567890125', 18.00, true),
('BOOK001', 'Python Programming Guide', 'Comprehensive guide to Python 3', 'Books', 'Technology', 'TechBooks', 39.99, 20.00, 200, 30, 'Book Distributors', '3234567890123', 0.60, true),
('BOOK002', 'Data Science Handbook', 'Essential data science techniques and tools', 'Books', 'Technology', 'TechBooks', 49.99, 25.00, 150, 25, 'Book Distributors', '3234567890124', 0.75, true),
('BOOK003', 'Business Strategy', 'Modern approaches to business management', 'Books', 'Business', 'BizPress', 34.99, 18.00, 180, 30, 'Book Distributors', '3234567890125', 0.55, true),
('STAT001', 'Notebook Set', 'Pack of 5 lined notebooks', 'Stationery', 'Writing', 'PaperCo', 12.99, 6.00, 300, 50, 'Office Supply Co', '4234567890123', 0.80, true),
('STAT002', 'Pen Set Premium', 'Set of 10 gel pens assorted colors', 'Stationery', 'Writing', 'WriteWell', 15.99, 8.00, 250, 40, 'Office Supply Co', '4234567890124', 0.20, true);

-- ====================================
-- 7. SAMPLE BUSINESS DATA - ORDERS
-- ====================================
INSERT INTO orders (order_number, customer_id, order_date, ship_date, status, subtotal, tax_amount, shipping_cost, total_amount, payment_method, shipping_address, notes) VALUES
('ORD001', 1, '2024-01-15', '2024-01-17', 'delivered', 459.97, 41.40, 15.00, 516.37, 'Credit Card', '123 Main St, New York, NY 10001', NULL),
('ORD002', 2, '2024-01-20', '2024-01-22', 'delivered', 189.96, 17.10, 10.00, 217.06, 'PayPal', '456 Oak Ave, Los Angeles, CA 90001', NULL),
('ORD003', 3, '2024-01-25', '2024-01-27', 'delivered', 799.96, 72.00, 25.00, 896.96, 'Credit Card', '789 Pine Rd, Chicago, IL 60601', 'Bulk order'),
('ORD004', 4, '2024-02-01', '2024-02-03', 'delivered', 129.98, 11.70, 8.00, 149.68, 'Debit Card', '321 Elm St, Houston, TX 77001', NULL),
('ORD005', 1, '2024-02-05', '2024-02-07', 'shipped', 349.99, 31.50, 12.00, 393.49, 'Credit Card', '123 Main St, New York, NY 10001', NULL),
('ORD006', 5, '2024-02-10', NULL, 'processing', 89.99, 8.10, 7.00, 105.09, 'PayPal', '654 Maple Dr, Phoenix, AZ 85001', NULL),
('ORD007', 6, '2024-02-12', '2024-02-14', 'delivered', 229.97, 20.70, 10.00, 260.67, 'Credit Card', '987 Cedar Ln, Philadelphia, PA 19101', NULL),
('ORD008', 7, '2024-02-15', NULL, 'pending', 599.97, 54.00, 20.00, 673.97, 'Bank Transfer', '147 Birch Ave, San Antonio, TX 78201', 'Corporate order'),
('ORD009', 2, '2024-02-18', '2024-02-20', 'delivered', 199.99, 18.00, 10.00, 227.99, 'PayPal', '456 Oak Ave, Los Angeles, CA 90001', NULL),
('ORD010', 8, '2024-02-20', NULL, 'processing', 449.99, 40.50, 15.00, 505.49, 'Credit Card', '258 Spruce St, San Diego, CA 92101', NULL);

-- ====================================
-- 8. SAMPLE BUSINESS DATA - EMPLOYEES
-- ====================================
INSERT INTO employees (employee_id, first_name, last_name, email, phone, department, position, hire_date, salary, office_location, employment_status) VALUES
('EMP001', 'Thomas', 'Johnson', 'thomas.johnson@company.com', '555-1001', 'Sales', 'Sales Manager', '2020-03-15', 75000.00, 'New York', 'active'),
('EMP002', 'Rachel', 'Williams', 'rachel.williams@company.com', '555-1002', 'Marketing', 'Marketing Specialist', '2021-06-01', 55000.00, 'Los Angeles', 'active'),
('EMP003', 'Kevin', 'Brown', 'kevin.brown@company.com', '555-1003', 'IT', 'Software Developer', '2019-09-10', 85000.00, 'San Francisco', 'active'),
('EMP004', 'Laura', 'Davis', 'laura.davis@company.com', '555-1004', 'HR', 'HR Manager', '2020-01-20', 70000.00, 'Chicago', 'active'),
('EMP005', 'Steven', 'Miller', 'steven.miller@company.com', '555-1005', 'Finance', 'Financial Analyst', '2021-03-15', 65000.00, 'New York', 'active'),
('EMP006', 'Nicole', 'Wilson', 'nicole.wilson@company.com', '555-1006', 'Sales', 'Sales Representative', '2022-07-01', 50000.00, 'Boston', 'active'),
('EMP007', 'Daniel', 'Moore', 'daniel.moore@company.com', '555-1007', 'IT', 'System Administrator', '2020-11-15', 72000.00, 'San Francisco', 'active'),
('EMP008', 'Jessica', 'Taylor', 'jessica.taylor@company.com', '555-1008', 'Marketing', 'Content Writer', '2022-02-10', 48000.00, 'Los Angeles', 'active'),
('EMP009', 'Ryan', 'Anderson', 'ryan.anderson@company.com', '555-1009', 'Operations', 'Operations Manager', '2019-05-20', 78000.00, 'Chicago', 'active'),
('EMP010', 'Ashley', 'Thomas', 'ashley.thomas@company.com', '555-1010', 'Customer Service', 'Customer Service Rep', '2023-01-15', 42000.00, 'New York', 'active');

-- ====================================
-- 9. SAMPLE BUSINESS DATA - SALES
-- ====================================
INSERT INTO sales (transaction_id, sale_date, product_id, customer_id, quantity, unit_price, discount_percent, tax_percent, total_amount, payment_method, salesperson, region) VALUES
('TXN001', '2024-01-15', 1, 1, 3, 29.99, 0, 9.0, 97.97, 'Credit Card', 'Thomas Johnson', 'Northeast'),
('TXN002', '2024-01-15', 2, 1, 2, 89.99, 5.0, 9.0, 185.82, 'Credit Card', 'Thomas Johnson', 'Northeast'),
('TXN003', '2024-01-20', 4, 2, 1, 59.99, 0, 9.0, 65.39, 'PayPal', 'Nicole Wilson', 'West'),
('TXN004', '2024-01-20', 8, 2, 3, 39.99, 10.0, 9.0, 130.77, 'PayPal', 'Nicole Wilson', 'West'),
('TXN005', '2024-01-25', 5, 3, 4, 199.99, 0, 9.0, 871.96, 'Credit Card', 'Thomas Johnson', 'Midwest'),
('TXN006', '2024-02-01', 11, 4, 10, 12.99, 0, 9.0, 141.59, 'Debit Card', 'Nicole Wilson', 'South'),
('TXN007', '2024-02-05', 3, 1, 1, 349.99, 0, 9.0, 381.49, 'Credit Card', 'Thomas Johnson', 'Northeast'),
('TXN008', '2024-02-10', 2, 5, 1, 89.99, 0, 9.0, 98.09, 'PayPal', 'Nicole Wilson', 'West'),
('TXN009', '2024-02-12', 9, 6, 5, 49.99, 15.0, 9.0, 231.46, 'Credit Card', 'Thomas Johnson', 'Northeast'),
('TXN010', '2024-02-15', 6, 7, 2, 449.99, 10.0, 9.0, 882.18, 'Bank Transfer', 'Nicole Wilson', 'South'),
('TXN011', '2024-02-18', 5, 2, 1, 199.99, 0, 9.0, 217.99, 'PayPal', 'Nicole Wilson', 'West'),
('TXN012', '2024-02-20', 6, 8, 1, 449.99, 0, 9.0, 490.49, 'Credit Card', 'Nicole Wilson', 'West');

-- ====================================
-- 10. SAMPLE BUSINESS DATA - INVENTORY
-- ====================================
INSERT INTO inventory (product_id, sku, warehouse_location, quantity_available, quantity_reserved, quantity_damaged, last_counted_date, reorder_point, max_stock_level) VALUES
(1, 'ELEC001', 'Warehouse A', 150, 10, 2, '2024-02-15', 20, 200),
(2, 'ELEC002', 'Warehouse A', 75, 5, 1, '2024-02-15', 15, 100),
(3, 'ELEC003', 'Warehouse A', 45, 3, 0, '2024-02-15', 10, 75),
(4, 'ELEC004', 'Warehouse A', 120, 8, 1, '2024-02-15', 25, 150),
(5, 'FURN001', 'Warehouse B', 30, 2, 0, '2024-02-14', 5, 50),
(6, 'FURN002', 'Warehouse B', 20, 1, 0, '2024-02-14', 5, 30),
(7, 'FURN003', 'Warehouse B', 40, 3, 1, '2024-02-14', 8, 60),
(8, 'BOOK001', 'Warehouse C', 200, 15, 3, '2024-02-16', 30, 300),
(9, 'BOOK002', 'Warehouse C', 150, 10, 2, '2024-02-16', 25, 250),
(10, 'BOOK003', 'Warehouse C', 180, 12, 1, '2024-02-16', 30, 280),
(11, 'STAT001', 'Warehouse C', 300, 25, 5, '2024-02-16', 50, 400),
(12, 'STAT002', 'Warehouse C', 250, 20, 3, '2024-02-16', 40, 350);

-- ====================================
-- SUCCESS MESSAGE
-- ====================================
DO $$
DECLARE
    user_count INTEGER;
    table_config_count INTEGER;
    mapping_count INTEGER;
    permission_count INTEGER;
    customer_count INTEGER;
    product_count INTEGER;
    order_count INTEGER;
    employee_count INTEGER;
    sale_count INTEGER;
    inventory_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO user_count FROM users;
    SELECT COUNT(*) INTO table_config_count FROM table_configs;
    SELECT COUNT(*) INTO mapping_count FROM import_mappings;
    SELECT COUNT(*) INTO permission_count FROM user_table_permissions;
    SELECT COUNT(*) INTO customer_count FROM customers;
    SELECT COUNT(*) INTO product_count FROM products;
    SELECT COUNT(*) INTO order_count FROM orders;
    SELECT COUNT(*) INTO employee_count FROM employees;
    SELECT COUNT(*) INTO sale_count FROM sales;
    SELECT COUNT(*) INTO inventory_count FROM inventory;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Dummy Data Inserted Successfully!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Users: % (admin@example.com / password123)', user_count;
    RAISE NOTICE 'Table Configs: %', table_config_count;
    RAISE NOTICE 'Import Mappings: %', mapping_count;
    RAISE NOTICE 'User Table Permissions: %', permission_count;
    RAISE NOTICE '----------------------------------------';
    RAISE NOTICE 'Business Data:';
    RAISE NOTICE '  - Customers: %', customer_count;
    RAISE NOTICE '  - Products: %', product_count;
    RAISE NOTICE '  - Orders: %', order_count;
    RAISE NOTICE '  - Employees: %', employee_count;
    RAISE NOTICE '  - Sales: %', sale_count;
    RAISE NOTICE '  - Inventory: %', inventory_count;
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Login as: admin@example.com / password123';
    RAISE NOTICE 'or any other user with same password';
    RAISE NOTICE '========================================';
END $$;
