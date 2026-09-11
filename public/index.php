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
        MOD_PATH . '/Dealer/'  . $class . '.php',
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

// ── Catch & Clean URL ─────────────────────────────────────────
// CloudPanel uses a two-level Nginx proxy (443 → 8080 → PHP-FPM).
// Nginx try_files can corrupt REQUEST_URI (rewrites /login → /index.php?).
// We pass the true original URI via the X-Request-URI proxy header instead.
// Fallback: REQUEST_URI is used if the header is not present.
$trueUri = $_SERVER['HTTP_X_REQUEST_URI']  // set by port 443 proxy_set_header
        ?? $_SERVER['REQUEST_URI']          // standard fallback
        ?? '/';
$urlPath = parse_url($trueUri, PHP_URL_PATH) ?? '/';

// Strip base path to allow subfolder hosting (e.g. localhost)
$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if (!empty($basePath) && $basePath !== '/' && strpos($urlPath, $basePath) === 0) {
    $urlPath = substr($urlPath, strlen($basePath));
}

$url = '/' . ltrim($urlPath, '/');
if (strpos($url, '/public/') === 0) {
    $url = substr($url, 7); // leaves the trailing slash
}
if ($url === '/public') {
    $url = '/';
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

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
$router->get( '/dealer/login',  ['DealerController', 'login']);
$router->post('/dealer/login',  ['DealerController', 'loginSubmit']);
$router->get( '/admin/logout',   ['AuthController', 'logout']);
$router->get( '/manager/logout', ['AuthController', 'logout']);
$router->get( '/sr/logout',      ['AuthController', 'logout']);
$router->get( '/dsr/logout',     ['AuthController', 'logout']);
$router->get( '/dealer/logout',  ['DealerController', 'logout']);

$router->get( '/forgot',   ['AuthController', 'showForgot']);
$router->post('/forgot',   ['AuthController', 'forgot']);
$router->get( '/',         fn() => header('Location: ' . BASE_URL . '/login') ?: exit());

// ── Admin routes ──────────────────────────────────────────────
$router->get( '/admin/dashboard',          ['AdminController', 'dashboard']);
$router->get( '/admin/ai-assistant',       ['AdminController', 'aiAssistant']);
$router->post('/admin/ai-assistant/api',   ['AdminController', 'aiAssistantApi']);
$router->post('/admin/ai-assistant/translate', ['AdminController', 'aiAssistantTranslate']);

// Warehouses
$router->get( '/admin/warehouses',         ['SystemAdminController', 'warehouses']);
$router->get( '/admin/warehouses/create',  ['SystemAdminController', 'warehouseCreate']);
$router->post('/admin/warehouses/store',   ['SystemAdminController', 'warehouseStore']);
$router->get( '/admin/warehouses/edit/{id}',   ['SystemAdminController', 'warehouseEdit']);
$router->post('/admin/warehouses/update/{id}', ['SystemAdminController', 'warehouseUpdate']);
$router->post('/admin/warehouses/delete/{id}', ['SystemAdminController', 'warehouseDelete']);

// Managers
$router->get( '/admin/managers',           ['UserAdminController', 'managers']);
$router->get( '/admin/managers/create',    ['UserAdminController', 'managerCreate']);
$router->post('/admin/managers/store',     ['UserAdminController', 'managerStore']);
$router->get( '/admin/managers/edit/{id}', ['UserAdminController', 'managerEdit']);
$router->post('/admin/managers/update/{id}', ['UserAdminController', 'managerUpdate']);
$router->post('/admin/managers/delete/{id}', ['UserAdminController', 'managerDelete']);

// SRs
$router->get( '/admin/srs',               ['UserAdminController', 'srs']);
$router->get( '/admin/srs/create',        ['UserAdminController', 'srCreate']);
$router->post('/admin/srs/store',         ['UserAdminController', 'srStore']);
$router->get( '/admin/srs/edit/{id}',     ['UserAdminController', 'srEdit']);
$router->post('/admin/srs/update/{id}',   ['UserAdminController', 'srUpdate']);
$router->post('/admin/srs/delete/{id}',   ['UserAdminController', 'srDelete']);
$router->get( '/admin/api/sr-orders-cutoff',        ['UserAdminController', 'apiSrOrdersCutoff']);
$router->post('/admin/api/sr-orders-cutoff/toggle', ['UserAdminController', 'apiToggleSrOrderCutoff']);
$router->post('/admin/api/sr-price-correction/toggle', ['UserAdminController', 'apiToggleSrPriceCorrection']);

// DSRs
$router->get( '/admin/dsrs',              ['UserAdminController', 'dsrs']);
$router->get( '/admin/dsrs/create',       ['UserAdminController', 'dsrCreate']);
$router->post('/admin/dsrs/store',        ['UserAdminController', 'dsrStore']);
$router->get( '/admin/dsrs/edit/{id}',    ['UserAdminController', 'dsrEdit']);
$router->post('/admin/dsrs/update/{id}',  ['UserAdminController', 'dsrUpdate']);
$router->post('/admin/dsrs/delete/{id}',  ['UserAdminController', 'dsrDelete']);

// Companies
$router->get( '/admin/companies',              ['SystemAdminController', 'companies']);
$router->get( '/admin/companies/create',       ['SystemAdminController', 'companyCreate']);
$router->post('/admin/companies/store',        ['SystemAdminController', 'companyStore']);
$router->get( '/admin/companies/edit/{id}',    ['SystemAdminController', 'companyEdit']);
$router->post('/admin/companies/update/{id}',  ['SystemAdminController', 'companyUpdate']);
$router->post('/admin/companies/delete/{id}',  ['SystemAdminController', 'companyDelete']);

// Dealers
$router->get( '/admin/dealers',               ['UserAdminController', 'dealers']);
$router->get( '/admin/dealers/create',        ['UserAdminController', 'dealerCreate']);
$router->post('/admin/dealers/store',         ['UserAdminController', 'dealerStore']);
$router->get( '/admin/dealers/edit/{id}',     ['UserAdminController', 'dealerEdit']);
$router->post('/admin/dealers/update/{id}',   ['UserAdminController', 'dealerUpdate']);
$router->post('/admin/dealers/delete/{id}',   ['UserAdminController', 'dealerDelete']);

// Approvals & Reports
$router->get( '/admin/approvals',             ['SystemAdminController', 'approvals']);
$router->post('/admin/approvals/approve/{id}',['SystemAdminController', 'approvalApprove']);
$router->post('/admin/approvals/reject/{id}', ['SystemAdminController', 'approvalReject']);
$router->get( '/admin/reports',               ['SystemAdminController', 'reports']);

// Database Sync
$router->get( '/admin/database-sync',         ['SystemAdminController', 'databaseSync']);
$router->post('/admin/database-sync/run',     ['SystemAdminController', 'databaseSyncRun']);
$router->post('/admin/database-sync/clear',          ['SystemAdminController', 'databaseClear']);
$router->post('/admin/database-sync/clear-dispatch', ['SystemAdminController', 'dispatchClear']);

// Retailers
$router->get( '/admin/retailers',             ['SystemAdminController', 'retailers']);
$router->get( '/admin/retailers/import',      ['SystemAdminController', 'retailersImport']);
$router->post('/admin/retailers/import',      ['SystemAdminController', 'retailersImportPost']);

// Orders
$router->get( '/admin/orders',                ['SystemAdminController', 'orders']);
$router->get( '/admin/api/orders',            ['SystemAdminController', 'apiOrders']);

// Sessions
$router->get( '/admin/sessions',              ['SystemAdminController', 'sessions']);
$router->post('/admin/sessions/logout/{id}',  ['SystemAdminController', 'sessionForceLogout']);

// SR Tracking
$router->get( '/admin/sr-tracking',                   ['TrackingController', 'srTracking']);
$router->get( '/admin/api/sr-tracking/live',          ['TrackingController', 'apiSrTrackingLive']);
$router->get( '/admin/api/sr-tracking/history',       ['TrackingController', 'apiSrTrackingHistory']);

// DSR Tracking
$router->get( '/admin/dsr-tracking',                  ['TrackingController', 'dsrTracking']);
$router->get( '/admin/api/dsr-tracking/live',         ['TrackingController', 'apiDsrTrackingLive']);
$router->get( '/admin/api/dsr-tracking/history',      ['TrackingController', 'apiDsrTrackingHistory']);

// Custom Areas Map Management
$router->get( '/admin/custom-areas',                  ['TrackingController', 'customAreas']);
$router->get( '/admin/api/custom-areas',              ['TrackingController', 'apiCustomAreas']);
$router->post('/admin/api/custom-areas/store',        ['TrackingController', 'apiCustomAreaStore']);
$router->post('/admin/api/custom-areas/update/{id}',  ['TrackingController', 'apiCustomAreaUpdate']);
$router->post('/admin/api/custom-areas/delete/{id}',  ['TrackingController', 'apiCustomAreaDelete']);

// Manager Logs
$router->get( '/admin/manager-logs',                  ['SystemAdminController', 'managerLogs']);


// ── Manager routes ────────────────────────────────────────────
$router->get( '/manager/dashboard',           ['ManagerController', 'dashboard']);

// Products (API based)
$router->get( '/manager/products',            ['ProductController', 'products']);
$router->post('/manager/api/products',        ['ProductController', 'apiProductStore']);
$router->post('/manager/api/products/update', ['ProductController', 'apiProductUpdate']);
$router->post('/manager/api/products/adjust-buying-price', ['ProductController', 'apiAdjustBuyingPrice']);
$router->get( '/manager/api/products/price-history',       ['ProductController', 'apiProductPriceHistory']);
$router->post('/manager/api/products/delete', ['ProductController', 'apiProductDelete']);
$router->post('/manager/api/stock/adjust',    ['ProductController', 'apiStockAdjust']);

// Categories
$router->get( '/manager/categories',          ['ProductController', 'categories']);
$router->post('/manager/api/categories',      ['ProductController', 'apiCategoryStore']);
$router->post('/manager/api/categories/update',['ProductController', 'apiCategoryUpdate']);
$router->post('/manager/api/categories/delete',['ProductController', 'apiCategoryDelete']);
// Lots
$router->get( '/manager/lots',                ['ProductController', 'lots']);
$router->post('/manager/api/lots/store',      ['ProductController', 'apiLotStore']);
$router->post('/manager/api/lots/update',     ['ProductController', 'apiLotUpdate']);
$router->post('/manager/api/lots/delete',     ['ProductController', 'apiLotDelete']);
$router->post('/manager/api/lots/delete-batch',['ProductController', 'apiLotBatchDelete']);
$router->post('/manager/api/lots/update-batch',['ProductController', 'apiLotBatchUpdate']);
$router->post('/manager/api/lots/request-edit', ['ProductController', 'apiLotBatchEditRequest']);

// Manager Orders
$router->get( '/manager/orders',                             ['ReportController', 'orders']);
$router->get( '/manager/api/orders/companies',               ['ReportController', 'apiOrdersCompanies']);
$router->get( '/manager/api/orders/srs',                     ['ReportController', 'apiOrdersSrs']);
$router->get( '/manager/api/orders/products',                ['ReportController', 'apiOrdersProducts']);

// Other manager pages
$router->get( '/manager/inventory',           ['InventoryController', 'inventory']);
$router->get( '/manager/dispatch',                           ['DispatchController', 'dispatch']);
$router->get( '/manager/api/dispatch/data',                  ['DispatchController', 'apiDispatchData']);
$router->get( '/manager/api/dispatch/new-popup-data',        ['DispatchController', 'apiDispatchNewPopupData']);
$router->post('/manager/api/dispatch/assign',                ['DispatchController', 'apiDispatchAssign']);
$router->get( '/manager/api/dispatch/sr-details/{id}',       ['DispatchController', 'apiDispatchSrDetails']);
$router->get( '/manager/api/dispatch/company-details/{id}',  ['DispatchController', 'apiDispatchCompanyDetails']);
$router->get( '/manager/api/dispatch/organize-data/{id}',    ['DispatchController', 'apiDispatchOrganizeData']);
$router->post('/manager/api/dispatch/organize-save/{id}',    ['DispatchController', 'apiDispatchOrganizeSave']);
$router->post('/manager/api/dispatch/status-update/{id}',    ['DispatchController', 'apiDispatchStatusUpdate']);
$router->post('/manager/api/dispatch/undo-dispatch/{id}',    ['DispatchController', 'apiDispatchUndoDispatch']);
$router->get('/manager/api/dispatch/van-stock/{dsrId}',      ['DispatchController', 'apiDispatchVanStock']);
$router->post('/manager/api/dispatch/return-save/{scheduleId}',   ['DispatchController', 'apiDispatchReturnSave']);
$router->post('/manager/api/dispatch/undo-return/{scheduleId}',   ['DispatchController', 'apiDispatchUndoReturn']);
$router->post('/manager/api/dispatch/update-product-qty/{scheduleId}', ['DispatchController', 'apiDispatchUpdateProductQty']);
$router->post('/manager/api/dispatch/update-dsr',            ['DispatchController', 'apiDispatchUpdateDsr']);
$router->post('/manager/api/dispatch/update-delivery-date',     ['DispatchController', 'apiDispatchUpdateDeliveryDate']);
$router->post('/manager/api/dispatch/delete/{id}',            ['DispatchController', 'apiDispatchDelete']);
$router->get( '/manager/settlements',               ['SettlementController', 'settlements']);
$router->post('/manager/api/settlements/update/{id}',['SettlementController', 'apiSettlementUpdate']);
$router->get( '/manager/attendance',                    ['AttendanceController', 'attendance']);
$router->post('/manager/attendance/store',              ['AttendanceController', 'attendanceStore']);
$router->get( '/manager/api/attendance/qr',             ['AttendanceController', 'apiAttendanceQrGet']);
$router->post('/manager/api/attendance/qr/generate',    ['AttendanceController', 'apiAttendanceQrGenerate']);
$router->get( '/manager/readysale',           ['ReportController', 'readysale']);
$router->post('/manager/readysale/store',     ['ReportController', 'readysaleStore']);

// Order Cutoff
$router->get( '/manager/api/sr-cutoff-status',              ['OperationsController', 'apiSrCutoffStatus']);
$router->post('/manager/api/order-cutoff/undo/{srId}',      ['OperationsController', 'apiUndoOrderCutoff']);

// Operations Panel
$router->get( '/manager/operations',                          ['OperationsController', 'operations']);
$router->get( '/manager/api/operations/orders',               ['OperationsController', 'apiOperationsOrders']);
$router->get( '/manager/api/operations/deliveries',           ['OperationsController', 'apiOperationsDeliveries']);
$router->get( '/manager/api/operations/dsr-deliveries',       ['OperationsController', 'apiOperationsDsrDeliveries']);
$router->post('/manager/api/operations/dsr-delivery-action',  ['OperationsController', 'apiOperationsDsrDeliveryAction']);
$router->post('/manager/api/operations/edit-order/{id}',      ['OperationsController', 'apiOperationsEditOrder']);
$router->post('/manager/api/operations/bulk-change-order-date', ['OperationsController', 'apiOperationsBulkChangeOrderDate']);
$router->post('/manager/api/operations/delete-order/{id}',    ['OperationsController', 'apiOperationsDeleteOrder']);
$router->post('/manager/api/operations/edit-delivery/{id}',   ['OperationsController', 'apiOperationsEditDelivery']);
$router->post('/manager/api/operations/place-order',          ['OperationsController', 'apiOperationsPlaceOrder']);
$router->post('/manager/api/operations/make-delivery',        ['OperationsController', 'apiOperationsMakeDelivery']);

// ── SR routes ─────────────────────────────────────────────────
$router->get( '/sr/dashboard',                ['SRController', 'dashboard']);
$router->get( '/sr/transactions',             ['SRController', 'transactions']);
$router->get( '/sr/orders',                   ['SRController', 'orders']);
$router->get( '/sr/orders/place',             ['SRController', 'placeOrder']);
$router->post('/sr/orders/store',             ['SRController', 'storeOrder']);
$router->post('/sr/orders/update',            ['SRController', 'updateOrder']);
$router->get( '/sr/sales',                    ['SRController', 'sales']);
$router->get( '/sr/retailers',                ['SRController', 'retailers']);
$router->get( '/sr/profile',                  ['SRController', 'profile']);
$router->get( '/sr/reports',                  ['SRController', 'reports']);
$router->get( '/sr/api/retailers',            ['SRController', 'apiRetailers']);
$router->get( '/sr/api/retailers/search',     ['SRController', 'apiSearchRetailers']);
$router->post('/sr/api/retailers/store',      ['SRController', 'apiStoreRetailer']);
$router->get( '/sr/api/products',             ['SRController', 'apiProducts']);
$router->get( '/sr/api/today-order',          ['SRController', 'apiGetTodayOrder']);
$router->post('/sr/api/location/push',        ['SRController', 'apiPushLocation']);
$router->post('/sr/api/order-cutoff',         ['SRController', 'apiSetOrderCutoff']);
$router->get( '/sr/price-correction',         ['SRController', 'priceCorrection']);
$router->post('/sr/api/price-correction/modify', ['SRController', 'apiPriceCorrectionModify']);
$router->post('/sr/api/log-visit',            ['SRController', 'apiLogVisit']);

// ── DSR routes ────────────────────────────────────────────────
$router->get( '/dsr/dashboard',               ['DSRController', 'dashboard']);
$router->get( '/dsr/scanner',                 ['DSRController', 'scanner']);
$router->post('/dsr/scanner/scan',            ['DSRController', 'scan']);
$router->get( '/dsr/van-stock',               ['DSRController', 'vanStock']);
$router->get( '/dsr/expenses',                ['DSRController', 'expenses']);
$router->post('/dsr/expenses/store',          ['DSRController', 'expenseStore']);
$router->get( '/dsr/delivery',                ['DSRController', 'delivery']);
$router->post('/dsr/delivery/update/{id}',    ['DSRController', 'deliveryUpdate']);
$router->post('/dsr/delivery/undo/{id}',      ['DSRController', 'deliveryUndo']);
$router->get( '/dsr/collection',              ['DSRController', 'collection']);
$router->post('/dsr/collection/complete',     ['DSRController', 'collectionComplete']);
$router->get( '/dsr/settlement',              ['DSRController', 'settlement']);
$router->post('/dsr/settlement/submit',       ['DSRController', 'settlementSubmit']);
$router->get( '/dsr/api/settlement/returns',  ['DSRController', 'apiSettlementReturns']);
$router->get( '/dsr/api/settlement/oc',       ['DSRController', 'apiSettlementOc']);
$router->get( '/dsr/profile',                 ['DSRController', 'profile']);
$router->post('/dsr/api/retailers/store',     ['DSRController', 'apiStoreRetailer']);
$router->post('/dsr/damage/store',            ['DSRController', 'damageStore']);
$router->get( '/dsr/api/companies-products',   ['DSRController', 'apiCompanyProducts']);
$router->post('/dsr/api/location/push',       ['DSRController', 'apiPushLocation']);
$router->get( '/dsr/api/van-stock',           ['DSRController', 'apiVanStock']);
$router->post('/dsr/ready-sale/store',         ['DSRController', 'readySaleStore']);
$router->get( '/dsr/qr-code',                  ['DSRController', 'qrCode']);
$router->post('/dsr/qr-code/mark',             ['DSRController', 'qrCodeMark']);



// ── Dealer routes ───────────────────────────────────────────────
$router->get( '/dealer/dashboard',    ['DealerController', 'dashboard']);
$router->get( '/dealer/transactions', ['DealerController', 'transactions']);
$router->get( '/dealer/transactions/bill', ['DealerController', 'billDetails']);
$router->get( '/dealer/inventory',    ['DealerController', 'inventory']);
$router->get( '/dealer/profit-report',['DealerController', 'profitReport']);

// Dealer Tracking (SR & DSR)
$router->get( '/dealer/sr-tracking',             ['DealerController', 'srTracking']);
$router->get( '/dealer/api/sr-tracking/live',    ['DealerController', 'apiSrTrackingLive']);
$router->get( '/dealer/api/sr-tracking/history', ['DealerController', 'apiSrTrackingHistory']);

$router->get( '/dealer/dsr-tracking',            ['DealerController', 'dsrTracking']);
$router->get( '/dealer/api/dsr-tracking/live',   ['DealerController', 'apiDsrTrackingLive']);
$router->get( '/dealer/api/dsr-tracking/history',['DealerController', 'apiDsrTrackingHistory']);

// ── Dispatch ──────────────────────────────────────────────────
$router->dispatch($url, $method);

