-- Product Price History Table
CREATE TABLE IF NOT EXISTS `product_price_history` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`        INT UNSIGNED NOT NULL,
    `old_buying_price`  DECIMAL(12,2) DEFAULT NULL,
    `new_buying_price`  DECIMAL(12,2) NOT NULL,
    `old_selling_price` DECIMAL(12,2) DEFAULT NULL,
    `new_selling_price` DECIMAL(12,2) NOT NULL,
    `changed_by`        INT UNSIGNED DEFAULT NULL,
    `change_type`       VARCHAR(50) NOT NULL DEFAULT 'manual_adjust',
    `reason`            VARCHAR(255) DEFAULT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_product_id` (`product_id`),
    KEY `idx_changed_by` (`changed_by`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
