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
                sr_id       INT NOT NULL,
                name        VARCHAR(255) NOT NULL,
                phone       VARCHAR(30),
                lat         DECIMAL(10,7),
                lng         DECIMAL(10,7),
                address     TEXT,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sr_id (sr_id),
                INDEX idx_lat_lng (lat, lng)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
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

        $stats = [
            'total_orders'   => $totalOrders,
            'pending_orders' => $pendingOrders,
            'confirmed'      => $confirmed,
            'total_value'    => $totalValue,
        ];

        $q = $this->db->prepare("
            SELECT o.*, d.name AS dealer_name
            FROM orders o LEFT JOIN dealers d ON d.id=o.dealer_id
            WHERE o.sr_id=? ORDER BY o.created_at DESC LIMIT 8
        ");
        $q->execute([$srId]);
        $recentOrders = $q->fetchAll();

        $this->renderApp('dashboard', compact('stats', 'recentOrders'));
    }

    // ── Orders ────────────────────────────────────────────────
    public function orders(): void
    {
        $srId = Auth::id();
        $q = $this->db->prepare("
            SELECT o.*, d.name AS dealer_name, w.name AS warehouse_name
            FROM orders o
            LEFT JOIN dealers d ON d.id=o.dealer_id
            LEFT JOIN warehouses w ON w.id=o.warehouse_id
            WHERE o.sr_id=?
            ORDER BY o.created_at DESC
        ");
        $q->execute([$srId]);
        $items = $q->fetchAll();
        $this->renderApp('orders', compact('items'));
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

    // ── Retailers (Coming Soon) ───────────────────────────────
    public function retailers(): void
    {
        $this->renderApp('retailers');
    }

    // ── Profile ───────────────────────────────────────────────
    public function profile(): void
    {
        $this->renderApp('profile');
    }

    // ── Reports ───────────────────────────────────────────────
    public function reports(): void
    {
        $this->renderApp('reports');
    }

    // ── API: Get retailers near location ──────────────────────
    public function apiRetailers(): void
    {
        $lat    = floatval($_GET['lat'] ?? 0);
        $lng    = floatval($_GET['lng'] ?? 0);
        $radius = floatval($_GET['radius'] ?? 100); // meters

        if ($lat === 0.0 || $lng === 0.0) {
            $this->json(['success' => false, 'retailers' => []]);
            return;
        }

        // Haversine formula approximation using bounding box + filter
        // 1 degree lat ≈ 111km, 1 degree lng ≈ 111km * cos(lat)
        $latDelta = $radius / 111000;
        $lngDelta = $radius / (111000 * cos(deg2rad($lat)));

        $q = $this->db->prepare("
            SELECT id, name, phone, lat, lng,
                   ROUND(
                     6371000 * 2 * ASIN(SQRT(
                       POWER(SIN(RADIANS(lat - ?) / 2), 2) +
                       COS(RADIANS(?)) * COS(RADIANS(lat)) *
                       POWER(SIN(RADIANS(lng - ?) / 2), 2)
                     ))
                   ) AS dist_m
            FROM retailers
            WHERE lat BETWEEN ? AND ?
              AND lng BETWEEN ? AND ?
            HAVING dist_m <= ?
            ORDER BY dist_m ASC
        ");
        $q->execute([
            $lat, $lat, $lng,
            $lat - $latDelta, $lat + $latDelta,
            $lng - $lngDelta, $lng + $lngDelta,
            $radius
        ]);
        $retailers = $q->fetchAll(PDO::FETCH_ASSOC);

        // Rename dist_m → dist for JS
        foreach ($retailers as &$r) {
            $r['dist'] = $r['dist_m'];
            unset($r['dist_m']);
        }

        $this->json(['success' => true, 'retailers' => $retailers]);
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
            INSERT INTO retailers (sr_id, name, phone, lat, lng)
            VALUES (?, ?, ?, ?, ?)
        ");
        $q->execute([Auth::id(), $name, $phone, $lat ?: null, $lng ?: null]);
        $id = $this->db->lastInsertId();

        $this->json(['success' => true, 'id' => $id]);
    }

    // ── API: Products for retailer ────────────────────────────
    public function apiProducts(): void
    {
        $q = $this->db->prepare("
            SELECT p.*, c.name AS company_name
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
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
            SELECT p.*, c.name AS company_name
            FROM products p
            LEFT JOIN companies c ON c.id=p.company_id
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
                $this->flash('error', 'Unauthorized product selected for this SR.');
                $this->redirect('sr/orders'); return;
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
        $this->redirect('sr/orders');
    }
}
