-- ==========================================
-- STEP 1: Add Snapshot Columns to Tables
-- ==========================================

-- For order_items
ALTER TABLE order_items 
ADD COLUMN product_name VARCHAR(200) AFTER product_id,
ADD COLUMN box_type VARCHAR(50) AFTER product_name,
ADD COLUMN pieces_per_box INT(10) AFTER box_type,
ADD COLUMN buying_price DECIMAL(12,2) AFTER pieces_per_box;

-- For dispatch_items
ALTER TABLE dispatch_items 
ADD COLUMN product_name VARCHAR(200) AFTER product_id,
ADD COLUMN box_type VARCHAR(50) AFTER product_name,
ADD COLUMN pieces_per_box INT(10) AFTER box_type,
ADD COLUMN unit_price DECIMAL(12,2) AFTER pieces_per_box,
ADD COLUMN total_price DECIMAL(14,2) AFTER unit_price;

-- For return_items
ALTER TABLE return_items 
ADD COLUMN product_name VARCHAR(200) AFTER product_id,
ADD COLUMN box_type VARCHAR(50) AFTER product_name,
ADD COLUMN pieces_per_box INT(10) AFTER box_type,
ADD COLUMN unit_price DECIMAL(12,2) AFTER pieces_per_box;

-- For orders (Retailer Snapshot)
ALTER TABLE orders 
ADD COLUMN retailer_name VARCHAR(255) AFTER retailer_id,
ADD COLUMN retailer_phone VARCHAR(30) AFTER retailer_name,
ADD COLUMN retailer_address TEXT AFTER retailer_phone;


-- ==========================================
-- STEP 2: Backfill Existing Data (No Data Loss)
-- ==========================================

-- Update order_items
UPDATE order_items oi
JOIN products p ON oi.product_id = p.id
SET oi.product_name = p.name,
    oi.box_type = p.box_type,
    oi.pieces_per_box = p.pieces_per_box,
    oi.buying_price = p.buying_price;

-- Update dispatch_items
UPDATE dispatch_items di
JOIN products p ON di.product_id = p.id
SET di.product_name = p.name,
    di.box_type = p.box_type,
    di.pieces_per_box = p.pieces_per_box,
    di.unit_price = p.price,
    di.total_price = (di.quantity * p.price);

-- Update return_items
UPDATE return_items ri
JOIN products p ON ri.product_id = p.id
SET ri.product_name = p.name,
    ri.box_type = p.box_type,
    ri.pieces_per_box = p.pieces_per_box,
    ri.unit_price = p.price;

-- Update orders (with Retailer info)
UPDATE orders o
JOIN retailers r ON o.retailer_id = r.id
SET o.retailer_name = r.name,
    o.retailer_phone = r.phone,
    o.retailer_address = r.address;

-- For readysales
ALTER TABLE readysales 
ADD COLUMN product_name VARCHAR(200) AFTER product_id, 
ADD COLUMN box_type VARCHAR(50) AFTER product_name, 
ADD COLUMN pieces_per_box INT(10) AFTER box_type;

UPDATE readysales rs 
JOIN products p ON rs.product_id = p.id 
SET rs.product_name = p.name, 
    rs.box_type = p.box_type, 
    rs.pieces_per_box = p.pieces_per_box;
