<?php
/**
 * SRController — Sales Rep panel (Mobile App)
 */
class SRController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER, ROLE_SR]);
        $this->viewPath = MOD_PATH . '/SR/views';
        $this->db = Database::getInstance();
        $this->ensureRetailersTable();
    }

    // ── Ensure retailers table exists ─────────────────────────
    private function ensureRetailersTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS retailers (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                name        VARCHAR(255) NOT NULL,
                phone       VARCHAR(30),
                lat         DECIMAL(10,7),
                lng         DECIMAL(10,7),
                address     TEXT,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_lat_lng (lat, lng)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Also ensure orders table has retailer_id column
        try {
            $this->db->query("SELECT retailer_id FROM orders LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE orders ADD COLUMN retailer_id INT DEFAULT NULL AFTER dealer_id");
            } catch (PDOException $ex) {
                // Ignore if add column fails (e.g. column already exists or lock issue)
            }
        }
    }

    // ── Render with SR mobile layout ──────────────────────────
    private function renderApp(string $view, array $data = []): void
    {
        $this->render($view, $data, 'sr_app');
    }

    // ── Dashboard ─────────────────────────────────────────────
    public function dashboard(): void
    {
        $srId = Auth::id();

        $q = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE sr_id=?"); $q->execute([$srId]);
        $totalOrders = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE sr_id=? AND status='pending'"); $q->execute([$srId]);
        $pendingOrders = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE sr_id=? AND status='confirmed'"); $q->execute([$srId]);
        $confirmed = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE sr_id=?"); $q->execute([$srId]);
        $totalValue = $q->fetchColumn();

        // Today's sales
        $q = $this->db->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE sr_id=? AND DATE(created_at) = CURDATE()"); $q->execute([$srId]);
        $todaySales = $q->fetchColumn();

        // Total retailers
        $q = $this->db->query("SELECT COUNT(*) FROM retailers");
        $totalRetailers = $q->fetchColumn();

        // Visited today (unique retailers ordered from today or orders today)
        $q = $this->db->prepare("SELECT COUNT(DISTINCT NULLIF(retailer_id, 0)) FROM orders WHERE sr_id=? AND DATE(created_at) = CURDATE()"); $q->execute([$srId]);
        $visitedToday = $q->fetchColumn();
        if ($visitedToday == 0) {
            $q = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE sr_id=? AND DATE(created_at) = CURDATE()"); $q->execute([$srId]);
            $visitedToday = $q->fetchColumn();
        }

        $stats = [
            'total_orders'    => $totalOrders,
            'pending_orders'  => $pendingOrders,
            'confirmed'       => $confirmed,
            'total_value'     => $totalValue,
            'today_sales'     => $todaySales,
            'total_retailers' => $totalRetailers,
            'visited_today'   => $visitedToday,
        ];

        $q = $this->db->prepare("
            SELECT o.*, d.name AS dealer_name
            FROM orders o LEFT JOIN dealers d ON d.id=o.dealer_id
            WHERE o.sr_id=? ORDER BY o.created_at DESC LIMIT 8
        ");
        $q->execute([$srId]);
        $recentOrders = $q->fetchAll();

        // Fetch order values for the last 7 days dynamically
        $q = $this->db->prepare("
            SELECT DATE(created_at) as order_date, COALESCE(SUM(total_amount), 0) as total_val
            FROM orders
            WHERE sr_id=? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        $q->execute([$srId]);
        $rawChartData = $q->fetchAll();

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('D', strtotime("-$i days"));
            
            $val = 0;
            foreach ($rawChartData as $row) {
                if ($row['order_date'] === $date) {
                    $val = (float)$row['total_val'];
                    break;
                }
            }
            $chartValues[] = $val;
        }

        $this->renderApp('dashboard', compact('stats', 'recentOrders', 'chartLabels', 'chartValues'));
    }

    // ── Orders ────────────────────────────────────────────────
    public function orders(): void
    {
        $srId = Auth::id();
        $q = $this->db->prepare("
            SELECT o.*, d.name AS dealer_name, d.happy_commission, w.name AS warehouse_name
            FROM orders o
            LEFT JOIN dealers d ON d.id=o.dealer_id
            LEFT JOIN warehouses w ON w.id=o.warehouse_id
            WHERE o.sr_id=?
            ORDER BY o.created_at DESC
        ");
        $q->execute([$srId]);
        $items = $q->fetchAll();

        $retailersSet = [];
        $productSummary = [];

        foreach ($items as &$item) {
            if ($item['retailer_id']) {
                $retailersSet['retailer_'.$item['retailer_id']] = true;
            } elseif ($item['dealer_id']) {
                $retailersSet['dealer_'.$item['dealer_id']] = true;
            }

            $iq = $this->db->prepare("
                SELECT oi.*, p.name AS product_name, p.pieces_per_box, p.price AS base_price
                FROM order_items oi
                JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = ?
            ");
            $iq->execute([$item['id']]);
            $item['products'] = $iq->fetchAll();

            foreach ($item['products'] as $p) {
                $pid = $p['product_id'];
                if (!isset($productSummary[$pid])) {
                    $productSummary[$pid] = [
                        'name' => $p['product_name'],
                        'qty' => 0,
                        'total_val' => 0,
                        'total_oc' => 0,
                        'ppb' => (int)$p['pieces_per_box'] ?: 1
                    ];
                }
                $qty = (int)$p['quantity'];
                $base_price = (float)($p['base_price'] ?? 0);
                $unit_price = (float)($p['unit_price'] ?? 0);
                $item_oc = ($unit_price - $base_price) * $qty;

                $productSummary[$pid]['qty'] += $qty;
                $productSummary[$pid]['total_val'] += (float)$p['total_price'];
                $productSummary[$pid]['total_oc'] += $item_oc;
            }
        }

        $retailerCount = count($retailersSet);
        $productSummary = array_values($productSummary); // Reset keys for easier loop in view

        $this->renderApp('orders', compact('items', 'retailerCount', 'productSummary'));
    }

    // ── Sales / Map Page ──────────────────────────────────────
    public function sales(): void
    {
        $wid = Auth::warehouseId();
        $srId = Auth::id();

        // Load all products this SR can sell, summing stock from the warehouses of their assigned dealers
        $q = $this->db->prepare("
            SELECT p.*, c.name AS company_name, p.pieces_per_box AS pieces_per_carton,
                   COALESCE(SUM(i.qty_boxes * p.pieces_per_box + i.qty_pieces), 0) AS stock
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
            LEFT JOIN inventory i ON i.product_id = p.id
              AND EXISTS (
                  SELECT 1 FROM dealers d 
                  JOIN dealer_companies dc ON dc.dealer_id = d.id 
                  WHERE dc.sr_id = ? AND d.warehouse_id = i.warehouse_id
              )
            WHERE p.status=1
              AND p.company_id IN (
                  SELECT DISTINCT company_id FROM dealer_companies WHERE sr_id = ?
              )
            GROUP BY p.id
            ORDER BY p.name
        ");
        $q->execute([$srId, $srId]);
        $allProducts = $q->fetchAll(PDO::FETCH_ASSOC);

        $this->renderApp('sales', compact('allProducts'));
    }

    // ── Retailers List & Filtering ────────────────────────────
    public function retailers(): void
    {
        $search = trim($_GET['search'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $params = [];
        $where = " WHERE 1=1 ";
        if ($search !== '') {
            $where .= " AND (r.name LIKE ? OR r.phone LIKE ? OR r.address LIKE ?) ";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Get total count
        $countQuery = $this->db->prepare("SELECT COUNT(*) FROM retailers r $where");
        $countQuery->execute($params);
        $totalRetailers = (int)$countQuery->fetchColumn();

        // Get paginated items with order count today
        $srId = Auth::id();
        $q = $this->db->prepare("
            SELECT r.*,
                   (SELECT COUNT(*) FROM orders o WHERE o.retailer_id = r.id AND o.sr_id = ? AND DATE(o.created_at) = CURDATE()) as has_order_today
            FROM retailers r
            $where
            ORDER BY r.name ASC
            LIMIT $limit OFFSET $offset
        ");
        
        $selectParams = array_merge([$srId], $params);
        $q->execute($selectParams);
        $retailers = $q->fetchAll();

        $totalPages = ceil($totalRetailers / $limit);

        // Load all products this SR can sell, summing stock from the warehouses of their assigned dealers
        $pq = $this->db->prepare("
            SELECT p.*, c.name AS company_name, p.pieces_per_box AS pieces_per_carton,
                   COALESCE(SUM(i.qty_boxes * p.pieces_per_box + i.qty_pieces), 0) AS stock
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
            LEFT JOIN inventory i ON i.product_id = p.id
              AND EXISTS (
                  SELECT 1 FROM dealers d 
                  JOIN dealer_companies dc ON dc.dealer_id = d.id 
                  WHERE dc.sr_id = ? AND d.warehouse_id = i.warehouse_id
              )
            WHERE p.status=1
              AND p.company_id IN (
                  SELECT DISTINCT company_id FROM dealer_companies WHERE sr_id = ?
              )
            GROUP BY p.id
            ORDER BY p.name
        ");
        $pq->execute([$srId, $srId]);
        $allProducts = $pq->fetchAll(PDO::FETCH_ASSOC);

        $this->renderApp('retailers', compact('retailers', 'search', 'page', 'totalPages', 'totalRetailers', 'allProducts'));
    }

    // ── Profile ───────────────────────────────────────────────
    public function profile(): void
    {
        $this->renderApp('profile');
    }


    // ── API: Get retailers near location ──────────────────────
    public function apiRetailers(): void
    {
        $lat    = floatval($_GET['lat'] ?? 0);
        $lng    = floatval($_GET['lng'] ?? 0);
        $radius = floatval($_GET['radius'] ?? 100); // meters

        if ($lat === 0.0 || $lng === 0.0) {
            $lat = 23.8103;
            $lng = 90.4125;
        }

        $q = $this->db->prepare("
            SELECT r.id, r.name, r.phone, r.lat, r.lng,
                   (SELECT COUNT(*) FROM orders o WHERE o.retailer_id = r.id AND o.sr_id = ? AND DATE(o.created_at) = CURDATE()) as has_order_today,
                   ROUND(
                     6371000 * 2 * ASIN(SQRT(
                       POWER(SIN(RADIANS(r.lat - ?) / 2), 2) +
                       COS(RADIANS(?)) * COS(RADIANS(r.lat)) *
                       POWER(SIN(RADIANS(r.lng - ?) / 2), 2)
                     ))
                   ) AS dist_m
            FROM retailers r
            ORDER BY dist_m ASC
        ");
        $q->execute([
            Auth::id(),
            $lat, $lat, $lng
        ]);
        $retailers = $q->fetchAll(PDO::FETCH_ASSOC);

        // Rename dist_m → dist for JS
        foreach ($retailers as &$r) {
            $r['dist'] = $r['dist_m'];
            $r['has_order_today'] = intval($r['has_order_today']) > 0;
            unset($r['dist_m']);
        }

        $this->json(['success' => true, 'retailers' => $retailers]);
    }

    // ── API: Get today's order details for a retailer ─────────
    public function apiGetTodayOrder(): void
    {
        $retailerId = intval($_GET['retailer_id'] ?? 0);
        $srId = Auth::id();

        // Fetch today's order for this retailer by this SR
        $q = $this->db->prepare("
            SELECT id, notes, dealer_id
            FROM orders 
            WHERE retailer_id = ? AND sr_id = ? AND DATE(created_at) = CURDATE()
            ORDER BY id DESC LIMIT 1
        ");
        $q->execute([$retailerId, $srId]);
        $order = $q->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'No order found today.']);
            return;
        }

        // Fetch items
        $qItems = $this->db->prepare("
            SELECT oi.product_id, oi.quantity, oi.unit_price, oi.total_price,
                   p.name, p.pieces_per_box AS pieces_per_carton
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $qItems->execute([$order['id']]);
        $items = $qItems->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'success' => true,
            'order' => $order,
            'items' => array_map(function($item) {
                return [
                    'id' => intval($item['product_id']),
                    'name' => $item['name'],
                    'qty' => intval($item['quantity']),
                    'price' => floatval($item['unit_price']),
                    'total' => floatval($item['total_price']),
                    'pcsPerCarton' => intval($item['pieces_per_carton'] ?: 12)
                ];
            }, $items)
        ]);
    }

    // ── API: Store new retailer ────────────────────────────────
    public function apiStoreRetailer(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name  = trim($input['name']  ?? '');
        $phone = trim($input['phone'] ?? '');
        $lat   = floatval($input['lat'] ?? 0);
        $lng   = floatval($input['lng'] ?? 0);

        if (empty($name)) {
            $this->json(['success' => false, 'message' => 'Name is required.']);
            return;
        }

        $q = $this->db->prepare("
            INSERT INTO retailers (name, phone, lat, lng)
            VALUES (?, ?, ?, ?)
        ");
        $q->execute([$name, $phone, $lat ?: null, $lng ?: null]);
        $id = $this->db->lastInsertId();

        $this->json(['success' => true, 'id' => $id]);
    }

    // ── API: Products for retailer ────────────────────────────
    public function apiProducts(): void
    {
        $q = $this->db->prepare("
            SELECT p.*, c.name AS company_name, cat.name AS category_name
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
            LEFT JOIN categories cat ON cat.id=p.category_id
            WHERE p.status=1
              AND p.company_id IN (SELECT company_id FROM dealer_companies WHERE sr_id = ?)
            ORDER BY p.name
        ");
        $q->execute([Auth::id()]);
        $products = $q->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['success' => true, 'products' => $products]);
    }

    // ── Place Order (keep existing) ───────────────────────────
    public function placeOrder(): void
    {
        $compStmt = $this->db->prepare("
            SELECT DISTINCT c.*
            FROM companies c
            JOIN dealer_companies dc ON dc.company_id = c.id
            WHERE dc.sr_id = ? AND c.status = 1
            ORDER BY c.name
        ");
        $compStmt->execute([Auth::id()]);
        $companies = $compStmt->fetchAll();

        $dealersStmt = $this->db->prepare("
            SELECT DISTINCT d.*
            FROM dealers d
            JOIN dealer_companies dc ON dc.dealer_id = d.id
            WHERE d.status=1 AND dc.sr_id = ?
            ORDER BY d.name
        ");
        $dealersStmt->execute([Auth::id()]);
        $dealers = $dealersStmt->fetchAll();

        $prodStmt = $this->db->prepare("
            SELECT p.*, c.name AS company_name, cat.name AS category_name
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
            LEFT JOIN categories cat ON cat.id=p.category_id
            WHERE p.status=1
              AND p.company_id IN (SELECT company_id FROM dealer_companies WHERE sr_id = ?)
            ORDER BY p.name
        ");
        $prodStmt->execute([Auth::id()]);
        $products = $prodStmt->fetchAll();

        $dealerCompaniesStmt = $this->db->prepare("SELECT dealer_id, company_id FROM dealer_companies WHERE sr_id = ?");
        $dealerCompaniesStmt->execute([Auth::id()]);
        $dcRaw = $dealerCompaniesStmt->fetchAll();

        $companyDealerMap = [];
        foreach ($dcRaw as $row) {
            $companyDealerMap[$row['company_id']][] = $row['dealer_id'];
        }

        $this->renderApp('place_order', compact('companies', 'dealers', 'products', 'companyDealerMap'));
    }

    // ── Store Order ───────────────────────────────────────────
    public function storeOrder(): void
    {
        $dealerId = $this->post('dealer_id') ?: null;
        $retailerId = $this->post('retailer_id') ?: null;

        $warehouseId = null;
        if ($dealerId) {
            $stmt = $this->db->prepare("SELECT warehouse_id FROM dealers WHERE id=?");
            $stmt->execute([$dealerId]);
            $warehouseId = $stmt->fetchColumn();
        } else {
            $warehouseId = Auth::warehouseId();
        }

        if (empty($warehouseId)) {
            $warehouseId = $this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: null;
        }

        $notes      = trim($this->post('notes', ''));
        $productIds = $this->post('product_id', []);
        $quantities = $this->post('quantity', []);
        $prices     = $this->post('unit_price', []);

        if (empty($warehouseId) || empty($productIds)) {
            if ($this->post('ajax')) {
                $this->json(['success' => false, 'message' => 'Warehouse and at least one product required.']);
            }
            $this->flash('error', 'Warehouse and at least one product required.');
            $this->redirect('sr/orders'); return;
        }

        $validCompanyIdsStmt = $this->db->prepare("SELECT DISTINCT company_id FROM dealer_companies WHERE sr_id = ?");
        $validCompanyIdsStmt->execute([Auth::id()]);
        $validCompanies = $validCompanyIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $checkProds = $this->db->prepare("SELECT id, company_id FROM products WHERE id IN ($placeholders)");
        $checkProds->execute($productIds);
        $prodData = $checkProds->fetchAll(PDO::FETCH_ASSOC);

        foreach ($prodData as $pd) {
            if (!in_array($pd['company_id'], $validCompanies)) {
                if ($this->post('ajax')) {
                    $this->json(['success' => false, 'message' => 'Unauthorized product selected for this SR.']);
                }
                $this->flash('error', 'Unauthorized product selected for this SR.');
                $this->redirect('sr/orders'); return;
            }
        }

        // Clean up today's previous order if it exists for this retailer
        if ($retailerId) {
            $stmtToday = $this->db->prepare("SELECT id FROM orders WHERE retailer_id=? AND sr_id=? AND DATE(created_at)=CURDATE() LIMIT 1");
            $stmtToday->execute([$retailerId, Auth::id()]);
            $oldOrderId = $stmtToday->fetchColumn();
            if ($oldOrderId) {
                $this->db->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$oldOrderId]);
                $this->db->prepare("DELETE FROM orders WHERE id=?")->execute([$oldOrderId]);
            }
        }

        $total = 0;
        foreach ($productIds as $k => $pid) {
            $total += ($quantities[$k] ?? 0) * ($prices[$k] ?? 0);
        }

        $stmt = $this->db->prepare("INSERT INTO orders (sr_id,dealer_id,retailer_id,warehouse_id,total_amount,notes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([Auth::id(), $dealerId, $retailerId, $warehouseId, $total, $notes]);
        $orderId = $this->db->lastInsertId();

        foreach ($productIds as $k => $pid) {
            $qty   = intval($quantities[$k] ?? 0);
            $price = floatval($prices[$k] ?? 0);
            if ($qty <= 0) continue;
            $this->db->prepare("INSERT INTO order_items (order_id,product_id,quantity,unit_price,total_price) VALUES (?,?,?,?,?)")
                     ->execute([$orderId, $pid, $qty, $price, $qty * $price]);
        }

        $this->flash('success', "Order #$orderId placed successfully!");
        if ($this->post('ajax')) {
            $this->json(['success' => true, 'message' => "Order #$orderId placed successfully!", 'order_id' => $orderId]);
        }
        $this->redirect('sr/orders');
    }

    public function reports(): void
    {
        $srId = Auth::id();

        // ── Date filter ───────────────────────────────────────────────
        $period     = $_GET['period'] ?? 'month';   // today | week | month | custom
        $customFrom = $_GET['from']   ?? date('Y-m-01');
        $customTo   = $_GET['to']     ?? date('Y-m-d');

        switch ($period) {
            case 'today':
                $dateFrom = date('Y-m-d');
                $dateTo   = date('Y-m-d');
                break;
            case 'week':
                $dateFrom = date('Y-m-d', strtotime('monday this week'));
                $dateTo   = date('Y-m-d');
                break;
            case 'custom':
                $dateFrom = $customFrom;
                $dateTo   = $customTo;
                break;
            default: // month
                $dateFrom = date('Y-m-01');
                $dateTo   = date('Y-m-d');
        }

        // ── Summary stats ─────────────────────────────────────────────
        $statsQ = $this->db->prepare("
            SELECT
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_value,
                COALESCE(SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END), 0) as pending,
                COALESCE(SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END), 0) as confirmed,
                COALESCE(SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END), 0) as delivered,
                COALESCE(SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END), 0) as cancelled,
                COUNT(DISTINCT COALESCE(retailer_id, dealer_id)) as unique_customers
            FROM orders
            WHERE sr_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ");
        $statsQ->execute([$srId, $dateFrom, $dateTo]);
        $stats = $statsQ->fetch();

        // ── Daily chart data (last 30 days always, for the trend chart) ─
        $chartQ = $this->db->prepare("
            SELECT DATE(created_at) as d, COUNT(*) as orders, COALESCE(SUM(total_amount),0) as value
            FROM orders
            WHERE sr_id=? AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        $chartQ->execute([$srId, $dateFrom, $dateTo]);
        $rawChart = $chartQ->fetchAll();

        $chartLabels = [];
        $chartOrders = [];
        $chartValues = [];
        $start = strtotime($dateFrom);
        $end   = strtotime($dateTo);
        $chartMap = [];
        foreach ($rawChart as $row) { $chartMap[$row['d']] = $row; }
        for ($d = $start; $d <= $end; $d += 86400) {
            $key = date('Y-m-d', $d);
            $chartLabels[] = date('d M', $d);
            $chartOrders[] = (int)($chartMap[$key]['orders'] ?? 0);
            $chartValues[] = (float)($chartMap[$key]['value']  ?? 0);
        }

        // ── Top retailers by order value ───────────────────────────────
        $topRetailersQ = $this->db->prepare("
            SELECT
                COALESCE(r.name, dl.name, 'Unknown') as customer_name,
                COUNT(o.id) as order_count,
                COALESCE(SUM(o.total_amount), 0) as total_value
            FROM orders o
            LEFT JOIN retailers r  ON r.id  = o.retailer_id
            LEFT JOIN dealers   dl ON dl.id = o.dealer_id
            WHERE o.sr_id = ? AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY o.retailer_id, o.dealer_id
            ORDER BY total_value DESC
            LIMIT 8
        ");
        $topRetailersQ->execute([$srId, $dateFrom, $dateTo]);
        $topRetailers = $topRetailersQ->fetchAll();

        // ── Top products by quantity ───────────────────────────────────
        $topProductsQ = $this->db->prepare("
            SELECT
                p.name, p.image,
                SUM(oi.quantity) as total_qty,
                SUM(oi.total_price) as total_value
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            WHERE o.sr_id = ? AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY oi.product_id
            ORDER BY total_qty DESC
            LIMIT 8
        ");
        $topProductsQ->execute([$srId, $dateFrom, $dateTo]);
        $topProducts = $topProductsQ->fetchAll();

        // ── Recent orders ─────────────────────────────────────────────
        $recentQ = $this->db->prepare("
            SELECT o.id, o.total_amount, o.status, o.created_at,
                   COALESCE(r.name, dl.name, 'Unknown') as customer_name
            FROM orders o
            LEFT JOIN retailers r  ON r.id  = o.retailer_id
            LEFT JOIN dealers   dl ON dl.id = o.dealer_id
            WHERE o.sr_id = ? AND DATE(o.created_at) BETWEEN ? AND ?
            ORDER BY o.created_at DESC
            LIMIT 15
        ");
        $recentQ->execute([$srId, $dateFrom, $dateTo]);
        $recentOrders = $recentQ->fetchAll();

        // ── Status breakdown for donut ────────────────────────────────
        $donutData = [
            'pending'   => (int)$stats['pending'],
            'confirmed' => (int)$stats['confirmed'],
            'delivered' => (int)$stats['delivered'],
            'cancelled' => (int)$stats['cancelled'],
        ];

        $this->renderApp('reports', compact(
            'stats', 'chartLabels', 'chartOrders', 'chartValues',
            'topRetailers', 'topProducts', 'recentOrders',
            'donutData', 'period', 'dateFrom', 'dateTo', 'customFrom', 'customTo'
        ));
    }
}
