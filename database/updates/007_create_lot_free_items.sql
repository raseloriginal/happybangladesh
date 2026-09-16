-- Migration: 007_create_lot_free_items.sql
-- Table structure for storing free items associated with lots / lot batches

CREATE TABLE IF NOT EXISTS `lot_free_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT(10) UNSIGNED NOT NULL,
  `lot_date` DATE NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company_date` (`company_id`, `lot_date`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `fk_lfi_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lfi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
