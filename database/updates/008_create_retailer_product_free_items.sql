-- Migration: 008_create_retailer_product_free_items.sql
-- Tables for storing free promotional items linked to specific retailers and ordered products

CREATE TABLE IF NOT EXISTS `retailer_product_free_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `retailer_id` INT(11) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `free_product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ret_prod_free` (`retailer_id`, `product_id`, `free_product_id`),
  KEY `idx_ret_prod` (`retailer_id`, `product_id`),
  KEY `idx_free_prod` (`free_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `order_free_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `retailer_id` INT(11) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `free_product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_ret_prod_order` (`retailer_id`, `product_id`),
  KEY `idx_free_prod_order` (`free_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
