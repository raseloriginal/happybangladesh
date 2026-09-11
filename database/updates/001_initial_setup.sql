-- Initial schema definition update
CREATE TABLE IF NOT EXISTS `database_migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration_file` VARCHAR(255) NOT NULL UNIQUE,
    `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
    `error_message` TEXT NULL,
    `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
