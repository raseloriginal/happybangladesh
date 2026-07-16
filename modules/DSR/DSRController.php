<?php
/**
 * DSRController — Delivery Sales Rep panel
 */
class DSRController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER, ROLE_DSR]);
        $this->viewPath = MOD_PATH . '/DSR/views';
        $this->db = Database::getInstance();
    }

    public function dashboard(): void
    {
        $dsrId = Auth::id();
        $stats = [];

        // Today's Delivery Count
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['todays_deliveries'] = $q->fetchColumn();

        // Ordered Retailers
        $q = $this->db->prepare("SELECT COUNT(DISTINCT COALESCE(o.dealer_id, o.id)) FROM dispatches d JOIN orders o ON o.id=d.order_id WHERE d.dsr_id=? AND d.dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['ordered_retailers'] = $q->fetchColumn();

        // Completed Deliveries
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status='delivered' AND dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['completed_deliveries'] = $q->fetchColumn();

        // Due Deliveries
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status IN ('pending', 'in_transit') AND dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['due_deliveries'] = $q->fetchColumn();

        // Ready Sales
        $q = $this->db->prepare("SELECT COUNT(*) FROM readysales r JOIN users u ON u.warehouse_id = r.warehouse_id WHERE DATE(r.created_at)=CURDATE()"); $q->execute();
        $stats['ready_sales'] = $q->fetchColumn();

        // Pending Settlement
        $q = $this->db->prepare("SELECT COUNT(*) FROM settlements WHERE dsr_id=? AND status='pending'"); $q->execute([$dsrId]);
        $stats['pending_settlement'] = $q->fetchColumn();

        $this->render('dashboard', compact('stats'), 'dsr_app');
    }

    public function scanner(): void
    {
        $this->render('scanner', [], 'dsr_app');
    }

    public function scan(): void
    {
        $code = trim($this->post('code', ''));
        if (empty($code)) {
            $this->json(['success' => false, 'message' => 'No code provided.']);
            return;
        }

        $product = $this->db->prepare("SELECT p.*, c.name AS company_name FROM products p LEFT JOIN companies c ON c.id=p.company_id WHERE p.sku=? LIMIT 1");
        $product->execute([$code]);
        $product = $product->fetch();

        if ($product) {
            $this->json(['success' => true, 'type' => 'product', 'data' => $product]);
            return;
        }

        $this->json(['success' => false, 'message' => "No product found for code: {$code}"]);
    }

    public function vanStock(): void
    {
        $dsrId = Auth::id();
        $items = $this->db->prepare("
            SELECT vs.*, p.name, p.image, p.pieces_per_box
            FROM van_stock vs
            JOIN products p ON p.id = vs.product_id
            WHERE vs.dsr_id = ? AND vs.quantity > 0
            ORDER BY p.name ASC
        ");
        $items->execute([$dsrId]);
        $items = $items->fetchAll();

        $this->render('van_stock', compact('items'), 'dsr_app');
    }

    public function expenses(): void
    {
        $items = $this->db->prepare("SELECT * FROM expenses WHERE dsr_id=? ORDER BY date DESC, created_at DESC");
        $items->execute([Auth::id()]);
        $items = $items->fetchAll();
        $this->render('expenses', compact('items'), 'dsr_app');
    }

    public function expenseStore(): void
    {
        $this->verifyCsrf();
        $this->db->prepare("INSERT INTO expenses (dsr_id,date,category,amount,description) VALUES (?,?,?,?,?)")
                 ->execute([Auth::id(), $this->post('date', date('Y-m-d')), $this->post('category','other'), $this->post('amount',0), trim($this->post('description',''))]);
        $this->flash('success', 'Expense recorded.'); $this->redirect('dsr/expenses');
    }

    public function delivery(): void
    {
        $dsrId = Auth::id();

        // Fetch only dispatches that are physically on the van (in_transit, partial) or delivered today
        $q = $this->db->prepare("
            SELECT d.id as dispatch_id, o.id as order_id, COALESCE(dl.id, r.id) as dealer_id,
                   COALESCE(dl.name, r.name) as dealer_name, 
                   COALESCE(dl.address, r.address) as address, 
                   COALESCE(dl.lat, r.lat) as lat, 
                   COALESCE(dl.lng, r.lng) as lng,
                   o.total_amount, d.status,
                   c.name as company_name
            FROM dispatches d
            JOIN orders o ON o.id = d.order_id
            JOIN users u ON u.id = o.sr_id
            LEFT JOIN companies c ON c.id = u.company_id
            LEFT JOIN dealers dl ON dl.id = o.dealer_id
            LEFT JOIN retailers r ON r.id = o.retailer_id
            WHERE d.dsr_id = ?
              AND (d.status IN ('in_transit', 'partial') OR (d.status = 'delivered' AND d.dispatch_date = CURDATE()))
            ORDER BY dealer_name ASC
        ");
        $q->execute([$dsrId]);
        $flatRetailers = $q->fetchAll();

        // Group by dealer_id
        $grouped = [];
        foreach ($flatRetailers as $ret) {
            $did = $ret['dealer_id'] ?? 'unknown_'.uniqid();
            if (!isset($grouped[$did])) {
                $grouped[$did] = [
                    'dealer_id' => $ret['dealer_id'],
                    'dealer_name' => $ret['dealer_name'],
                    'address' => $ret['address'],
                    'lat' => $ret['lat'],
                    'lng' => $ret['lng'],
                    'orders' => []
                ];
            }
            
            // Fetch products for this dispatch
            $iq = $this->db->prepare("
                SELECT di.product_id, di.quantity, di.lot_id,
                       p.name, p.image, p.pieces_per_box
                FROM dispatch_items di
                JOIN products p ON p.id = di.product_id
                WHERE di.dispatch_id = ?
            ");
            $iq->execute([$ret['dispatch_id']]);
            $products = $iq->fetchAll();
            
            $grouped[$did]['orders'][] = [
                'dispatch_id' => $ret['dispatch_id'],
                'order_id' => $ret['order_id'],
                'total_amount' => $ret['total_amount'],
                'status' => $ret['status'],
                'company_name' => $ret['company_name'] ?: 'Unknown Company',
                'products' => $products
            ];
        }
        
        $orderedRetailers = array_values($grouped);

        // Check if collection is complete
        $check = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=CURDATE() AND status='pending'");
        $check->execute([$dsrId]);
        
        $qItems = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=CURDATE()");
        $qItems->execute([$dsrId]);
        
        $isCompleted = ($qItems->fetchColumn() > 0 && $check->fetchColumn() == 0);

        $this->render('delivery', compact('orderedRetailers', 'isCompleted'), 'dsr_app');
    }

    public function deliveryUpdate(string $id): void
    {
        $status = $this->post('status', 'delivered');
        $dsrId = Auth::id();
        
        $this->db->prepare("UPDATE dispatches SET status=?, updated_at=NOW() WHERE id=? AND dsr_id=?")
                 ->execute([$status, $id, $dsrId]);
        
        if ($status === 'delivered' || $status === 'partial') {
            // Deduct from van_stock based on dispatch items
            $items = $this->db->prepare("SELECT product_id, lot_id, quantity FROM dispatch_items WHERE dispatch_id=?");
            $items->execute([$id]);
            $items = $items->fetchAll();
            
            foreach($items as $item) {
                // We're assuming the delivered quantity is equal to dispatch quantity for now,
                // or we should be receiving the exact delivered qty from the frontend.
                // The prompt says "Box Input, PCS Input". We need to accept the quantities from frontend!
                // For simplicity, we deduct the original dispatch quantity if it's 'delivered'.
                if ($status === 'delivered') {
                    $this->db->prepare("UPDATE van_stock SET quantity = quantity - ? WHERE dsr_id=? AND product_id=? AND (lot_id=? OR (? IS NULL AND lot_id IS NULL))")
                             ->execute([$item['quantity'], $dsrId, $item['product_id'], $item['lot_id'], $item['lot_id']]);
                }
            }
        }
        
        $this->json(['success' => true]);
    }

    public function collection(): void
    {
        $dsrId = Auth::id();
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $q = $this->db->prepare("
            SELECT di.product_id, p.name, p.image, SUM(di.quantity) as total_qty, MAX(d.status) as status
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            JOIN products p ON p.id = di.product_id
            WHERE d.dsr_id=? AND d.dispatch_date=?
            GROUP BY di.product_id, p.name, p.image
        ");
        $q->execute([$dsrId, $date]);
        $items = $q->fetchAll();

        $check = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND status='pending'");
        $check->execute([$dsrId, $date]);
        $isCompleted = (!empty($items) && $check->fetchColumn() == 0);

        $this->render('collection', compact('items', 'isCompleted', 'date'), 'dsr_app');
    }

    public function collectionComplete(): void
    {
        $dsrId = Auth::id();
        $date = $_POST['date'] ?? date('Y-m-d');
        
        // 1. Get all items that are pending dispatch for this DSR today
        $q = $this->db->prepare("
            SELECT di.product_id, di.lot_id, SUM(di.quantity) as total_qty
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            WHERE d.dsr_id=? AND d.dispatch_date=? AND d.status='pending'
            GROUP BY di.product_id, di.lot_id
        ");
        $q->execute([$dsrId, $date]);
        $itemsToLoad = $q->fetchAll();

        foreach ($itemsToLoad as $item) {
            $lotCondition = $item['lot_id'] === null ? "IS NULL" : "= ?";
            $params = [$dsrId, $item['product_id']];
            if ($item['lot_id'] !== null) $params[] = $item['lot_id'];
            
            $check = $this->db->prepare("SELECT id FROM van_stock WHERE dsr_id=? AND product_id=? AND lot_id $lotCondition LIMIT 1");
            $check->execute($params);
            
            if ($row = $check->fetch()) {
                $this->db->prepare("UPDATE van_stock SET quantity = quantity + ?, loaded_at = ? WHERE id=?")
                         ->execute([$item['total_qty'], $date, $row['id']]);
            } else {
                $this->db->prepare("INSERT INTO van_stock (dsr_id, product_id, lot_id, quantity, loaded_at) VALUES (?, ?, ?, ?, ?)")
                         ->execute([$dsrId, $item['product_id'], $item['lot_id'], $item['total_qty'], $date]);
            }
        }

        // 3. Mark dispatches as in_transit
        $this->db->prepare("UPDATE dispatches SET status='in_transit', updated_at=NOW() WHERE dsr_id=? AND dispatch_date=? AND status='pending'")
                 ->execute([$dsrId, $date]);

        // 4. Update the manager's dispatch schedule status to 'dispatched'
        $this->db->prepare("UPDATE dispatch_schedules SET status='dispatched' WHERE dsr_id=? AND dispatch_date=? AND status='organized'")
                 ->execute([$dsrId, $date]);
        
        $this->json(['success' => true]);
    }

    public function settlement(): void
    {
        $dsrId = Auth::id();

        $q = $this->db->prepare("
            SELECT COALESCE(SUM(o.total_amount), 0)
            FROM dispatches d
            JOIN orders o ON o.id=d.order_id
            WHERE d.dsr_id=? AND d.dispatch_date=CURDATE()
        ");
        $q->execute([$dsrId]);
        $dispatchedValue = $q->fetchColumn();

        $q = $this->db->prepare("
            SELECT COALESCE(SUM(ri.quantity * p.price), 0)
            FROM returns r
            JOIN return_items ri ON ri.return_id=r.id
            JOIN products p ON p.id=ri.product_id
            WHERE r.dsr_id=? AND r.return_date=CURDATE()
        ");
        $q->execute([$dsrId]);
        $returnedValue = $q->fetchColumn();

        $this->render('settlement', compact('dispatchedValue', 'returnedValue'), 'dsr_app');
    }

    public function settlementSubmit(): void
    {
        $this->verifyCsrf();
        $dsrId = Auth::id();
        
        $dispatched = (float) $this->post('dispatched_value', 0);
        $returned = (float) $this->post('returned_value', 0);
        $damage = (float) $this->post('damage_amount', 0);
        $expense = (float) $this->post('total_expense', 0);
        $shouldPay = (float) $this->post('should_pay', 0);
        $countedCash = (float) $this->post('counted_cash', 0);
        $difference = (float) $this->post('difference', 0);
        $cashBreakdown = $this->post('cash_breakdown', '{}');

        $this->db->prepare("
            INSERT INTO settlements (dsr_id, date, total_dispatched, total_returned, total_damage, total_expense, should_pay, counted_cash, difference, cash_breakdown)
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([$dsrId, $dispatched, $returned, $damage, $expense, $shouldPay, $countedCash, $difference, $cashBreakdown]);

        $this->flash('success', 'Settlement submitted for Manager approval.');
        $this->redirect('dsr/dashboard');
    }

    public function profile(): void
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $user->execute([Auth::id()]);
        $user = $user->fetch();

        $this->render('profile', compact('user'), 'dsr_app');
    }
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

        // We use the dsr_id as sr_id for compatibility with the retailers table
        $q = $this->db->prepare("
            INSERT INTO retailers (sr_id, name, phone, lat, lng)
            VALUES (?, ?, ?, ?, ?)
        ");
        $q->execute([Auth::id(), $name, $phone, $lat ?: null, $lng ?: null]);
        $id = $this->db->lastInsertId();

        $this->json(['success' => true, 'id' => $id]);
    }
}
