-- ============================================================
--  HappyBangladesh DMS — Database Schema
--  Run this file first, then run seed.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `happybangladesh_dms`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `happybangladesh_dms`;

-- ── Roles ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(50)  NOT NULL,
    `slug`       VARCHAR(50)  NOT NULL UNIQUE,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Warehouses ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(120) NOT NULL,
    `location`   VARCHAR(255) NOT NULL,
    `phone`      VARCHAR(30)  DEFAULT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=active,0=inactive',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id`      TINYINT UNSIGNED NOT NULL,
    `warehouse_id` INT UNSIGNED     DEFAULT NULL,
    `company_id`   INT UNSIGNED     DEFAULT NULL,
    `name`         VARCHAR(120)     NOT NULL,
    `email`        VARCHAR(180)     NOT NULL UNIQUE,
    `phone`        VARCHAR(30)      DEFAULT NULL,
    `password`     VARCHAR(255)     NOT NULL,
    `avatar`       VARCHAR(255)     DEFAULT NULL,
    `status`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`)      REFERENCES `roles`(`id`)      ON DELETE RESTRICT,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`)   REFERENCES `companies`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Permissions ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id`    TINYINT UNSIGNED NOT NULL,
    `module`     VARCHAR(80)      NOT NULL,
    `can_view`   TINYINT(1)       NOT NULL DEFAULT 0,
    `can_create` TINYINT(1)       NOT NULL DEFAULT 0,
    `can_edit`   TINYINT(1)       NOT NULL DEFAULT 0,
    `can_delete` TINYINT(1)       NOT NULL DEFAULT 0,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Main Categories ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `main_categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150) NOT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Categories ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`       INT UNSIGNED DEFAULT NULL,
    `main_category_id` INT UNSIGNED DEFAULT NULL,
    `name`             VARCHAR(150) NOT NULL,
    `status`           TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`main_category_id`) REFERENCES `main_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Companies ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `companies` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150) NOT NULL,
    `contact`    VARCHAR(80)  DEFAULT NULL,
    `email`      VARCHAR(180) DEFAULT NULL,
    `phone`      VARCHAR(30)  DEFAULT NULL,
    `address`    TEXT         DEFAULT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Dealers ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `dealers` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id`     INT UNSIGNED DEFAULT NULL,
    `name`             VARCHAR(150) NOT NULL,
    `business_name`    VARCHAR(150) DEFAULT NULL,
    `phone`            VARCHAR(30)  DEFAULT NULL,
    `address`          TEXT         DEFAULT NULL,
    `lat`              DECIMAL(10,7) DEFAULT NULL,
    `lng`              DECIMAL(10,7) DEFAULT NULL,
    `trade_license`    VARCHAR(100) DEFAULT NULL,
    `happy_commission` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `status`           TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Dealer Companies & SR Assignments ───────────────────────────
CREATE TABLE IF NOT EXISTS `dealer_companies` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dealer_id`  INT UNSIGNED NOT NULL,
    `company_id` INT UNSIGNED NOT NULL,
    `sr_id`      INT UNSIGNED NOT NULL,
    FOREIGN KEY (`dealer_id`)  REFERENCES `dealers`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sr_id`)      REFERENCES `users`(`id`)     ON DELETE RESTRICT,
    UNIQUE KEY `uq_dealer_company` (`dealer_id`, `company_id`)
) ENGINE=InnoDB;
-- ── Products ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`        INT UNSIGNED DEFAULT NULL,
    `category_id`       INT UNSIGNED DEFAULT NULL,
    `name`              VARCHAR(200) NOT NULL,
    `sku`               VARCHAR(80)  NOT NULL UNIQUE,
    `unit`              VARCHAR(30)  NOT NULL DEFAULT 'pcs',
    `box_type`          VARCHAR(50)  DEFAULT NULL,
    `pieces_per_box`    INT UNSIGNED NOT NULL DEFAULT 1,
    `dealer_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `buying_price`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `price`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `description`       TEXT         DEFAULT NULL,
    `image`             VARCHAR(255) DEFAULT NULL,
    `status`            TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`)  REFERENCES `companies`(`id`)  ON DELETE SET NULL,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Lots ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lots` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`        INT UNSIGNED NOT NULL,
    `lot_date`          DATE         DEFAULT NULL,
    `lot_number`        VARCHAR(80)  DEFAULT NULL,
    `manufacturing_date` DATE        DEFAULT NULL,
    `expiry_date`        DATE         DEFAULT NULL,
    `qty_boxes`         INT UNSIGNED NOT NULL DEFAULT 0,
    `buying_price`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `qty_pieces`        INT UNSIGNED NOT NULL DEFAULT 0,
    `quantity`          INT UNSIGNED NOT NULL DEFAULT 0,
    `notes`             TEXT         DEFAULT NULL,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Inventory ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `inventory` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`   INT UNSIGNED NOT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `lot_id`       INT UNSIGNED DEFAULT NULL,
    `qty_boxes`    INT UNSIGNED NOT NULL DEFAULT 0,
    `qty_pieces`   INT UNSIGNED NOT NULL DEFAULT 0,
    `last_updated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`)   REFERENCES `products`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lot_id`)       REFERENCES `lots`(`id`)       ON DELETE CASCADE,
    UNIQUE KEY `uq_inventory_lot` (`product_id`, `warehouse_id`, `lot_id`)
) ENGINE=InnoDB;

-- ── Retailers ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `retailers` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(255) NOT NULL,
    `phone`       VARCHAR(30)  DEFAULT NULL,
    `lat`         DECIMAL(10,7) DEFAULT NULL,
    `lng`         DECIMAL(10,7) DEFAULT NULL,
    `address`     TEXT         DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_lat_lng` (`lat`, `lng`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Orders ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sr_id`        INT UNSIGNED NOT NULL,
    `dealer_id`    INT UNSIGNED DEFAULT NULL,
    `retailer_id`  INT UNSIGNED DEFAULT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `status`       ENUM('pending','confirmed','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `total_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `notes`        TEXT          DEFAULT NULL,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`sr_id`)        REFERENCES `users`(`id`)       ON DELETE RESTRICT,
    FOREIGN KEY (`dealer_id`)    REFERENCES `dealers`(`id`)     ON DELETE SET NULL,
    FOREIGN KEY (`retailer_id`)  REFERENCES `retailers`(`id`)   ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── Order Items ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `lot_id`      INT UNSIGNED DEFAULT NULL,
    `quantity`    INT UNSIGNED NOT NULL,
    `unit_price`  DECIMAL(12,2) NOT NULL,
    `total_price` DECIMAL(14,2) NOT NULL,
    FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`lot_id`)     REFERENCES `lots`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Dispatches ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `dispatches` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`      INT UNSIGNED DEFAULT NULL,
    `dsr_id`        INT UNSIGNED NOT NULL,
    `warehouse_id`  INT UNSIGNED NOT NULL,
    `dispatch_date` DATE         NOT NULL,
    `status`        ENUM('pending','in_transit','delivered','partial','returned') NOT NULL DEFAULT 'pending',
    `paid_amount`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `notes`         TEXT         DEFAULT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`)     REFERENCES `orders`(`id`)     ON DELETE SET NULL,
    FOREIGN KEY (`dsr_id`)       REFERENCES `users`(`id`)      ON DELETE RESTRICT,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── Dispatch Items ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `dispatch_items` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dispatch_id` INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `lot_id`      INT UNSIGNED DEFAULT NULL,
    `quantity`    INT UNSIGNED NOT NULL,
    `delivered_quantity` INT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`)   ON DELETE RESTRICT,
    FOREIGN KEY (`lot_id`)      REFERENCES `lots`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Returns ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `returns` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dispatch_id` INT UNSIGNED DEFAULT NULL,
    `dsr_id`      INT UNSIGNED NOT NULL,
    `return_date` DATE         NOT NULL,
    `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `reason`      TEXT         DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`dsr_id`)      REFERENCES `users`(`id`)      ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── Return Items ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `return_items` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `return_id`  INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `lot_id`     INT UNSIGNED DEFAULT NULL,
    `quantity`   INT UNSIGNED NOT NULL,
    `reason`     VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`return_id`)  REFERENCES `returns`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`lot_id`)     REFERENCES `lots`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Attendance ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `attendance` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `date`       DATE         NOT NULL,
    `check_in`   TIME         DEFAULT NULL,
    `check_out`  TIME         DEFAULT NULL,
    `status`     ENUM('present','absent','late','half_day','holiday') NOT NULL DEFAULT 'present',
    `notes`      TEXT         DEFAULT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_att` (`user_id`, `date`)
) ENGINE=InnoDB;

-- ── Expenses ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `expenses` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dsr_id`      INT UNSIGNED NOT NULL,
    `date`        DATE         NOT NULL,
    `category`    ENUM('fuel','food','toll','repair','other') NOT NULL DEFAULT 'other',
    `amount`      DECIMAL(10,2) NOT NULL,
    `description` TEXT          DEFAULT NULL,
    `receipt_url` VARCHAR(255)  DEFAULT NULL,
    `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`dsr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Ready Sales ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `readysales` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `product_id`   INT UNSIGNED NOT NULL,
    `lot_id`       INT UNSIGNED DEFAULT NULL,
    `quantity`     INT UNSIGNED NOT NULL DEFAULT 0,
    `price`        DECIMAL(12,2) NOT NULL,
    `status`       TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)   REFERENCES `products`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`lot_id`)       REFERENCES `lots`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Van Stock ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `van_stock` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dsr_id`     INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `lot_id`     INT UNSIGNED DEFAULT NULL,
    `quantity`   INT          NOT NULL DEFAULT 0,
    `loaded_at`  DATE         DEFAULT NULL,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`dsr_id`)     REFERENCES `users`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lot_id`)     REFERENCES `lots`(`id`)     ON DELETE SET NULL,
    UNIQUE KEY `uq_van` (`dsr_id`, `product_id`, `lot_id`)
) ENGINE=InnoDB;

-- ── Sales Reports ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sales_reports` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id`      INT UNSIGNED DEFAULT NULL,
    `date`              DATE         NOT NULL,
    `total_orders`      INT UNSIGNED NOT NULL DEFAULT 0,
    `total_dispatches`  INT UNSIGNED NOT NULL DEFAULT 0,
    `total_returns`     INT UNSIGNED NOT NULL DEFAULT 0,
    `total_sales`       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Approvals ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `approvals` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requested_by` INT UNSIGNED NOT NULL,
    `approved_by`  INT UNSIGNED DEFAULT NULL,
    `module`       VARCHAR(80)  NOT NULL,
    `action`       ENUM('edit','delete') NOT NULL,
    `record_id`    INT UNSIGNED NOT NULL,
    `old_data`     JSON         DEFAULT NULL,
    `new_data`     JSON         DEFAULT NULL,
    `status`       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `reason`       TEXT         DEFAULT NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`)  REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Activity Logs ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED DEFAULT NULL,
    `action`      VARCHAR(80)  NOT NULL,
    `module`      VARCHAR(80)  NOT NULL,
    `record_id`   INT UNSIGNED DEFAULT NULL,
    `description` TEXT         DEFAULT NULL,
    `ip_address`  VARCHAR(45)  DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Dispatch Schedules (New Dispatch Workflow) ────────────────
CREATE TABLE IF NOT EXISTS `dispatch_schedules` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dsr_id`        INT UNSIGNED NOT NULL,
    `dispatch_date` DATE         NOT NULL,
    `delivery_date` DATE         DEFAULT NULL,
    `status`        ENUM('assigned','organized','dispatched','returned') NOT NULL DEFAULT 'assigned',
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`dsr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Dispatch Schedule SRs (Assignments) ──────────────────────
CREATE TABLE IF NOT EXISTS `dispatch_schedule_srs` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `schedule_id` INT UNSIGNED NOT NULL,
    `sr_id`       INT UNSIGNED NOT NULL,
    FOREIGN KEY (`schedule_id`) REFERENCES `dispatch_schedules`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sr_id`)       REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_schedule_sr` (`schedule_id`, `sr_id`)
) ENGINE=InnoDB;

-- ── Dispatch Extras (Organize Step additions) ────────────────
CREATE TABLE IF NOT EXISTS `dispatch_extras` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `schedule_id` INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `qty_boxes`   INT          NOT NULL DEFAULT 0,
    `qty_pieces`  INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (`schedule_id`) REFERENCES `dispatch_schedules`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Settlements ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settlements` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dsr_id`             INT UNSIGNED NOT NULL,
    `date`               DATE NOT NULL,
    `total_dispatched`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_returned`     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_damage`       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_expense`      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `should_pay`         DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `counted_cash`       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `difference`         DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `cash_breakdown`     JSON DEFAULT NULL,
    `status`             ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `manager_notes`      TEXT DEFAULT NULL,
    `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`dsr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
