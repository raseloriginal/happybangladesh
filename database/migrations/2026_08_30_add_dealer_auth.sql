-- Migration: Add Authentication to Dealers
-- Date: 2026-08-30
-- Description: Adds username and password columns to allow dealers to login.

-- 1. Add the columns
ALTER TABLE `dealers` 
ADD COLUMN `username` VARCHAR(100) NULL AFTER `name`,
ADD COLUMN `password` VARCHAR(255) NULL AFTER `username`,
ADD UNIQUE INDEX `uq_dealers_username` (`username`);

-- 2. Backfill existing data to avoid NULL issues for unique constraint
-- We use a default password '123456' which is password_hash('123456', PASSWORD_DEFAULT)
-- The hash below is pre-computed for '123456' using BCRYPT
UPDATE `dealers` 
SET `username` = CONCAT('dealer_', `id`),
    `password` = '$2y$10$wE/.7.oT1.P7wZ.D.lXh/O.h3lU0uM6F.sL9WqJ8cR4X8Nf.D7R0e'
WHERE `username` IS NULL;
