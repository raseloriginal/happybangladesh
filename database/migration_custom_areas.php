<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS `custom_areas` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(150) NOT NULL,
      `description` text DEFAULT NULL,
      `type` enum('polygon','rectangle','polyline','circle','marker') NOT NULL DEFAULT 'polygon',
      `coordinates` longtext NOT NULL,
      `stroke_color` varchar(30) DEFAULT '#3b82f6',
      `fill_color` varchar(30) DEFAULT '#93c5fd',
      `fill_opacity` float DEFAULT 0.35,
      `assigned_type` varchar(50) DEFAULT NULL,
      `assigned_id` int(10) unsigned DEFAULT NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $db->exec($sql);
    echo "TABLE_CREATED_SUCCESSFULLY\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
