# HappyBangladesh DMS — FMCG Distribution Management System

A modular MVC PHP/MySQL FMCG Distribution Management System.

## Quick Start

### 1. Database Setup
1. Open **phpMyAdmin** → Import `database/migrations/schema.sql`
2. Import `database/seeders/seed.sql`

### 2. Configuration
Edit `app/Config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'happybangladesh_dms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/happybangladesh/public');
```

### 3. Apache
Enable `mod_rewrite` in XAMPP. Ensure `AllowOverride All` is set in httpd.conf.

### 4. Login
Visit: `http://localhost/happybangladesh/public/`

| Role    | Email              | Password    |
|---------|-------------------|-------------|
| Admin   | admin@dms.com     | password123 |
| Manager | manager@dms.com   | password123 |
| SR      | sr@dms.com        | password123 |
| DSR     | dsr@dms.com       | password123 |

## Project Structure
```
happybangladesh/
├── app/
│   ├── Config/config.php          ← DB & app settings
│   ├── Core/                      ← MVC Framework
│   ├── Middleware/                ← Auth & Role checks
│   └── Views/                     ← Layouts & components
├── modules/
│   ├── Auth/                      ← Login/Logout
│   ├── Admin/                     ← Admin panel
│   ├── Manager/                   ← Manager panel
│   ├── SR/                        ← SR panel
│   └── DSR/                       ← DSR panel
├── public/
│   ├── index.php                  ← Front controller
│   ├── .htaccess                  ← URL rewriting
│   └── assets/                    ← CSS, JS
├── database/
│   ├── migrations/schema.sql      ← DB schema (22 tables)
│   └── seeders/seed.sql           ← Demo data
└── logs/
```

## Tech Stack
- PHP 8+ with PDO
- MySQL 8
- Tailwind CSS (CDN)
- Font Awesome 6 (CDN)
- Vanilla JavaScript
- Session-based auth with CSRF protection
