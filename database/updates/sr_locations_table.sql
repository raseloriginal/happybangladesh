-- ============================================================
--  HappyBangladesh DMS — SR Location Tracking
--  Run this file to add GPS tracking support
-- ============================================================

-- ── SR Locations (live + history) ────────────────────────────
CREATE TABLE IF NOT EXISTS `sr_locations` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sr_id`      INT UNSIGNED NOT NULL,
    `lat`        DECIMAL(10,7) NOT NULL,
    `lng`        DECIMAL(10,7) NOT NULL,
    `address`    VARCHAR(500)  DEFAULT NULL COMMENT 'Reverse-geocoded address from client',
    `accuracy`   FLOAT         DEFAULT NULL COMMENT 'GPS accuracy in metres',
    `recorded_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the SR device reported the location',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sr_recorded` (`sr_id`, `recorded_at`),
    INDEX `idx_recorded`    (`recorded_at`),
    FOREIGN KEY (`sr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
