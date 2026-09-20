-- Migration: 009_add_partial_to_orders_status.sql
-- Add 'partial' status to orders table status enum to match dispatches table

ALTER TABLE `orders` 
  MODIFY COLUMN `status` ENUM('pending','confirmed','dispatched','delivered','partial','cancelled') NOT NULL DEFAULT 'pending';
