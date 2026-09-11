# 🚀 HappyBangladesh DMS — Remediation Roadmap to 9/10

> **Goal:** Take the platform from 5.9/10 → 9/10 investor rating
> **Based on:** Full static code audit of all PHP controllers, middleware, config, schema, CI/CD, and frontend
> **Format:** Phased plan with exact file targets, effort estimates, and score impact

---

## 📊 Current vs Target Scorecard

| Category | Current | Target | Gap |
|---|---|---|---|
| Security | 4.5/10 | 9/10 | +4.5 |
| Architecture & Code Quality | 6/10 | 9/10 | +3 |
| Testing | 0/10 | 8/10 | +8 |
| Scalability | 4/10 | 8/10 | +4 |
| Business Logic | 7/10 | 9/10 | +2 |
| UX/UI | 6/10 | 8.5/10 | +2.5 |
| Performance | 5/10 | 8/10 | +3 |
| Documentation | 3/10 | 8/10 | +5 |
| DevOps/Infrastructure | 5/10 | 9/10 | +4 |
| Tech Debt | 4/10 | 9/10 | +5 |
| **OVERALL** | **5.9/10** | **9/10** | **+3.1** |

---

## 🗺️ Phases Overview

```
Phase 1 — Emergency          (1–2 days)   → Score: 5.9 → 6.8
Phase 2 — Architecture       (1–2 weeks)  → Score: 6.8 → 7.8
Phase 3 — Engineering        (2–3 weeks)  → Score: 7.8 → 8.5
Phase 4 — Product Polish     (1–2 weeks)  → Score: 8.5 → 8.8
Phase 5 — Scale-Ready        (2–3 weeks)  → Score: 8.8 → 9.0+
```

---

## 🔴 PHASE 1 — Emergency (Do Today, Before Any Demo)

> **Score impact: +0.9** | **Effort: ~4–8 hours**

### 1.1 Delete All Debug & Test Files

These files are publicly accessible and must be removed from production and from the git repository immediately.

**Files to DELETE:**
```
public/cleanup.php          ← Can wipe entire DB, unauthenticated
public/debug.php            ← Leaks file system paths
public/debug_server.php     ← Dumps entire $_SERVER to public
public/clear_opcache.php    ← Unauthenticated OPCache clearing
scratch.php                 ← Raw SQL dump of approvals table
test_db.php                 ← Raw SQL dump of van_stock data
test_pdo.php                ← DB connection test script
```

**Git commands:**
```bash
git rm public/cleanup.php public/debug.php public/debug_server.php public/clear_opcache.php
git rm scratch.php test_db.php test_pdo.php
git commit -m "security: remove all debug and test scripts from public"
git push
```

**Also clean up junk files from repo root:**
```
temp.md
temp_qty_search.txt
dispatch_return_analysis_report.txt
rendered_delivery.html
ai_api_doc.md
WEBSITE_REVIEW.md
add_dealer_auth.php
undo_feature_migration.sql
db_schema.php
```

---

### 1.2 Remove Deploy Backdoor from index.php

**File:** `public/index.php`, Lines 13–21

**Remove this block:**
```php
// ── Quick deploy verification (remove after confirming) ───────
if (isset($_GET['_v'])) {
    header('Content-Type: text/plain');
    echo 'DEPLOYED_VERSION=v7' . PHP_EOL;
    ...
    exit;
}
```

Also update the file header comment — remove `DEPLOYED: v7 — rsync fix`.

---

### 1.3 Move ALL Credentials to Environment Variables

**This is the most critical security fix.**

**Current problem:** `app/Config/config.php` has 3 live production passwords hardcoded in git.

**Step 1 — Create `.env` file (never committed):**
```ini
# .env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=happybd
DB_USER=happybd
DB_PASS=your_new_rotated_password_here
APP_ENV=production
```

**Step 2 — Create `app/Config/env.php`:**
```php
<?php
function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}
```

**Step 3 — Update `config.php`:**
```php
require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'happybangladesh_dms'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DEBUG_MODE', env('APP_ENV', 'production') !== 'production');
```

**Step 4 — Update `.gitignore`:**
```
.env
.env.*
!.env.example
```

**Step 5 — Create `.env.example` (committed, no real values):**
```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=happybangladesh_dms
DB_USER=root
DB_PASS=
APP_ENV=development
```

**Step 6 — ROTATE ALL COMPROMISED PASSWORDS** on the live server. The old passwords in git history are permanently compromised.

---

### 1.4 Fix SSL Verification

**Files:** `modules/Admin/AdminController.php` line 158, `modules/Manager/ManagerController.php` line 2796

**Remove:**
```php
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => false,
```

**Replace with:**
```php
CURLOPT_SSL_VERIFYPEER => true,
CURLOPT_SSL_VERIFYHOST => 2,
```

---

### 1.5 Add Login Rate Limiting

**New file:** `app/Core/RateLimiter.php`
```php
<?php
class RateLimiter
{
    private static string $storagePath = '';

    private static function path(string $key): string
    {
        $dir = ROOT_PATH . '/storage/rate_limits';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir . '/' . md5($key) . '.json';
    }

    public static function tooManyAttempts(string $key, int $max = 5, int $decay = 300): bool
    {
        $file = self::path($key);
        if (!file_exists($file)) return false;
        $data = json_decode(file_get_contents($file), true) ?? [];
        if (($data['locked_until'] ?? 0) > time()) return true;
        if ((time() - ($data['first_attempt'] ?? 0)) > $decay) return false;
        return ($data['attempts'] ?? 0) >= $max;
    }

    public static function hit(string $key, int $max = 5, int $decay = 300): void
    {
        $file = self::path($key);
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : ['first_attempt' => time()];
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        if ($data['attempts'] >= $max) $data['locked_until'] = time() + $decay;
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public static function clear(string $key): void
    {
        $file = self::path($key);
        if (file_exists($file)) @unlink($file);
    }

    public static function retriesLeft(string $key, int $max = 5): int
    {
        $file = self::path($key);
        if (!file_exists($file)) return $max;
        $data = json_decode(file_get_contents($file), true) ?? [];
        return max(0, $max - ($data['attempts'] ?? 0));
    }
}
```

**Update `modules/Auth/AuthController.php` — `processRoleLogin()`:**
```php
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = "login:{$role}:{$ip}";

if (RateLimiter::tooManyAttempts($rateLimitKey)) {
    $this->flash('error', 'Too many failed attempts. Try again in 5 minutes.');
    $this->redirect($loginUrl);
    return;
}

// ... on failed login:
RateLimiter::hit($rateLimitKey);

// ... on successful login:
RateLimiter::clear($rateLimitKey);
```

---

## 🟠 PHASE 2 — Architecture Refactoring (Week 1–2)

> **Score impact: +1.0** | **Effort: ~3–5 days**

### 2.1 Break Up God Controllers

**`modules/Manager/ManagerController.php` is 3,886 lines.** Split into:

```
modules/Manager/
├── ManagerController.php          ← Dashboard only (~80 lines)
├── ProductController.php          ← Products + categories + lots + stock
├── InventoryController.php        ← Inventory management
├── DispatchController.php         ← All dispatch workflow (biggest split)
├── OperationsController.php       ← Operations panel
├── AttendanceController.php       ← Attendance + QR codes
├── SettlementController.php       ← DSR settlements
└── ReportController.php           ← Reports, readysale
```

**`modules/Admin/AdminController.php` is 2,050 lines.** Split into:

```
modules/Admin/
├── AdminController.php            ← Dashboard + AI (~120 lines)
├── UserAdminController.php        ← Managers/SRs/DSRs/Dealers CRUD
├── SystemAdminController.php      ← Warehouses/Approvals/Sessions/Sync
└── TrackingController.php         ← SR/DSR tracking APIs + custom areas
```

**Update `public/index.php`** to point routes at the new controllers.

---

### 2.2 Move Schema Migrations Out of Controllers

**Tables being created inside controllers on every request (guarded by static flag, but still wrong):**

| Controller | Tables Created Inline |
|---|---|
| `SRController.php` | `retailers`, `sr_order_cutoffs`, `order_archive`, `sr_visits` |
| `ManagerController.php` | `attendance_qr_codes`, `dsr_attendance` |
| `AdminController.php` | `database_migrations` |

**Fix:** Move all these `CREATE TABLE IF NOT EXISTS` statements to `database/migrations/schema.sql`.
Remove `ensureRetailersTable()` and any equivalent init methods from all controllers.

---

### 2.3 Create a Service Layer

**New directory:** `app/Services/`

| Service | Extracts From |
|---|---|
| `OrderService.php` | `SRController::storeOrder()`, `OperationsController` |
| `DispatchService.php` | `DispatchController` business logic |
| `InventoryService.php` | `InventoryController`, van stock calculations |
| `AttendanceService.php` | `AttendanceController` QR + status logic |

Controllers become thin: validate input → call service → render response.

---

### 2.4 Create Input Validation Layer

**New file:** `app/Core/Validator.php`

```php
<?php
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data) { $this->data = $data; }

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address.';
        }
        return $this;
    }

    public function phone(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !preg_match('/^[0-9+\-\s()]{7,20}$/', $val)) {
            $this->errors[$field] = 'Please enter a valid phone number.';
        }
        return $this;
    }

    public function numeric(string $field, float $min = 0, ?float $max = null): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !is_numeric($val)) {
            $this->errors[$field] = 'Must be a number.';
        } elseif (is_numeric($val) && (float)$val < $min) {
            $this->errors[$field] = "Must be at least {$min}.";
        } elseif ($max !== null && is_numeric($val) && (float)$val > $max) {
            $this->errors[$field] = "Must not exceed {$max}.";
        }
        return $this;
    }

    public function minLength(string $field, int $min): static
    {
        $val = $this->data[$field] ?? '';
        if (mb_strlen((string)$val) < $min) {
            $this->errors[$field] = "Must be at least {$min} characters.";
        }
        return $this;
    }

    public function date(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !\DateTime::createFromFormat('Y-m-d', $val)) {
            $this->errors[$field] = 'Invalid date format (YYYY-MM-DD expected).';
        }
        return $this;
    }

    public function passes(): bool { return empty($this->errors); }
    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(): string { return reset($this->errors) ?: ''; }
}
```

**Usage — replace all bare `trim()` validation in controllers:**
```php
public function warehouseStore(): void
{
    $this->verifyCsrf();
    $v = new Validator($_POST);
    $v->required('name', 'Warehouse name')
      ->minLength('name', 3)
      ->required('location', 'Location');

    if ($v->fails()) {
        $this->flash('error', $v->firstError());
        $this->redirect('admin/warehouses/create');
        return;
    }
    // ... proceed
}
```

---

### 2.5 Fix Missing CSRF + Other Quick Bugs

**`modules/Admin/AdminController.php` line 230 — `warehouseDelete` has no CSRF:**
```php
public function warehouseDelete(string $id): void
{
    $this->verifyCsrf();  // ← ADD THIS
    $this->db->prepare("UPDATE warehouses SET status=0 WHERE id=?")->execute([$id]);
```

**`modules/DSR/DSRController.php` line 17 — duplicate DB init:**
```php
// Remove this duplicate line:
$this->db = Database::getInstance();
$this->db = Database::getInstance();  // ← DELETE
```

**Audit all `*Delete` methods** across all controllers — every POST handler must call `$this->verifyCsrf()` first.

---

### 2.6 Wire Up or Remove the `permissions` Table

The schema defines a `permissions` table (can_view, can_create, can_edit, can_delete per module, per role), but zero code reads from it.

**Option A — Wire it up:**
Extend `RoleMiddleware::check()` to accept a module + permission type:
```php
RoleMiddleware::check(ROLE_MANAGER, 'products', 'can_edit');
```

**Option B — Remove it from schema** and document the decision explicitly. Dead schema = confusion during due diligence.

---

## 🟡 PHASE 3 — Engineering Practices (Week 2–3)

> **Score impact: +0.7** | **Effort: ~1 week**

### 3.1 Add PHPUnit Test Suite

**Install:**
```bash
composer require --dev phpunit/phpunit
```

**Minimum test targets (45+ tests):**

```
tests/
├── Unit/
│   ├── ValidatorTest.php          ← 12 tests: all validation rules
│   ├── HelpersTest.php            ← 8 tests: money(), date(), e(), CSRF
│   ├── CacheTest.php              ← 8 tests: set/get/remember/forget/gc
│   └── RateLimiterTest.php        ← 8 tests: hit/clear/lock/unlock
├── Integration/
│   ├── AuthTest.php               ← 6 tests: login success/fail/wrong role
│   └── OrderServiceTest.php       ← 5 tests: place, validate, rollback
└── bootstrap.php
```

**Update CI/CD — `.github/workflows/deploy.yml`:**
```yaml
- name: Run Tests
  run: ./vendor/bin/phpunit --stop-on-failure

# Deploy step only runs after tests pass (GitHub Actions sequential jobs)
```

---

### 3.2 Add Structured Logging

**New file:** `app/Core/Logger.php`
```php
<?php
class Logger
{
    private static function write(string $level, string $msg, array $ctx = []): void
    {
        $path = ROOT_PATH . '/storage/logs';
        if (!is_dir($path)) @mkdir($path, 0755, true);
        $file = $path . '/app-' . date('Y-m-d') . '.log';
        $ctx = empty($ctx) ? '' : ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE);
        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        file_put_contents($file, "[".date('Y-m-d H:i:s')."] [{$level}] [{$ip}] {$msg}{$ctx}\n", FILE_APPEND | LOCK_EX);
    }

    public static function info(string $msg, array $ctx = []): void    { self::write('INFO',  $msg, $ctx); }
    public static function warning(string $msg, array $ctx = []): void { self::write('WARN',  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void   { self::write('ERROR', $msg, $ctx); }
    public static function debug(string $msg, array $ctx = []): void   { if (DEBUG_MODE) self::write('DEBUG', $msg, $ctx); }

    public static function exception(\Throwable $e): void
    {
        self::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
}
```

**Add global exception handler to `public/index.php`:**
```php
set_exception_handler(function (\Throwable $e) {
    Logger::exception($e);
    if (DEBUG_MODE) throw $e;
    http_response_code(500);
    echo '<h1>500 — Internal Server Error</h1>';
    exit;
});
```

---

### 3.3 Add Health Check Endpoint

**Add to `public/index.php`:**
```php
$router->get('/health', function () {
    try {
        Database::getInstance()->query('SELECT 1');
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbOk = false;
    }
    http_response_code($dbOk ? 200 : 503);
    header('Content-Type: application/json');
    echo json_encode([
        'status'    => $dbOk ? 'ok' : 'degraded',
        'database'  => $dbOk ? 'connected' : 'error',
        'version'   => APP_VERSION,
        'env'       => APP_ENV,
        'timestamp' => date('c'),
    ]);
    exit;
});
```

---

### 3.4 Implement Password Reset

**Add table to `database/migrations/schema.sql`:**
```sql
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(180) NOT NULL,
    `token`      VARCHAR(64)  NOT NULL UNIQUE,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB;
```

**Add routes:**
```php
$router->get( '/reset-password/{token}', ['AuthController', 'showReset']);
$router->post('/reset-password/{token}', ['AuthController', 'processReset']);
```

**`AuthController::forgot()`** — generate token, store in DB, send email via `mail()`. Even a basic implementation beats "not implemented."

---

### 3.5 Update Package.json Scripts

```json
{
  "scripts": {
    "test":  "./vendor/bin/phpunit",
    "build": "npx tailwindcss -i ./tailwind-input.css -o ./public/assets/css/tailwind.css --minify",
    "dev":   "npx tailwindcss -i ./tailwind-input.css -o ./public/assets/css/tailwind.css --watch"
  }
}
```

---

### 3.6 Create CHANGELOG.md

```markdown
# Changelog

## [Unreleased]
### Security
- Move all credentials to .env (no more hardcoded passwords in git)
- Remove unauthenticated debug/cleanup scripts
- Add login rate limiting (5 attempts / 5 minutes per IP)
- Fix SSL verification disabled in outbound API calls

### Added
- Input Validator class with chainable rules
- Structured Logger with daily log rotation
- RateLimiter for login brute-force protection
- /health and /ping monitoring endpoints
- PHPUnit test suite (45+ tests)
- Password reset via email

### Fixed  
- DSRController duplicate DB initialization (line 17)
- Missing CSRF on warehouseDelete
- Duplicate CSS comment in app.css

## [1.0.0] — 2026-07-01
- Initial release
```

---

## 🟢 PHASE 4 — Product Polish (Week 3–4)

> **Score impact: +0.3** | **Effort: ~4–5 days**

### 4.1 Redesign Login Pages

**File:** `app/Views/layouts/auth.php` — rewrite with glassmorphism design:
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e1b4b 100%);
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
    }
    .auth-card {
      background: rgba(255,255,255,0.05);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px; padding: 40px;
      width: 100%; max-width: 420px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.5);
      animation: slideUp 0.4s ease;
      color: #f1f5f9;
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="auth-card"><?= $content ?></div>
</body>
</html>
```

---

### 4.2 Add Dark Mode

**File:** `public/assets/css/app.css` — add at end:
```css
@media (prefers-color-scheme: dark) {
  body { background: #0f172a !important; color: #e2e8f0; }
  .sidebar { background: #1e293b !important; border-right: 1px solid #334155; }
  .sidebar-link { color: #94a3b8 !important; }
  .sidebar-link:hover { background: rgba(37,99,235,0.15) !important; color: #93c5fd !important; }
}
```

---

### 4.3 Fix CSS Issues

- **Remove duplicate comment** in `app.css` line 6: `/* ── Sidebar ─── */` appears twice
- **Purge unused Tailwind** via `npm run build` — target: `sr_app.css` from 70 KB → < 15 KB
- **Audit all views** for CDN Tailwind references: `grep -r "cdn.tailwindcss.com" .`

---

### 4.4 Add AJAX Loading State

**`app/Views/layouts/main.php`** — add before `</body>`:
```html
<div id="ajax-loader" style="display:none;position:fixed;top:0;left:0;right:0;height:3px;
     background:linear-gradient(90deg,#2563eb,#7c3aed);z-index:9999;"></div>
```

**`public/assets/js/app.js`** — intercept fetch:
```javascript
const loader = document.getElementById('ajax-loader');
if (loader) {
    const orig = window.fetch;
    window.fetch = (...args) => {
        loader.style.display = 'block';
        return orig(...args).finally(() => loader.style.display = 'none');
    };
}
```

---

## 🔵 PHASE 5 — Scale-Ready (Week 4–6)

> **Score impact: +0.2** | **Effort: ~2 weeks**

### 5.1 Add Database Indexes

**Add to `database/migrations/schema.sql`:**
```sql
-- Frequently queried columns missing indexes
ALTER TABLE `orders`             ADD INDEX `idx_status`      (`status`);
ALTER TABLE `orders`             ADD INDEX `idx_created_at`  (`created_at`);
ALTER TABLE `dispatch_schedules` ADD INDEX `idx_dsr_date`    (`dsr_id`, `dispatch_date`);
ALTER TABLE `attendance`         ADD INDEX `idx_date`        (`date`);
ALTER TABLE `activity_logs`      ADD INDEX `idx_created_at`  (`created_at`);
ALTER TABLE `expenses`           ADD INDEX `idx_dsr_date`    (`dsr_id`, `date`);
```

---

### 5.2 Add API Version Prefix

```php
// Before:
$router->get('/admin/api/orders', ['AdminController', 'apiOrders']);

// After:
$router->get('/admin/api/v1/orders', ['AdminController', 'apiOrders']);
```

Signals engineering maturity. Allows future breaking changes without disruption.

---

### 5.3 Add Security Headers to .htaccess

**File:** `public/.htaccess` — add:
```apache
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Permissions-Policy "geolocation=(self), camera=(self), microphone=()"
</IfModule>
```

---

### 5.4 Write Professional README

Replace current minimal README with:
1. Project overview + feature list
2. Architecture diagram (text-based is fine)
3. Tech stack with versions
4. Prerequisites
5. Installation (numbered, copy-pasteable)
6. Environment setup (`.env.example` reference)
7. Running tests (`./vendor/bin/phpunit`)
8. Deployment (CI/CD explanation)
9. Role documentation (what Admin/Manager/SR/DSR/Dealer can each do)
10. Contributing guide
11. License

---

## 📋 Master Checklist

### ✅ Phase 1 — Emergency
- [ ] `git rm public/cleanup.php public/debug.php public/debug_server.php public/clear_opcache.php`
- [ ] `git rm scratch.php test_db.php test_pdo.php`
- [ ] Remove junk files from root (temp.md, dispatch_report.txt, rendered_delivery.html, ai_api_doc.md, WEBSITE_REVIEW.md, add_dealer_auth.php, undo_feature_migration.sql, db_schema.php)
- [ ] Remove `?_v=` block from `public/index.php` lines 13–21
- [ ] Remove `DEPLOYED: v7 — rsync fix` from `index.php` header
- [ ] Create `app/Config/env.php` (env loader)
- [ ] Create `.env` with real values
- [ ] Create `.env.example` with empty placeholders
- [ ] Update `.gitignore` to exclude `.env`
- [ ] Update `app/Config/config.php` to use `env()`
- [ ] **ROTATE ALL 3 PRODUCTION DATABASE PASSWORDS**
- [ ] Fix `CURLOPT_SSL_VERIFYPEER => false` in `AdminController.php` line 158
- [ ] Fix `CURLOPT_SSL_VERIFYPEER => false` in `ManagerController.php` line 2796
- [ ] Create `app/Core/RateLimiter.php`
- [ ] Add rate limiting to `AuthController::processRoleLogin()`
- [ ] Add `storage/rate_limits/` to `.gitignore`

### ✅ Phase 2 — Architecture
- [ ] Split `ManagerController.php` into 8 focused files
- [ ] Split `AdminController.php` into 4 focused files
- [ ] Update all routes in `public/index.php` to new controllers
- [ ] Move `ensureRetailersTable()` tables to `schema.sql`
- [ ] Remove `ensureRetailersTable()` from `SRController`
- [ ] Remove inline `CREATE TABLE` from `ManagerController` and `DSRController`
- [ ] Create `app/Services/OrderService.php`
- [ ] Create `app/Services/DispatchService.php`
- [ ] Create `app/Services/InventoryService.php`
- [ ] Create `app/Core/Validator.php`
- [ ] Replace bare validation in all CRUD methods with `Validator`
- [ ] Add `verifyCsrf()` to `AdminController::warehouseDelete()` (line 230)
- [ ] Audit all delete/update POST handlers for missing CSRF
- [ ] Fix duplicate `$this->db = Database::getInstance()` in `DSRController.php` line 17
- [ ] Decide: wire up `permissions` table OR remove it from schema

### ✅ Phase 3 — Engineering
- [ ] `composer require --dev phpunit/phpunit`
- [ ] Create `phpunit.xml`
- [ ] Create `tests/bootstrap.php`
- [ ] Write `tests/Unit/ValidatorTest.php` (12+ tests)
- [ ] Write `tests/Unit/HelpersTest.php` (8+ tests)
- [ ] Write `tests/Unit/CacheTest.php` (8+ tests)
- [ ] Write `tests/Unit/RateLimiterTest.php` (8+ tests)
- [ ] Write `tests/Integration/AuthTest.php` (6+ tests)
- [ ] Write `tests/Integration/OrderServiceTest.php` (5+ tests)
- [ ] Create `app/Core/Logger.php`
- [ ] Add global exception handler to `public/index.php`
- [ ] Add `storage/logs/*.log` to `.gitignore`
- [ ] Add `/health` and `/ping` routes to `public/index.php`
- [ ] Add `- name: Run Tests` step to `.github/workflows/deploy.yml`
- [ ] Implement `AuthController::forgot()` with real email sending
- [ ] Add `password_resets` table to `schema.sql`
- [ ] Add reset password routes and views
- [ ] Update `package.json` scripts (test, build, dev)
- [ ] Create `CHANGELOG.md`

### ✅ Phase 4 — Product Polish
- [ ] Rewrite `app/Views/layouts/auth.php` with glassmorphism design
- [ ] Update `login_admin.php`, `login_manager.php`, `login_sr.php`, `login_dsr.php` views
- [ ] Add dark mode CSS block to `public/assets/css/app.css`
- [ ] Add dark mode toggle button to `app/Views/components/header.php`
- [ ] Remove duplicate `/* ── Sidebar */` comment (line 6 of `app.css`)
- [ ] Run `npm run build` — verify `sr_app.css` shrinks from 70 KB
- [ ] `grep -r "cdn.tailwindcss.com" .` — remove any remaining CDN refs
- [ ] Add AJAX loading bar to `app/Views/layouts/main.php`
- [ ] Add fetch interceptor to `public/assets/js/app.js`

### ✅ Phase 5 — Scale-Ready
- [ ] Add 6 missing indexes to `database/migrations/schema.sql`
- [ ] Add `/api/v1/` prefix to all internal API routes
- [ ] Add security headers block to `public/.htaccess`
- [ ] Write professional `README.md` (10+ sections)
- [ ] Tag `v1.1.0` git release after Phase 3 complete

---

## 🎯 Final Target: 9/10

| Category | After All Phases |
|---|---|
| Security | **9/10** |
| Architecture & Code Quality | **9/10** |
| Testing | **8/10** |
| Scalability | **8/10** |
| Business Logic | **9/10** |
| UX/UI | **8.5/10** |
| Performance | **8/10** |
| Documentation | **8/10** |
| DevOps/Infrastructure | **9/10** |
| Tech Debt | **9/10** |
| **OVERALL** | **✅ 9/10** |

---

> **Phase 1 alone** takes you from "do not invest" → "interesting, needs work."
> **Phases 1–3** take you to "credible team, fundable."
> **All 5 phases** → "technically sound, investor-ready."
