-- ============================================================
--  HappyBangladesh DMS — Seed Data
--  Run AFTER schema.sql
--  All passwords = "password123" (bcrypt hash)
-- ============================================================

USE `happybangladesh_dms`;

-- ── Roles ─────────────────────────────────────────────────────
INSERT INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Administrator', 'admin'),
(2, 'Manager',       'manager'),
(3, 'Sales Rep',     'sr'),
(4, 'DSR',           'dsr');

-- ── Warehouses ────────────────────────────────────────────────
INSERT INTO `warehouses` (`id`, `name`, `location`, `phone`, `status`) VALUES
(1, 'Dhaka Central Warehouse',   'Tejgaon, Dhaka',        '01700-000001', 1),
(2, 'Chittagong Port Warehouse', 'Agrabad, Chittagong',   '01700-000002', 1),
(3, 'Sylhet Branch',             'Zindabazar, Sylhet',    '01700-000003', 1);

-- ── Users (password = "password123") ─────────────────────────
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `users` (`id`, `role_id`, `warehouse_id`, `company_id`, `name`, `email`, `phone`, `password`, `status`) VALUES
(1, 1, 1, NULL, 'Admin User',       'admin@dms.com',    '01700-111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(2, 2, 1, NULL, 'Karim Manager',    'manager@dms.com',  '01700-222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(3, 2, 2, NULL, 'Rahim Manager',    'manager2@dms.com', '01700-333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(4, 3, 1, 1,    'Sumon SR',         'sr@dms.com',       '01700-444444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(5, 3, 1, 2,    'Milon SR',         'sr2@dms.com',      '01700-555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(6, 4, 1, NULL, 'Rubel DSR',        'dsr@dms.com',      '01700-666666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(7, 4, 2, NULL, 'Hasan DSR',        'dsr2@dms.com',     '01700-777777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- ── Companies ─────────────────────────────────────────────────
INSERT INTO `companies` (`id`, `name`, `contact`, `email`, `phone`, `address`, `status`) VALUES
(1, 'Unilever Bangladesh Ltd',    'Sales Dept',  'sales@unilever.com.bd', '09611-000001', 'Gulshan, Dhaka',     1),
(2, 'Nestlé Bangladesh Limited',  'Trade Team',  'trade@nestle.com.bd',   '09611-000002', 'Banani, Dhaka',      1),
(3, 'ACI Limited',                'Distribution','dist@aci.com.bd',       '09611-000003', 'Tejgaon, Dhaka',     1),
(4, 'Pran-RFL Group',             'FMCG Div',   'fmcg@pran.com.bd',      '09611-000004', 'Naryanganj',        1);

-- ── Dealers ──────────────────────────────────────────────────
INSERT INTO `dealers` (`id`, `warehouse_id`, `name`, `contact`, `phone`, `address`, `credit_limit`, `status`) VALUES
(1, 1, 'Bismillah Traders',     'Hossain',  '01711-100001', 'Mirpur, Dhaka',       50000.00, 1),
(2, 1, 'Akash Enterprise',      'Akash',    '01711-100002', 'Dhanmondi, Dhaka',    75000.00, 1),
(3, 1, 'Rahman Super Shop',     'Rahman',   '01711-100003', 'Uttara, Dhaka',       60000.00, 1),
(4, 2, 'City Departmental',     'Faruk',    '01711-100004', 'Agrabad, Chittagong',100000.00, 1),
(5, 2, 'Village Mart',          'Jalal',    '01711-100005', 'Halisahar, CTG',      30000.00, 1);

-- ── Dealer Companies (dealer_id, company_id, sr_id) ────────────
-- SRs are users: 4 (Sumon SR), 5 (Milon SR)
INSERT INTO `dealer_companies` (`id`, `dealer_id`, `company_id`, `sr_id`) VALUES
(1, 1, 1, 4), -- Bismillah Traders -> Unilever (SR: Sumon)
(2, 1, 2, 5), -- Bismillah Traders -> Nestle (SR: Milon)
(3, 2, 1, 4), -- Akash Enterprise -> Unilever (SR: Sumon)
(4, 3, 3, 4), -- Rahman Super -> ACI
(5, 4, 4, 4), -- City Dept -> Pran
(6, 5, 4, 5); -- Village Mart -> Pran

-- ── Categories ─────────────────────────────────────────────────
INSERT INTO `categories` (`id`, `name`, `status`) VALUES
(1, 'Detergents & Soap', 1),
(2, 'Beverages',         1),
(3, 'Noodles',           1),
(4, 'Grocery & Staples', 1),
(5, 'Antiseptics',       1);

-- ── Products ─────────────────────────────────────────────────
INSERT INTO `products` (`id`, `company_id`, `category_id`, `name`, `sku`, `unit`, `box_type`, `pieces_per_box`, `dealer_percentage`, `buying_price`, `price`, `description`, `status`) VALUES
(1,  1, 1, 'Wheel Detergent Powder 1kg', 'UNI-WHL-1KG', 'pcs', 'বক্স',   12, 10.00, 65.00,  75.00, 'Washing powder 1kg pack',    1),
(2,  1, 1, 'Dove Soap 75g',             'UNI-DOV-75G', 'pcs', 'বক্স',   36, 12.00, 48.00,  55.00, 'Beauty soap bar',             1),
(3,  1, 1, 'Lux Soap 100g',             'UNI-LUX-100', 'pcs', 'বক্স',   48, 12.00, 39.00,  45.00, 'Premium soap bar',            1),
(4,  2, 2, 'Nestlé Milo 400g',          'NES-MIL-400', 'pcs', 'কার্টুন',24,  8.00, 255.00,280.00, 'Chocolate malt drink',        1),
(5,  2, 3, 'Maggi Noodles 70g',         'NES-MAG-70G', 'pcs', 'কার্টুন',48, 15.00, 15.00,  18.00, 'Instant noodles',             1),
(6,  3, 4, 'ACI Pure Salt 1kg',         'ACI-SLT-1KG', 'pcs', 'বস্তা',  25,  5.00, 23.00,  25.00, 'Iodized table salt',          1),
(7,  3, 5, 'Savlon Antiseptic 100ml',   'ACI-SAV-100', 'pcs', 'বক্স',   24, 15.00, 75.00,  90.00, 'Antiseptic liquid',           1),
(8,  4, 2, 'Pran Mango Juice 200ml',    'PRN-MNG-200', 'pcs', 'কেস',    24, 20.00, 16.00,  20.00, 'Mango flavored drink',        1),
(9,  4, 3, 'Mr. Noodles 60g',           'PRN-NOD-60G', 'pcs', 'কার্টুন',48, 15.00, 12.00,  15.00, 'Instant noodles pran',        1),
(10, 4, 4, 'Pran Mustard Oil 1L',       'PRN-MSD-1LT', 'pcs', 'কার্টুন',12, 10.00, 160.00,180.00, 'Refined mustard oil',         1);

-- ── Lots ─────────────────────────────────────────────────────
INSERT INTO `lots` (`id`, `product_id`, `lot_number`, `manufacturing_date`, `expiry_date`, `quantity`) VALUES
(1,  1, 'LOT-2024-001', '2024-01-15', '2026-01-14', 500),
(2,  1, 'LOT-2024-002', '2024-06-01', '2026-05-31', 300),
(3,  2, 'LOT-2024-011', '2024-02-01', '2025-01-31', 400),
(4,  3, 'LOT-2024-021', '2024-03-01', '2025-02-28', 600),
(5,  4, 'LOT-2024-031', '2024-01-20', '2025-01-19', 200),
(6,  5, 'LOT-2024-041', '2024-04-01', '2025-03-31', 800),
(7,  6, 'LOT-2024-051', '2024-05-15', '2026-05-14', 1000),
(8,  7, 'LOT-2024-061', '2024-06-10', '2026-06-09', 250),
(9,  8, 'LOT-2024-071', '2024-03-20', '2025-03-19', 600),
(10, 9, 'LOT-2024-081', '2024-04-25', '2025-04-24', 900);

-- ── Inventory ─────────────────────────────────────────────────
INSERT INTO `inventory` (`warehouse_id`, `product_id`, `lot_id`, `qty_boxes`, `qty_pieces`) VALUES
(1, 1,  1, 37, 6),
(1, 1,  2, 25, 0),
(1, 2,  3, 10, 20),
(1, 3,  4, 12, 4),
(1, 4,  5, 7, 12),
(1, 5,  6, 15, 30),
(1, 6,  7, 38, 0),
(1, 7,  8, 9, 14),
(1, 8,  9, 23, 8),
(1, 9, 10, 18, 6),
(2, 1,  1, 8, 4),
(2, 5,  6, 4, 8),
(2, 8,  9, 6, 6);

-- ── Van Stock ─────────────────────────────────────────────────
INSERT INTO `van_stock` (`dsr_id`, `product_id`, `lot_id`, `quantity`, `loaded_at`) VALUES
(6, 1, 1,  50, CURDATE()),
(6, 5, 6, 100, CURDATE()),
(6, 8, 9,  80, CURDATE()),
(7, 2, 3,  40, CURDATE()),
(7, 9,10,  60, CURDATE());

-- ── Sample Orders ─────────────────────────────────────────────
INSERT INTO `orders` (`id`, `sr_id`, `dealer_id`, `warehouse_id`, `status`, `total_amount`, `notes`) VALUES
(1, 4, 1, 1, 'confirmed',  3750.00, 'Urgent delivery'),
(2, 4, 2, 1, 'pending',    1800.00, NULL),
(3, 5, 3, 1, 'dispatched', 5600.00, 'Handle with care'),
(4, 4, 4, 1, 'delivered',  2250.00, NULL),
(5, 5, 5, 1, 'pending',     900.00, NULL);

-- ── Order Items ──────────────────────────────────────────────
INSERT INTO `order_items` (`order_id`, `product_id`, `lot_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, 50,  75.00, 3750.00),
(2, 5, 6, 100, 18.00, 1800.00),
(3, 4, 5, 20, 280.00, 5600.00),
(4, 6, 7, 90,  25.00, 2250.00),
(5, 8, 9, 45,  20.00,  900.00);

-- ── Sample Dispatches ─────────────────────────────────────────
INSERT INTO `dispatches` (`id`, `order_id`, `dsr_id`, `warehouse_id`, `dispatch_date`, `status`) VALUES
(1, 1, 6, 1, CURDATE(), 'in_transit'),
(2, 3, 7, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'delivered');

-- ── Attendance ────────────────────────────────────────────────
INSERT INTO `attendance` (`user_id`, `date`, `check_in`, `check_out`, `status`) VALUES
(6, CURDATE(), '08:30:00', '17:00:00', 'present'),
(7, CURDATE(), '08:45:00', '17:00:00', 'present'),
(4, CURDATE(), '09:00:00', '17:00:00', 'present'),
(5, CURDATE(), '09:15:00', '17:00:00', 'late');

-- ── Expenses ─────────────────────────────────────────────────
INSERT INTO `expenses` (`dsr_id`, `date`, `category`, `amount`, `description`, `status`) VALUES
(6, CURDATE(), 'fuel',  500.00, 'Petrol for van route A',     'pending'),
(6, CURDATE(), 'food',  150.00, 'Lunch during delivery',      'approved'),
(7, CURDATE(), 'toll',   80.00, 'Toll gate highway',           'pending'),
(7, CURDATE(), 'fuel',  600.00, 'CNG refill for van',         'approved');

-- ── Activity Logs ────────────────────────────────────────────
INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `record_id`, `description`, `ip_address`) VALUES
(1, 'login',   'auth',     NULL, 'Admin logged in',         '127.0.0.1'),
(2, 'create',  'products',    1, 'Created product Wheel',   '127.0.0.1'),
(4, 'create',  'orders',      1, 'Placed order #1',         '127.0.0.1'),
(6, 'update',  'dispatches',  1, 'Updated dispatch status', '127.0.0.1');
