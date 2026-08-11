-- ==========================================
-- STEP 1: Add buying_price to Tables
-- ==========================================

-- For dispatch_items
ALTER TABLE dispatch_items 
ADD COLUMN buying_price DECIMAL(12,2) AFTER unit_price;

-- For return_items
ALTER TABLE return_items 
ADD COLUMN buying_price DECIMAL(12,2) AFTER unit_price;

-- For readysales
ALTER TABLE readysales 
ADD COLUMN buying_price DECIMAL(12,2) AFTER price;

-- ==========================================
-- STEP 2: Backfill Historical Data
-- ==========================================

-- Backfill dispatch_items
UPDATE dispatch_items di
JOIN products p ON di.product_id = p.id
SET di.buying_price = p.price;

-- Backfill return_items
UPDATE return_items ri
JOIN products p ON ri.product_id = p.id
SET ri.buying_price = p.price;

-- Backfill readysales
UPDATE readysales rs
JOIN products p ON rs.product_id = p.id
SET rs.buying_price = p.price;
