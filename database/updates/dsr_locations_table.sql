-- ============================================================
--  HappyBangladesh DMS — DSR Location Tracking
--  Run this file to add GPS tracking support for DSRs
-- ============================================================

-- ── DSR Locations (live + history) ────────────────────────────
CREATE TABLE IF NOT EXISTS `dsr_locations` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dsr_id`     INT UNSIGNED NOT NULL,
    `lat`        DECIMAL(10,7) NOT NULL,
    `lng`        DECIMAL(10,7) NOT NULL,
    `address`    VARCHAR(500)  DEFAULT NULL COMMENT 'Reverse-geocoded address from client',
    `accuracy`   FLOAT         DEFAULT NULL COMMENT 'GPS accuracy in metres',
    `recorded_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the DSR device reported the location',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_dsr_recorded` (`dsr_id`, `recorded_at`),
    INDEX `idx_recorded`    (`recorded_at`),
    FOREIGN KEY (`dsr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
