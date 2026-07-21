<?php
/**
 * ============================================================
 *  HappyBangladesh DMS — Front Controller
 * ============================================================
 */
declare(strict_types=1);

// ── Config ────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/app/Config/config.php';

// ── Core autoloader ───────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $searchPaths = [
        APP_PATH . '/Core/' . $class . '.php',
        APP_PATH . '/Middleware/' . $class . '.php',
        MOD_PATH . '/Auth/'    . $class . '.php',
        MOD_PATH . '/Admin/'   . $class . '.php',
        MOD_PATH . '/Manager/' . $class . '.php',
        MOD_PATH . '/SR/'      . $class . '.php',
        MOD_PATH . '/DSR/'     . $class . '.php',
    ];
    foreach ($searchPaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Bootstrap ─────────────────────────────────────────────────
require_once APP_PATH . '/Core/Helpers.php';
Auth::start();

// ── Router ────────────────────────────────────────────────────
$router = new Router();

// ── Catch URL ─────────────────────────────────────────────────
$url    = $_GET['url'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

// ── Auth routes ───────────────────────────────────────────────
$router->get( '/login',         ['AuthController', 'portal']);
$router->get( '/admin/login',   ['AuthController', 'showLoginAdmin']);
$router->post('/admin/login',   ['AuthController', 'loginAdmin']);
$router->get( '/manager/login', ['AuthController', 'showLoginManager']);
$router->post('/manager/login', ['AuthController', 'loginManager']);
$router->get( '/sr/login',      ['AuthController', 'showLoginSR']);
$router->post('/sr/login',      ['AuthController', 'loginSR']);
$router->get( '/dsr/login',     ['AuthController', 'showLoginDSR']);
$router->post('/dsr/login',     ['AuthController', 'loginDSR']);
$router->get( '/admin/logout',   ['AuthController', 'logout']);
$router->get( '/manager/logout', ['AuthController', 'logout']);
$router->get( '/sr/logout',      ['AuthController', 'logout']);
$router->get( '/dsr/logout',     ['AuthController', 'logout']);

$router->get( '/forgot',   ['AuthController', 'showForgot']);
$router->post('/forgot',   ['AuthController', 'forgot']);
$router->get( '/',         fn() => header('Location: ' . BASE_URL . '/login') ?: exit());

// ── Admin routes ──────────────────────────────────────────────
$router->get( '/admin/dashboard',          ['AdminController', 'dashboard']);

// Warehouses
$router->get( '/admin/warehouses',         ['AdminController', 'warehouses']);
$router->get( '/admin/warehouses/create',  ['AdminController', 'warehouseCreate']);
$router->post('/admin/warehouses/store',   ['AdminController', 'warehouseStore']);
$router->get( '/admin/warehouses/edit/{id}',   ['AdminController', 'warehouseEdit']);
$router->post('/admin/warehouses/update/{id}', ['AdminController', 'warehouseUpdate']);
$router->post('/admin/warehouses/delete/{id}', ['AdminController', 'warehouseDelete']);

// Managers
$router->get( '/admin/managers',           ['AdminController', 'managers']);
$router->get( '/admin/managers/create',    ['AdminController', 'managerCreate']);
$router->post('/admin/managers/store',     ['AdminController', 'managerStore']);
$router->get( '/admin/managers/edit/{id}', ['AdminController', 'managerEdit']);
$router->post('/admin/managers/update/{id}', ['AdminController', 'managerUpdate']);
$router->post('/admin/managers/delete/{id}', ['AdminController', 'managerDelete']);

// SRs
$router->get( '/admin/srs',               ['AdminController', 'srs']);
$router->get( '/admin/srs/create',        ['AdminController', 'srCreate']);
$router->post('/admin/srs/store',         ['AdminController', 'srStore']);
$router->get( '/admin/srs/edit/{id}',     ['AdminController', 'srEdit']);
$router->post('/admin/srs/update/{id}',   ['AdminController', 'srUpdate']);
$router->post('/admin/srs/delete/{id}',   ['AdminController', 'srDelete']);

// DSRs
$router->get( '/admin/dsrs',              ['AdminController', 'dsrs']);
$router->get( '/admin/dsrs/create',       ['AdminController', 'dsrCreate']);
$router->post('/admin/dsrs/store',        ['AdminController', 'dsrStore']);
$router->get( '/admin/dsrs/edit/{id}',    ['AdminController', 'dsrEdit']);
$router->post('/admin/dsrs/update/{id}',  ['AdminController', 'dsrUpdate']);
$router->post('/admin/dsrs/delete/{id}',  ['AdminController', 'dsrDelete']);

// Companies
$router->get( '/admin/companies',              ['AdminController', 'companies']);
$router->get( '/admin/companies/create',       ['AdminController', 'companyCreate']);
$router->post('/admin/companies/store',        ['AdminController', 'companyStore']);
$router->get( '/admin/companies/edit/{id}',    ['AdminController', 'companyEdit']);
$router->post('/admin/companies/update/{id}',  ['AdminController', 'companyUpdate']);
$router->post('/admin/companies/delete/{id}',  ['AdminController', 'companyDelete']);

// Dealers
$router->get( '/admin/dealers',               ['AdminController', 'dealers']);
$router->get( '/admin/dealers/create',        ['AdminController', 'dealerCreate']);
$router->post('/admin/dealers/store',         ['AdminController', 'dealerStore']);
$router->get( '/admin/dealers/edit/{id}',     ['AdminController', 'dealerEdit']);
$router->post('/admin/dealers/update/{id}',   ['AdminController', 'dealerUpdate']);
$router->post('/admin/dealers/delete/{id}',   ['AdminController', 'dealerDelete']);

// Approvals & Reports
$router->get( '/admin/approvals',             ['AdminController', 'approvals']);
$router->post('/admin/approvals/approve/{id}',['AdminController', 'approvalApprove']);
$router->post('/admin/approvals/reject/{id}', ['AdminController', 'approvalReject']);
$router->get( '/admin/reports',               ['AdminController', 'reports']);

// Database Sync
$router->get( '/admin/database-sync',         ['AdminController', 'databaseSync']);
$router->post('/admin/database-sync/run',     ['AdminController', 'databaseSyncRun']);
$router->post('/admin/database-sync/clear',   ['AdminController', 'databaseClear']);

// Import Retailers
$router->get( '/admin/retailers/import',      ['AdminController', 'retailersImport']);
$router->post('/admin/retailers/import',      ['AdminController', 'retailersImportPost']);

// ── Manager routes ────────────────────────────────────────────
$router->get( '/manager/dashboard',           ['ManagerController', 'dashboard']);

// Products (API based)
$router->get( '/manager/products',            ['ManagerController', 'products']);
$router->post('/manager/api/products',        ['ManagerController', 'apiProductStore']);
$router->post('/manager/api/products/update', ['ManagerController', 'apiProductUpdate']);
$router->post('/manager/api/products/delete', ['ManagerController', 'apiProductDelete']);
$router->post('/manager/api/stock/adjust',    ['ManagerController', 'apiStockAdjust']);

// Categories
$router->get( '/manager/categories',          ['ManagerController', 'categories']);
$router->post('/manager/api/categories',      ['ManagerController', 'apiCategoryStore']);
$router->post('/manager/api/categories/update',['ManagerController', 'apiCategoryUpdate']);
$router->post('/manager/api/categories/delete',['ManagerController', 'apiCategoryDelete']);
// Lots
$router->get( '/manager/lots',                ['ManagerController', 'lots']);
$router->post('/manager/api/lots/store',      ['ManagerController', 'apiLotStore']);
$router->post('/manager/api/lots/update',     ['ManagerController', 'apiLotUpdate']);
$router->post('/manager/api/lots/delete',     ['ManagerController', 'apiLotDelete']);

// Other manager pages
$router->get( '/manager/inventory',           ['ManagerController', 'inventory']);
$router->get( '/manager/dispatch',                           ['ManagerController', 'dispatch']);
$router->get( '/manager/api/dispatch/data',                  ['ManagerController', 'apiDispatchData']);
$router->get( '/manager/api/dispatch/new-popup-data',        ['ManagerController', 'apiDispatchNewPopupData']);
$router->post('/manager/api/dispatch/assign',                ['ManagerController', 'apiDispatchAssign']);
$router->get( '/manager/api/dispatch/sr-details/{id}',       ['ManagerController', 'apiDispatchSrDetails']);
$router->get( '/manager/api/dispatch/organize-data/{id}',    ['ManagerController', 'apiDispatchOrganizeData']);
$router->post('/manager/api/dispatch/organize-save/{id}',    ['ManagerController', 'apiDispatchOrganizeSave']);
$router->post('/manager/api/dispatch/status-update/{id}',    ['ManagerController', 'apiDispatchStatusUpdate']);
$router->post('/manager/api/dispatch/update-dsr',            ['ManagerController', 'apiDispatchUpdateDsr']);
$router->get( '/manager/settlements',               ['ManagerController', 'settlements']);
$router->post('/manager/api/settlements/update/{id}',['ManagerController', 'apiSettlementUpdate']);
$router->get( '/manager/attendance',          ['ManagerController', 'attendance']);
$router->post('/manager/attendance/store',    ['ManagerController', 'attendanceStore']);
$router->get( '/manager/readysale',           ['ManagerController', 'readysale']);
$router->post('/manager/readysale/store',     ['ManagerController', 'readysaleStore']);

// ── SR routes ─────────────────────────────────────────────────
$router->get( '/sr/dashboard',                ['SRController', 'dashboard']);
$router->get( '/sr/orders',                   ['SRController', 'orders']);
$router->get( '/sr/orders/place',             ['SRController', 'placeOrder']);
$router->post('/sr/orders/store',             ['SRController', 'storeOrder']);
$router->get( '/sr/sales',                    ['SRController', 'sales']);
$router->get( '/sr/retailers',                ['SRController', 'retailers']);
$router->get( '/sr/profile',                  ['SRController', 'profile']);
$router->get( '/sr/reports',                  ['SRController', 'reports']);
$router->get( '/sr/api/retailers',            ['SRController', 'apiRetailers']);
$router->post('/sr/api/retailers/store',      ['SRController', 'apiStoreRetailer']);
$router->get( '/sr/api/products',             ['SRController', 'apiProducts']);
$router->get( '/sr/api/today-order',          ['SRController', 'apiGetTodayOrder']);

// ── DSR routes ────────────────────────────────────────────────
$router->get( '/dsr/dashboard',               ['DSRController', 'dashboard']);
$router->get( '/dsr/scanner',                 ['DSRController', 'scanner']);
$router->post('/dsr/scanner/scan',            ['DSRController', 'scan']);
$router->get( '/dsr/van-stock',               ['DSRController', 'vanStock']);
$router->get( '/dsr/expenses',                ['DSRController', 'expenses']);
$router->post('/dsr/expenses/store',          ['DSRController', 'expenseStore']);
$router->get( '/dsr/delivery',                ['DSRController', 'delivery']);
$router->post('/dsr/delivery/update/{id}',    ['DSRController', 'deliveryUpdate']);
$router->get( '/dsr/collection',              ['DSRController', 'collection']);
$router->post('/dsr/collection/complete',     ['DSRController', 'collectionComplete']);
$router->get( '/dsr/settlement',              ['DSRController', 'settlement']);
$router->post('/dsr/settlement/submit',       ['DSRController', 'settlementSubmit']);
$router->get( '/dsr/profile',                 ['DSRController', 'profile']);
$router->post('/dsr/api/retailers/store',     ['DSRController', 'apiStoreRetailer']);
$router->post('/dsr/damage/store',            ['DSRController', 'damageStore']);
$router->get('/dsr/api/companies-products',    ['DSRController', 'apiCompanyProducts']);

// ── Dispatch ──────────────────────────────────────────────────
$router->dispatch($url, $method);
