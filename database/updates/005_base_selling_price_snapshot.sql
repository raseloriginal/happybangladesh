-- =====================================================
-- 005: Add base_selling_price snapshot to transaction tables
-- base_selling_price = buying_price + dealer_percentage%
--                    = products.price at the time of transaction
-- O/C = unit_price - base_selling_price
-- =====================================================

-- 1. order_items
ALTER TABLE order_items
  ADD COLUMN base_selling_price DECIMAL(12,2) DEFAULT NULL AFTER unit_price;

-- 2. dispatch_items
ALTER TABLE dispatch_items
  ADD COLUMN base_selling_price DECIMAL(12,2) DEFAULT NULL AFTER unit_price;

-- 3. return_items
ALTER TABLE return_items
  ADD COLUMN base_selling_price DECIMAL(12,2) DEFAULT NULL AFTER unit_price;

-- 4. readysales
ALTER TABLE readysales
  ADD COLUMN base_selling_price DECIMAL(12,2) DEFAULT NULL AFTER price;

-- ── Backfill historical rows ──────────────────────────
-- Best approximation: current products.price
-- (exact historical price unavailable for old rows)

UPDATE order_items oi
JOIN products p ON p.id = oi.product_id
SET oi.base_selling_price = p.price
WHERE oi.base_selling_price IS NULL;

UPDATE dispatch_items di
JOIN products p ON p.id = di.product_id
SET di.base_selling_price = p.price
WHERE di.base_selling_price IS NULL;

UPDATE return_items ri
JOIN products p ON p.id = ri.product_id
SET ri.base_selling_price = p.price
WHERE ri.base_selling_price IS NULL;

UPDATE readysales rs
JOIN products p ON p.id = rs.product_id
SET rs.base_selling_price = p.price
WHERE rs.base_selling_price IS NULL;
