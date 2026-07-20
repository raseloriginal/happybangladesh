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
        $this->ensurePaidAmountColumn();
        $this->ensureDeliveredQuantityColumn();
        $this->ensureReturnRetailerColumn();
    }

    private function ensurePaidAmountColumn(): void
    {
        try {
            $this->db->query("SELECT paid_amount FROM dispatches LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE dispatches ADD COLUMN paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER status");
            } catch (PDOException $ex) {
                // Ignore if add column fails (e.g. column already exists or lock issue)
            }
        }
    }

    private function ensureDeliveredQuantityColumn(): void
    {
        try {
            $this->db->query("SELECT delivered_quantity FROM dispatch_items LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE dispatch_items ADD COLUMN delivered_quantity INT UNSIGNED DEFAULT NULL AFTER quantity");
            } catch (PDOException $ex) {
                // Ignore if add column fails (e.g. column already exists or lock issue)
            }
        }
    }

    private function ensureReturnRetailerColumn(): void
    {
        try {
            $this->db->query("SELECT retailer_id FROM returns LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE returns ADD COLUMN retailer_id INT(11) DEFAULT NULL AFTER dsr_id");
            } catch (PDOException $ex) {
                // Ignore
            }
        }
    }

    /**
     * POST /dsr/damage/store
     * Saves damage report for a retailer visit.
     * Payload: csrf_token, retailer_id, total_amount, date, products (JSON array of {product_id, qty})
     */
    public function damageStore(): void
    {
        $this->verifyCsrf();
        $dsrId      = Auth::id();
        $retailerId = (int)($_POST['retailer_id'] ?? 0);
        $date       = $_POST['date'] ?? date('Y-m-d');
        $totalAmt   = (float)($_POST['total_amount'] ?? 0);
        $products   = json_decode($_POST['products'] ?? '[]', true);

        if (empty($products) || $totalAmt <= 0) {
            echo json_encode(['success' => false, 'message' => 'No products or amount provided.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Insert return header (damage type)
            $stmt = $this->db->prepare("
                INSERT INTO returns (dsr_id, retailer_id, return_date, status, reason)
                VALUES (?, ?, ?, 'approved', 'Damage')
            ");
            $stmt->execute([$dsrId, $retailerId ?: null, $date]);
            $returnId = $this->db->lastInsertId();

            // Insert return_items
            $itemStmt = $this->db->prepare("
                INSERT INTO return_items (return_id, product_id, quantity, reason)
                VALUES (?, ?, ?, 'Damage')
            ");
            foreach ($products as $p) {
                $pid = (int)($p['product_id'] ?? 0);
                $qty = (int)($p['qty'] ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $itemStmt->execute([$returnId, $pid, $qty]);
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'return_id' => $returnId]);
        } catch (PDOException $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
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
        $date = $_GET['date'] ?? date('Y-m-d');

        // We need to fetch products that have activity (outside, sale, inside, damage) for this DSR on this date.
        // We will build an aggregated structure in PHP.

        $productsData = [
            'outside' => [],
            'sale' => [],
            'inside' => [],
            'damage' => []
        ];
        
        $totals = ['outside' => 0, 'sale' => 0, 'inside' => 0, 'damage' => 0];

        // Helper to fetch basic product info + trade_price
        // First get all relevant products to prevent many queries
        $allProductsStmt = $this->db->query("SELECT id, name, sku, pieces_per_box, price FROM products");
        $productMap = [];
        while($row = $allProductsStmt->fetch()) {
            $productMap[$row['id']] = $row;
        }

        // 1. OUTSIDE (Dispatches loaded onto van)
        $outsideQ = $this->db->prepare("
            SELECT di.product_id, SUM(di.quantity) as qty
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ?
            GROUP BY di.product_id
        ");
        $outsideQ->execute([$dsrId, $date]);
        foreach ($outsideQ->fetchAll() as $row) {
            $pid = $row['product_id'];
            if(isset($productMap[$pid])) {
                $p = $productMap[$pid];
                $val = $row['qty'] * $p['price'];
                $totals['outside'] += $val;
                $productsData['outside'][] = [
                    'name' => $p['name'],
                    'qty' => (int)$row['qty'],
                    'pcs_per_box' => (int)$p['pieces_per_box'],
                    'trade_price' => $p['price'],
                    'value' => $val,
                    'oc_value' => 0
                ];
            }
        }

        // 2. SALE (Delivered orders)
        // Orders created on that date by ANY SR, but dispatched by this DSR on that date.
        // Or simply `delivered_quantity` in dispatch_items? Actually order_items is better.
        // Let's use dispatch_items.delivered_quantity which is updated when DSR confirms delivery.
        $saleQ = $this->db->prepare("
            SELECT di.product_id, SUM(di.delivered_quantity) as qty
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND DATE(d.updated_at) = ? AND d.status IN ('delivered', 'partial')
            GROUP BY di.product_id
        ");
        $saleQ->execute([$dsrId, $date]);
        foreach ($saleQ->fetchAll() as $row) {
            $pid = $row['product_id'];
            if(isset($productMap[$pid]) && $row['qty'] > 0) {
                $p = $productMap[$pid];
                $val = $row['qty'] * $p['price'];
                $totals['sale'] += $val;
                $productsData['sale'][] = [
                    'name' => $p['name'],
                    'qty' => (int)$row['qty'],
                    'pcs_per_box' => (int)$p['pieces_per_box'],
                    'trade_price' => $p['price'],
                    'value' => $val,
                    'oc_value' => 0 // In real app, calculate actual OC if stored
                ];
            }
        }

        // 3. INSIDE = Outside - Sale (per product)
        // Build a map of outside quantities keyed by product_id for easy subtraction
        $outsideQtyMap = [];
        foreach ($productsData['outside'] as $item) {
            // find product_id from productMap by name match is fragile; re-query instead
        }
        // Re-fetch outside as map keyed by product_id
        $outsideMapQ = $this->db->prepare("
            SELECT di.product_id, SUM(di.quantity) as qty
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ?
            GROUP BY di.product_id
        ");
        $outsideMapQ->execute([$dsrId, $date]);
        foreach ($outsideMapQ->fetchAll() as $row) {
            $outsideQtyMap[(int)$row['product_id']] = (int)$row['qty'];
        }

        // Re-fetch sale as map keyed by product_id
        $saleQtyMap = [];
        $saleMapQ = $this->db->prepare("
            SELECT di.product_id, SUM(di.delivered_quantity) as qty
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND DATE(d.updated_at) = ? AND d.status IN ('delivered', 'partial')
            GROUP BY di.product_id
        ");
        $saleMapQ->execute([$dsrId, $date]);
        foreach ($saleMapQ->fetchAll() as $row) {
            $saleQtyMap[(int)$row['product_id']] = (int)$row['qty'];
        }

        // Inside = Outside qty - Sale qty for each product that was dispatched
        $allPids = array_unique(array_merge(array_keys($outsideQtyMap), array_keys($saleQtyMap)));
        foreach ($allPids as $pid) {
            if (!isset($productMap[$pid])) continue;
            $p        = $productMap[$pid];
            $oQty     = $outsideQtyMap[$pid] ?? 0;
            $sQty     = $saleQtyMap[$pid]    ?? 0;
            $insideQty = $oQty - $sQty;
            if ($insideQty <= 0) continue;
            $val = $insideQty * $p['price'];
            $totals['inside'] += $val;
            $productsData['inside'][] = [
                'name'        => $p['name'],
                'qty'         => $insideQty,
                'pcs_per_box' => (int)$p['pieces_per_box'],
                'trade_price' => $p['price'],
                'value'       => $val,
                'oc_value'    => 0
            ];
        }

        // 4. DAMAGE (Returns marked as Damage)
        $damageQ = $this->db->prepare("
            SELECT ri.product_id, SUM(ri.quantity) as qty
            FROM returns r
            JOIN return_items ri ON r.id = ri.return_id
            WHERE r.dsr_id = ? AND r.return_date = ? AND ri.reason = 'Damage'
            GROUP BY ri.product_id
        ");
        $damageQ->execute([$dsrId, $date]);
        foreach ($damageQ->fetchAll() as $row) {
            $pid = $row['product_id'];
            if(isset($productMap[$pid])) {
                $p = $productMap[$pid];
                $val = $row['qty'] * $p['price'];
                $totals['damage'] += $val;
                $productsData['damage'][] = [
                    'name' => $p['name'],
                    'qty' => (int)$row['qty'],
                    'pcs_per_box' => (int)$p['pieces_per_box'],
                    'trade_price' => $p['price'],
                    'value' => $val,
                    'oc_value' => 0
                ];
            }
        }

        // Final: Inside total = Outside total - Sale total
        $totals['inside'] = max(0, $totals['outside'] - $totals['sale']);

        $this->render('van_stock', [
            'products' => $productsData,
            'totals' => $totals,
            'selectedDate' => $date
        ], 'dsr_app');
    }

    public function expenses(): void
    {
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        $items = $this->db->prepare("SELECT * FROM expenses WHERE dsr_id=? AND date=? ORDER BY created_at DESC");
        $items->execute([Auth::id(), $selectedDate]);
        $items = $items->fetchAll();
        $this->render('expenses', compact('items', 'selectedDate'), 'dsr_app');
    }

    public function expenseStore(): void
    {
        $this->verifyCsrf();
        $date = $this->post('date', date('Y-m-d'));
        $this->db->prepare("INSERT INTO expenses (dsr_id,date,category,amount,description) VALUES (?,?,?,?,?)")
                 ->execute([Auth::id(), $date, $this->post('category','other'), $this->post('amount',0), trim($this->post('description',''))]);
        $this->flash('success', 'Expense recorded.'); 
        $this->redirect('dsr/expenses?date=' . $date);
    }

    public function delivery(): void
    {
        $dsrId = Auth::id();
        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        // Fetch only dispatches that are physically on the van (in_transit, partial) or delivered today
        $q = $this->db->prepare("
            SELECT d.id as dispatch_id, o.id as order_id, COALESCE(dl.id, r.id) as dealer_id,
                   COALESCE(dl.name, r.name) as dealer_name, 
                   r.name as retailer_name, dl.name as dealer_business_name,
                   COALESCE(dl.address, r.address) as address, 
                   COALESCE(dl.lat, r.lat) as lat, 
                   COALESCE(dl.lng, r.lng) as lng,
                   o.total_amount, d.status, d.paid_amount,
                   c.name as company_name
            FROM dispatches d
            JOIN orders o ON o.id = d.order_id
            JOIN users u ON u.id = o.sr_id
            LEFT JOIN companies c ON c.id = u.company_id
            LEFT JOIN dealers dl ON dl.id = o.dealer_id
            LEFT JOIN retailers r ON r.id = o.retailer_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ?
            ORDER BY dealer_name ASC
        ");
        $q->execute([$dsrId, $selectedDate]);
        $flatRetailers = $q->fetchAll();

        // Group by dealer_id
        $grouped = [];
        foreach ($flatRetailers as $ret) {
            $did = $ret['dealer_id'] ?? 'unknown_'.uniqid();
            if (!isset($grouped[$did])) {
                $grouped[$did] = [
                    'dealer_id' => $ret['dealer_id'],
                    'dealer_name' => $ret['dealer_name'],
                    'retailer_name' => $ret['retailer_name'],
                    'dealer_business_name' => $ret['dealer_business_name'],
                    'address' => $ret['address'],
                    'lat' => $ret['lat'],
                    'lng' => $ret['lng'],
                    'orders' => []
                ];
            }
            
            // Fetch products for this dispatch
            $iq = $this->db->prepare("
                SELECT di.product_id, di.quantity, di.lot_id, di.delivered_quantity,
                       p.name, p.image, p.pieces_per_box, 
                       COALESCE(oi.unit_price, p.price) as price
                FROM dispatch_items di
                JOIN products p ON p.id = di.product_id
                LEFT JOIN order_items oi ON oi.order_id = ? AND oi.product_id = di.product_id
                WHERE di.dispatch_id = ?
            ");
            $iq->execute([$ret['order_id'], $ret['dispatch_id']]);
            $products = $iq->fetchAll();
            
            $grouped[$did]['orders'][] = [
                'dispatch_id' => $ret['dispatch_id'],
                'order_id' => $ret['order_id'],
                'total_amount' => $ret['total_amount'],
                'status' => $ret['status'],
                'paid_amount' => $ret['paid_amount'],
                'company_name' => $ret['company_name'] ?: 'Unknown Company',
                'products' => $products
            ];
        }
        
        $orderedRetailers = array_values($grouped);

        // Check if collection is complete
        $check = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND status='pending'");
        $check->execute([$dsrId, $selectedDate]);
        
        $qItems = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=?");
        $qItems->execute([$dsrId, $selectedDate]);
        
        $isCompleted = ($qItems->fetchColumn() > 0 && $check->fetchColumn() == 0);

        // Van stock: total dispatched qty minus delivered qty for today
        $vanQ = $this->db->prepare("
            SELECT di.product_id, 
                   SUM(di.quantity) as dispatched,
                   SUM(COALESCE(di.delivered_quantity, 0)) as delivered
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ?
            GROUP BY di.product_id
        ");
        $vanQ->execute([$dsrId, $selectedDate]);
        $vanStockMap = [];
        foreach ($vanQ->fetchAll() as $row) {
            $remaining = (int)$row['dispatched'] - (int)$row['delivered'];
            if ($remaining > 0) {
                $vanStockMap[(int)$row['product_id']] = $remaining;
            }
        }

        $this->render('delivery', compact('orderedRetailers', 'isCompleted', 'selectedDate', 'vanStockMap'), 'dsr_app');
    }

    public function deliveryUpdate(string $id): void
    {
        $status = $this->post('status', 'delivered');
        $paidAmount = (float) $this->post('paid_amount', 0);
        $dsrId = Auth::id();
        
        // Check if settlement is already submitted/approved for this dispatch's date
        $dispatch = $this->db->prepare("SELECT dispatch_date FROM dispatches WHERE id=? AND dsr_id=?");
        $dispatch->execute([$id, $dsrId]);
        $dispatchDate = $dispatch->fetchColumn();

        if ($dispatchDate) {
            $check = $this->db->prepare("SELECT status FROM settlements WHERE dsr_id=? AND date=? AND status IN ('pending', 'approved')");
            $check->execute([$dsrId, $dispatchDate]);
            if ($check->fetch()) {
                $this->json(['success' => false, 'message' => 'Settlement already submitted for this date. Cannot modify delivery.']);
                return;
            }
        }
        
        $notes = $this->post('notes', null);
        
        $this->db->prepare("UPDATE dispatches SET status=?, paid_amount=?, notes=?, updated_at=NOW() WHERE id=? AND dsr_id=?")
                 ->execute([$status, $paidAmount, $notes, $id, $dsrId]);
        
        // Deduct/adjust van_stock based on dispatch items
        $items = $this->db->prepare("SELECT product_id, lot_id, quantity, delivered_quantity FROM dispatch_items WHERE dispatch_id=?");
        $items->execute([$id]);
        $items = $items->fetchAll();
        
        $deliveredItemsStr = $this->post('items', '{}');
        $deliveredItems = json_decode($deliveredItemsStr, true) ?? [];
        
        foreach($items as $item) {
            $prevDelivered = $item['delivered_quantity'] !== null ? (int)$item['delivered_quantity'] : 0;
            
            if ($status === 'cancelled') {
                $newDelivered = 0;
            } else {
                // If specific delivery amounts are provided from frontend, use them
                // Otherwise, default to full quantity (for complete)
                $newDelivered = $item['quantity'];
                if (isset($deliveredItems[$item['product_id']])) {
                    $newDelivered = (int) $deliveredItems[$item['product_id']];
                    if ($newDelivered > $item['quantity']) {
                        $newDelivered = $item['quantity'];
                    }
                }
            }
            
            $diff = $newDelivered - $prevDelivered;
            
            if ($diff != 0) {
                $this->db->prepare("UPDATE van_stock SET quantity = quantity - ? WHERE dsr_id=? AND product_id=? AND (lot_id=? OR (? IS NULL AND lot_id IS NULL))")
                         ->execute([$diff, $dsrId, $item['product_id'], $item['lot_id'], $item['lot_id']]);
            }
            
            // Save the new delivered quantity in DB
            $this->db->prepare("UPDATE dispatch_items SET delivered_quantity = ? WHERE dispatch_id = ? AND product_id = ?")
                     ->execute([$newDelivered, $id, $item['product_id']]);
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
        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        // Calculate Dispatched Value and Spot Return Value (from deliveries)
        $q = $this->db->prepare("
            SELECT 
                COALESCE(SUM(di.quantity * p.price), 0) as dispatched_value,
                COALESCE(SUM((di.quantity - COALESCE(di.delivered_quantity, di.quantity)) * p.price), 0) as spot_return_value
            FROM dispatch_items di
            JOIN dispatches d ON d.id=di.dispatch_id
            JOIN products p ON p.id=di.product_id
            WHERE d.dsr_id=? AND d.dispatch_date=?
        ");
        $q->execute([$dsrId, $selectedDate]);
        $res = $q->fetch();
        
        $dispatchedValue = $res['dispatched_value'] ?: 0;
        $spotReturnValue = $res['spot_return_value'] ?: 0;

        // Formal returns (if any)
        $q2 = $this->db->prepare("
            SELECT COALESCE(SUM(ri.quantity * p.price), 0)
            FROM returns r
            JOIN return_items ri ON ri.return_id=r.id
            JOIN products p ON p.id=ri.product_id
            WHERE r.dsr_id=? AND r.return_date=? AND (r.reason != 'Damage' OR r.reason IS NULL)
        ");
        $q2->execute([$dsrId, $selectedDate]);
        $formalReturnValue = $q2->fetchColumn();

        $returnedValue = $spotReturnValue + $formalReturnValue;
        
        // Damage amount
        $q3 = $this->db->prepare("
            SELECT COALESCE(SUM(ri.quantity * p.price), 0)
            FROM returns r
            JOIN return_items ri ON ri.return_id=r.id
            JOIN products p ON p.id=ri.product_id
            WHERE r.dsr_id=? AND r.return_date=? AND r.reason='Damage'
        ");
        $q3->execute([$dsrId, $selectedDate]);
        $totalDamage = (float) $q3->fetchColumn();

        // Total Expenses
        $q4 = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE dsr_id=? AND date=?
        ");
        $q4->execute([$dsrId, $selectedDate]);
        $totalExpense = (float) $q4->fetchColumn();

        // Check if settlement already submitted for this date
        $check = $this->db->prepare("SELECT * FROM settlements WHERE dsr_id=? AND date=?");
        $check->execute([$dsrId, $selectedDate]);
        $existingSettlement = $check->fetch() ?: null;

        $this->render('settlement', compact('dispatchedValue', 'returnedValue', 'totalDamage', 'totalExpense', 'selectedDate', 'existingSettlement'), 'dsr_app');
    }

    public function settlementSubmit(): void
    {
        $this->verifyCsrf();
        $dsrId = Auth::id();
        $date = $this->post('settlement_date', date('Y-m-d'));
        
        $dispatched = (float) $this->post('dispatched_value', 0);
        $returned = (float) $this->post('returned_value', 0);
        $damage = (float) $this->post('damage_amount', 0);
        $expense = (float) $this->post('total_expense', 0);
        $shouldPay = (float) $this->post('should_pay', 0);
        $countedCash = (float) $this->post('counted_cash', 0);
        $difference = (float) $this->post('difference', 0);
        
        $cashBreakdown = json_decode($this->post('cash_breakdown', '{}'), true) ?? [];
        $cashBreakdown['note'] = trim($this->post('note', ''));
        $cashBreakdownStr = json_encode($cashBreakdown);

        $this->db->prepare("
            INSERT INTO settlements (dsr_id, date, total_dispatched, total_returned, total_damage, total_expense, should_pay, counted_cash, difference, cash_breakdown)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([$dsrId, $date, $dispatched, $returned, $damage, $expense, $shouldPay, $countedCash, $difference, $cashBreakdownStr]);

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

    public function apiCompanyProducts(): void
    {
        $dsrId = Auth::id();
        $dispatchIds = $_GET['dispatch_ids'] ?? '';
        $dispatchIdsArray = array_filter(array_map('intval', explode(',', $dispatchIds)));

        if (empty($dispatchIdsArray)) {
            $this->json(['success' => true, 'products' => []]);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($dispatchIdsArray), '?'));
        
        // Fetch products of the companies that are present in the specified dispatches
        $q = $this->db->prepare("
            SELECT p.*, c.name AS company_name, cat.name AS category_name
            FROM products p
            LEFT JOIN companies c ON c.id = p.company_id
            LEFT JOIN categories cat ON cat.id = p.category_id
            WHERE p.status = 1 AND p.company_id IN (
                SELECT DISTINCT p2.company_id
                FROM dispatches d
                JOIN orders o ON o.id = d.order_id
                JOIN order_items oi ON oi.order_id = o.id
                JOIN products p2 ON p2.id = oi.product_id
                WHERE d.dsr_id = ? AND d.id IN ($placeholders)
            )
            ORDER BY p.name
        ");
        
        $params = array_merge([$dsrId], $dispatchIdsArray);
        $q->execute($params);
        $products = $q->fetchAll(PDO::FETCH_ASSOC);

        $this->json(['success' => true, 'products' => $products]);
    }
}
