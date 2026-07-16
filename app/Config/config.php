<?php

// ─── Path & URL ───────────────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__, 2));          // project root
define('APP_PATH',  ROOT_PATH . '/app');
define('MOD_PATH',  ROOT_PATH . '/modules');
define('PUB_PATH',  ROOT_PATH . '/public');

// Adjust BASE_URL to match your XAMPP virtual-host or sub-folder setup
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
define('BASE_URL', $protocol . $host . '/happybangladesh');

// ─── Application ──────────────────────────────────────────────────────────────
define('APP_NAME', 'HappyBangladesh DMS');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development');   // development | production
define('DEBUG_MODE', true);

// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'happybangladesh_dms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ─── Session ──────────────────────────────────────────────────────────────────
define('SESSION_NAME', 'hb_dms_sess');
define('SESSION_LIFETIME', 7200);   // 2 hours

// ─── Pagination ───────────────────────────────────────────────────────────────
define('PER_PAGE', 15);

// ─── Roles ────────────────────────────────────────────────────────────────────
define('ROLE_ADMIN',   'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_SR',      'sr');
define('ROLE_DSR',     'dsr');

// ─── Error Reporting ──────────────────────────────────────────────────────────
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Dhaka');
