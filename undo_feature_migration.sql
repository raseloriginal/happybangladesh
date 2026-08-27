CREATE TABLE IF NOT EXISTS `dispatch_undo_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dsr_id` INT NOT NULL,
  `dispatch_id` VARCHAR(50) NOT NULL,
  `previous_status` VARCHAR(50) DEFAULT NULL,
  `previous_paid_amount` DECIMAL(10,2) DEFAULT '0.00',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_dsr_id` (`dsr_id`),
  INDEX `idx_dispatch_id` (`dispatch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
