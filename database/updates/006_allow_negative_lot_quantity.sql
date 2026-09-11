-- Migration: 006_allow_negative_lot_quantity.sql
-- Allow negative quantities in lots and warehouse inventory, and modify products.pieces_per_box to signed INT

ALTER TABLE `lots` 
    MODIFY `qty_pieces` INT NOT NULL DEFAULT 0,
    MODIFY `qty_boxes` INT NOT NULL DEFAULT 0,
    MODIFY `quantity` INT NOT NULL DEFAULT 0;

ALTER TABLE `inventory` 
    MODIFY `qty_pieces` INT NOT NULL DEFAULT 0,
    MODIFY `qty_boxes` INT NOT NULL DEFAULT 0;

ALTER TABLE `products` 
    MODIFY `pieces_per_box` INT NOT NULL DEFAULT 1;
