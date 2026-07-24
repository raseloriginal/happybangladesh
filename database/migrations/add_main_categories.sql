-- Create the main_categories table
CREATE TABLE IF NOT EXISTS `main_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Add main_category_id to categories table if it doesn't exist
-- Note: MySQL doesn't support IF NOT EXISTS on ADD COLUMN directly without a procedure, 
-- but you can run this manually on the live server:
ALTER TABLE `categories` 
ADD COLUMN `main_category_id` INT UNSIGNED DEFAULT NULL AFTER `company_id`,
ADD CONSTRAINT `fk_categories_main_category` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories`(`id`) ON DELETE SET NULL;
