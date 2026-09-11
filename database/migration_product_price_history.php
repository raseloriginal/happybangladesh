<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS `product_price_history` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `product_id` INT UNSIGNED NOT NULL,
      `old_buying_price` DECIMAL(12,2) DEFAULT NULL,
      `new_buying_price` DECIMAL(12,2) NOT NULL,
      `old_selling_price` DECIMAL(12,2) DEFAULT NULL,
      `new_selling_price` DECIMAL(12,2) NOT NULL,
      `changed_by` INT UNSIGNED DEFAULT NULL,
      `change_type` VARCHAR(50) NOT NULL DEFAULT 'manual_adjust',
      `reason` VARCHAR(255) DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_product_id` (`product_id`),
      KEY `idx_changed_by` (`changed_by`),
      KEY `idx_created_at` (`created_at`),
      CONSTRAINT `fk_pph_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_pph_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "TABLE_CREATED_SUCCESSFULLY\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
