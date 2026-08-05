<?php
/**
 * DSRController — Delivery Sales Rep panel
 */
class DSRController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    private static bool $schemaChecked = false;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER, ROLE_DSR]);
        $this->viewPath = MOD_PATH . '/DSR/views';
        $this->db = Database::getInstance();
        if (!self::$schemaChecked) {
            $this->ensurePaidAmountColumn();
            $this->ensureDeliveredQuantityColumn();
            $this->ensureReturnRetailerColumn();
            $this->ensureReadySaleColumn();
            self::$schemaChecked = true;
        }
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

    private function ensureReadySaleColumn(): void
    {
        try {
            $this->db->query("SELECT is_ready_sale FROM orders LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE orders ADD COLUMN is_ready_sale TINYINT(1) NOT NULL DEFAULT 0 AFTER total_amount");
            } catch (PDOException $ex) {
                // Ignore
            }
        }

        try {
            $this->db->query("SELECT is_ready_sale FROM dispatches LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE dispatches ADD COLUMN is_ready_sale TINYINT(1) NOT NULL DEFAULT 0 AFTER paid_amount");
            } catch (PDOException $ex) {
                // Ignore
            }
        }
    }

    /**
     * POST /dsr/damage/store
     * Saves damage report for a retailer visit.
     * Payload: csrf_token, retailer_id, total_amount, date, rows (JSON array of {sr_id, amount}) or products (fallback)
     */
    public function damageStore(): void
    {
        $this->verifyCsrf();
        $dsrId      = Auth::id();
        $retailerId = (int)($_POST['retailer_id'] ?? 0);
        $date       = $_POST['date'] ?? date('Y-m-d');
        $totalAmt   = (float)($_POST['total_amount'] ?? 0);
        $rows       = json_decode($_POST['rows'] ?? '[]', true);
        $products   = json_decode($_POST['products'] ?? '[]', true);

        if (empty($rows) && empty($products) && $totalAmt <= 0) {
            echo json_encode(['success' => false, 'message' => 'No damage data or amount provided.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Insert return header (damage type)
            $stmt = $this->db->prepare("
                INSERT INTO returns (dsr_id, retailer_id, return_date, status, reason)
                VALUES (?, ?, ?, 'approved', ?)
            ");

            if (!empty($rows)) {
                // Temporary manual damage mode: save each SR damage row as a damage return record
                foreach ($rows as $row) {
                    $amt = (float)($row['amount'] ?? 0);
                    $srId = (int)($row['sr_id'] ?? 0);
                    if ($amt > 0) {
                        $reasonText = 'Damage' . ($srId > 0 ? " | SR: {$srId} | Amount: {$amt}" : " | Amount: {$amt}");
                        $stmt->execute([$dsrId, $retailerId ?: null, $date, $reasonText]);
                    }
                }
            } else {
                $stmt->execute([$dsrId, $retailerId ?: null, $date, 'Damage']);
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
            }

            $this->db->commit();
            echo json_encode(['success' => true]);
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
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=CURDATE() AND status != 'pending'"); $q->execute([$dsrId]);
        $stats['todays_deliveries'] = $q->fetchColumn();

        // Ordered Retailers
        $q = $this->db->prepare("SELECT COUNT(DISTINCT COALESCE(o.dealer_id, o.id)) FROM dispatches d JOIN orders o ON o.id=d.order_id WHERE d.dsr_id=? AND d.dispatch_date=CURDATE() AND d.status != 'pending'"); $q->execute([$dsrId]);
        $stats['ordered_retailers'] = $q->fetchColumn();

        // Completed Deliveries
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status='delivered' AND dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['completed_deliveries'] = $q->fetchColumn();

        // Due Deliveries
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status='in_transit' AND dispatch_date=CURDATE()"); $q->execute([$dsrId]);
        $stats['due_deliveries'] = $q->fetchColumn();

        // Ready Sales
        $q = $this->db->prepare("SELECT COUNT(*) FROM readysales r JOIN users u ON u.warehouse_id = r.warehouse_id WHERE DATE(r.created_at)=CURDATE()"); $q->execute();
        $stats['ready_sales'] = $q->fetchColumn();

        // Pending Settlement
        $q = $this->db->prepare("SELECT COUNT(*) FROM settlements WHERE dsr_id=? AND status='pending'"); $q->execute([$dsrId]);
        $stats['pending_settlement'] = $q->fetchColumn();

        // Check if settlement is allowed (no unreturned dispatches for today)
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatch_schedules WHERE dsr_id=? AND (delivery_date=CURDATE() OR (delivery_date IS NULL AND dispatch_date=CURDATE())) AND status != 'returned'");
        $q->execute([$dsrId]);
        $unreturned_dispatches = $q->fetchColumn();
        $stats['can_settle'] = ($unreturned_dispatches == 0);

        // Today Delivery Rate %
        $totToday = (int)$stats['todays_deliveries'];
        $compToday = (int)$stats['completed_deliveries'];
        $stats['today_rate'] = $totToday > 0 ? round(($compToday / $totToday) * 100, 1) : 0;

        // Overall Average Delivery Rate %
        $q = $this->db->prepare("SELECT COUNT(*) as tot_all, SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) as tot_del FROM dispatches WHERE dsr_id=? AND status != 'pending'");
        $q->execute([$dsrId]);
        $rowAvg = $q->fetch();
        $totAll = (int)($rowAvg['tot_all'] ?? 0);
        $totDel = (int)($rowAvg['tot_del'] ?? 0);
        $stats['avg_rate'] = $totAll > 0 ? round(($totDel / $totAll) * 100, 1) : 0;

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
    }    public function vanStock(): void
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
        
        $totals = [
            'outside' => 0, 'outside_oc' => 0,
            'sale'    => 0, 'sale_oc'    => 0,
            'inside'  => 0, 'inside_oc'  => 0,
            'damage'  => 0, 'damage_oc'  => 0
        ];

        // Helper to fetch basic product info + trade_price
        // First get all relevant products to prevent many queries
        $allProductsStmt = $this->db->query("SELECT id, name, sku, pieces_per_box, price FROM products");
        $productMap = [];
        while($row = $allProductsStmt->fetch()) {
            $productMap[$row['id']] = $row;
        }

        // 1. OUTSIDE (Dispatches loaded onto van, excluding ready sales to avoid double load count)
        $outsideQ = $this->db->prepare("
            SELECT di.product_id, 
                   SUM(di.quantity) as qty,
                   SUM(di.quantity * (COALESCE(oi.unit_price, p.price) - p.price)) as oc_val
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            JOIN products p ON p.id = di.product_id
            LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status != 'pending' AND d.is_ready_sale = 0
            GROUP BY di.product_id
        ");
        $outsideQ->execute([$dsrId, $date]);
        $outsideDataMap = [];
        foreach ($outsideQ->fetchAll() as $row) {
            $pid = (int)$row['product_id'];
            $ocVal = (float)($row['oc_val'] ?? 0);
            $outsideDataMap[$pid] = [
                'qty' => (int)$row['qty'],
                'oc_val' => $ocVal
            ];
            if (isset($productMap[$pid])) {
                $p = $productMap[$pid];
                $val = $row['qty'] * $p['price'];
                $totals['outside'] += $val;
                $totals['outside_oc'] += $ocVal;
                $productsData['outside'][] = [
                    'name' => $p['name'],
                    'qty' => (int)$row['qty'],
                    'pcs_per_box' => (int)$p['pieces_per_box'],
                    'trade_price' => $p['price'],
                    'value' => $val,
                    'oc_value' => $ocVal
                ];
            }
        }

        // 2. SALE (Delivered orders including delivered ready sales)
        $saleQ = $this->db->prepare("
            SELECT di.product_id, 
                   SUM(COALESCE(di.delivered_quantity, 0)) as qty,
                   SUM(COALESCE(di.delivered_quantity, 0) * (COALESCE(oi.unit_price, p.price) - p.price)) as oc_val
            FROM dispatches d
            JOIN dispatch_items di ON d.id = di.dispatch_id
            JOIN products p ON p.id = di.product_id
            LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status IN ('delivered', 'partial')
            GROUP BY di.product_id
        ");
        $saleQ->execute([$dsrId, $date]);
        $saleDataMap = [];
        foreach ($saleQ->fetchAll() as $row) {
            $pid = (int)$row['product_id'];
            $qty = (int)$row['qty'];
            $ocVal = (float)($row['oc_val'] ?? 0);
            $saleDataMap[$pid] = [
                'qty' => $qty,
                'oc_val' => $ocVal
            ];
            if (isset($productMap[$pid]) && $qty > 0) {
                $p = $productMap[$pid];
                $val = $qty * $p['price'];
                $totals['sale'] += $val;
                $totals['sale_oc'] += $ocVal;
                $productsData['sale'][] = [
                    'name' => $p['name'],
                    'qty' => $qty,
                    'pcs_per_box' => (int)$p['pieces_per_box'],
                    'trade_price' => $p['price'],
                    'value' => $val,
                    'oc_value' => $ocVal
                ];
            }
        }

        // 3. INSIDE = Outside - Sale (per product)
        $allPids = array_unique(array_merge(array_keys($outsideDataMap), array_keys($saleDataMap)));
        foreach ($allPids as $pid) {
            if (!isset($productMap[$pid])) continue;
            $p         = $productMap[$pid];
            $oData     = $outsideDataMap[$pid] ?? ['qty' => 0, 'oc_val' => 0];
            $sData     = $saleDataMap[$pid]    ?? ['qty' => 0, 'oc_val' => 0];
            $insideQty = $oData['qty'] - $sData['qty'];
            if ($insideQty <= 0) continue;
            
            $insideVal   = $insideQty * $p['price'];
            $insideOcVal = max(0, $oData['oc_val'] - $sData['oc_val']);
            
            $totals['inside'] += $insideVal;
            $totals['inside_oc'] += $insideOcVal;
            
            $productsData['inside'][] = [
                'name'        => $p['name'],
                'qty'         => $insideQty,
                'pcs_per_box' => (int)$p['pieces_per_box'],
                'trade_price' => $p['price'],
                'value'       => $insideVal,
                'oc_value'    => $insideOcVal
            ];
        }

        // 4. DAMAGE (Returns marked as Damage, including manual damage entries)
        $damageQ = $this->db->prepare("
            SELECT r.reason, ri.product_id, SUM(ri.quantity) as qty
            FROM returns r
            LEFT JOIN return_items ri ON r.id = ri.return_id
            WHERE r.dsr_id = ? AND r.return_date = ? AND r.reason LIKE 'Damage%'
            GROUP BY r.id, ri.product_id, r.reason
        ");
        $damageQ->execute([$dsrId, $date]);
        foreach ($damageQ->fetchAll() as $row) {
            $pid = $row['product_id'];
            if ($pid && isset($productMap[$pid])) {
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
            } else {
                // Manual damage row entry recorded in reason text
                $reasonText = $row['reason'] ?? '';
                preg_match('/Amount:\s*([\d\.]+)/', $reasonText, $matches);
                $amt = isset($matches[1]) ? (float)$matches[1] : 0;
                if ($amt > 0) {
                    $totals['damage'] += $amt;
                    $productsData['damage'][] = [
                        'name' => 'Manual Damage Entry (' . $reasonText . ')',
                        'qty' => 1,
                        'pcs_per_box' => 1,
                        'trade_price' => $amt,
                        'value' => $amt,
                        'oc_value' => 0
                    ];
                }
            }
        }

        // Ensure Inside base total matches Outside base - Sale base
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
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status != 'pending'
            ORDER BY dealer_name ASC
        ");
        $q->execute([$dsrId, $selectedDate]);
        $flatRetailers = $q->fetchAll();

        // Group by dealer_id
        $grouped = [];
        $dispatchIds = array_column($flatRetailers, 'dispatch_id');
        $dispatchProducts = [];

        if (!empty($dispatchIds)) {
            $inClause = implode(',', array_map('intval', $dispatchIds));
            $iq = $this->db->query("
                SELECT di.dispatch_id, di.product_id, di.quantity, di.lot_id, di.delivered_quantity,
                       p.name, p.image, p.pieces_per_box, p.box_type,
                       p.price as base_price,
                       COALESCE(oi.unit_price, p.price) as price
                FROM dispatch_items di
                JOIN products p ON p.id = di.product_id
                JOIN dispatches d ON d.id = di.dispatch_id
                LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
                WHERE di.dispatch_id IN ($inClause)
            ");
            foreach ($iq->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $dispatchProducts[$row['dispatch_id']][] = $row;
            }
        }

        // Fetch retailer IDs that have damage recorded today
        $damagedRetailersStmt = $this->db->prepare("
            SELECT DISTINCT retailer_id 
            FROM returns 
            WHERE dsr_id = ? AND return_date = ? AND reason LIKE 'Damage%' AND retailer_id IS NOT NULL
        ");
        $damagedRetailersStmt->execute([$dsrId, $selectedDate]);
        $damagedRetailerIds = array_flip($damagedRetailersStmt->fetchAll(PDO::FETCH_COLUMN));

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
                    'has_damage' => isset($damagedRetailerIds[$ret['dealer_id']]),
                    'orders' => []
                ];
            }
            
            $products = $dispatchProducts[$ret['dispatch_id']] ?? [];
            
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
        $check = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND status='in_transit'");
        $check->execute([$dsrId, $selectedDate]);
        
        $qItems = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND status != 'pending'");
        $qItems->execute([$dsrId, $selectedDate]);
        
        $isCompleted = ($qItems->fetchColumn() > 0 && $check->fetchColumn() == 0);

        // Van stock: total dispatched qty minus delivered qty for today
        $vanQ = $this->db->prepare("
            SELECT di.product_id, 
                   SUM(di.quantity) as dispatched,
                   SUM(COALESCE(di.delivered_quantity, 0)) as delivered
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status != 'pending'
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

        // Active Sales Representatives who have products loaded on this DSR's van today with company name
        $srsStmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.name, COALESCE(c.name, 'No Company') as company_name 
            FROM dispatches d
            JOIN orders o ON o.id = d.order_id
            JOIN users u ON u.id = o.sr_id
            LEFT JOIN companies c ON c.id = u.company_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status != 'pending'
            ORDER BY u.name ASC
        ");
        $srsStmt->execute([$dsrId, $selectedDate]);
        $srsList = $srsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all retailers for Ready Sale selection
        $allRetailersStmt = $this->db->query("SELECT id, name, phone, address, lat, lng FROM retailers ORDER BY name ASC");
        $allRetailers = $allRetailersStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('delivery', compact('orderedRetailers', 'isCompleted', 'selectedDate', 'vanStockMap', 'srsList', 'allRetailers'), 'dsr_app');
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
            WHERE d.dsr_id=? AND d.dispatch_date=? AND d.status != 'pending'
            GROUP BY di.product_id, p.name, p.image
        ");
        $q->execute([$dsrId, $date]);
        $items = $q->fetchAll();

        $check = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND status='in_transit'");
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
        $this->db->prepare("UPDATE dispatch_schedules SET status='dispatched' WHERE dsr_id=? AND (delivery_date=? OR (delivery_date IS NULL AND dispatch_date=?)) AND status='organized'")
                 ->execute([$dsrId, $date, $date]);
        
        $this->json(['success' => true]);
    }

    public function settlement(): void
    {
        $dsrId = Auth::id();
        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        // Check dispatch schedule status for this date (must all be returned)
        $qSch = $this->db->prepare("
            SELECT COUNT(*) as total_schedules,
                   SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_schedules
            FROM dispatch_schedules
            WHERE dsr_id=? AND (delivery_date=? OR (delivery_date IS NULL AND dispatch_date=?))
        ");
        $qSch->execute([$dsrId, $selectedDate, $selectedDate]);
        $schRow = $qSch->fetch();
        $totSch = (int)($schRow['total_schedules'] ?? 0);
        $retSch = (int)($schRow['returned_schedules'] ?? 0);
        $scheduleStatus = ($totSch > 0 && $totSch === $retSch) ? 'returned' : 'pending';

        // Calculate Dispatched Value and Spot Return Value (from deliveries)
        $q = $this->db->prepare("
            SELECT 
                COALESCE(SUM(di.quantity * p.price), 0) as dispatched_value,
                COALESCE(SUM((di.quantity - COALESCE(di.delivered_quantity, 0)) * p.price), 0) as spot_return_value
            FROM dispatch_items di
            JOIN dispatches d ON d.id=di.dispatch_id
            JOIN products p ON p.id=di.product_id
            WHERE d.dsr_id=? AND d.dispatch_date=?
        ");
        $q->execute([$dsrId, $selectedDate]);
        $res = $q->fetch();
        
        $dispatchedValue = $res['dispatched_value'] ?: 0;
        $returnedValue   = $res['spot_return_value'] ?: 0;
        
        // Damage amount (calculates product return items OR manual damage entries recorded in returns header/reason)
        $q3 = $this->db->prepare("
            SELECT 
                COALESCE((
                    SELECT SUM(ri.quantity * p.price)
                    FROM returns r
                    JOIN return_items ri ON ri.return_id=r.id
                    JOIN products p ON p.id=ri.product_id
                    WHERE r.dsr_id=? AND r.return_date=? AND r.reason='Damage'
                ), 0)
                +
                COALESCE((
                    SELECT SUM(CAST(SUBSTRING_INDEX(r.reason, 'Amount: ', -1) AS DECIMAL(14,2)))
                    FROM returns r
                    LEFT JOIN return_items ri ON ri.return_id=r.id
                    WHERE r.dsr_id=? AND r.return_date=? AND r.reason LIKE 'Damage%' AND ri.id IS NULL
                ), 0)
        ");
        $q3->execute([$dsrId, $selectedDate, $dsrId, $selectedDate]);
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

        $this->render('settlement', compact('dispatchedValue', 'returnedValue', 'totalDamage', 'totalExpense', 'selectedDate', 'existingSettlement', 'scheduleStatus'), 'dsr_app');
    }

    public function settlementSubmit(): void
    {
        $this->verifyCsrf();
        $dsrId = Auth::id();
        $date = $this->post('settlement_date', date('Y-m-d'));

        // Validate that settlement is not already submitted
        $check = $this->db->prepare("SELECT id FROM settlements WHERE dsr_id=? AND date=?");
        $check->execute([$dsrId, $date]);
        if ($check->fetch()) {
            $this->flash('error', 'এই দিনের সেটেলমেন্ট ইতিমধ্যেই জমা দেওয়া হয়েছে।');
            $this->redirect('dsr/settlement?date=' . $date);
            return;
        }

        // Validate that dispatch schedule is returned
        $qSch = $this->db->prepare("
            SELECT COUNT(*) as total_schedules,
                   SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_schedules
            FROM dispatch_schedules
            WHERE dsr_id=? AND (delivery_date=? OR (delivery_date IS NULL AND dispatch_date=?))
        ");
        $qSch->execute([$dsrId, $date, $date]);
        $schRow = $qSch->fetch();
        $totSch = (int)($schRow['total_schedules'] ?? 0);
        $retSch = (int)($schRow['returned_schedules'] ?? 0);

        if ($totSch === 0 || $totSch !== $retSch) {
            $this->flash('error', 'ম্যানেজার কর্তৃক ডেলিভারি স্ট্যাটাস Returned হওয়ার পর সেটেলমেন্ট জমা দিতে পারবেন।');
            $this->redirect('dsr/settlement?date=' . $date);
            return;
        }
        
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

    public function apiSettlementReturns(): void
    {
        $dsrId = Auth::id();
        $date  = $_GET['date'] ?? date('Y-m-d');

        // Spot returns: dispatched but not delivered
        $q = $this->db->prepare("
            SELECT p.name AS product_name,
                   SUM(di.quantity - COALESCE(di.delivered_quantity, 0)) AS qty,
                   p.price,
                   SUM((di.quantity - COALESCE(di.delivered_quantity, 0)) * p.price) AS total
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            JOIN products p   ON p.id = di.product_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ?
              AND (di.quantity - COALESCE(di.delivered_quantity, 0)) > 0
            GROUP BY p.id, p.name, p.price
        ");
        $q->execute([$dsrId, $date]);
        $items = $q->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'items'   => $items,
        ]);
        exit;
    }

    public function profile(): void
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $user->execute([Auth::id()]);
        $user = $user->fetch();

        // Check if settlement is allowed
        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatch_schedules WHERE dsr_id=? AND (delivery_date=CURDATE() OR (delivery_date IS NULL AND dispatch_date=CURDATE())) AND status != 'returned'");
        $q->execute([Auth::id()]);
        $unreturned_dispatches = $q->fetchColumn();
        $can_settle = ($unreturned_dispatches == 0);

        $this->render('profile', compact('user', 'can_settle'), 'dsr_app');
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

        $q = $this->db->prepare("
            INSERT INTO retailers (name, phone, lat, lng)
            VALUES (?, ?, ?, ?)
        ");
        $q->execute([$name, $phone, $lat ?: null, $lng ?: null]);
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

    // ══════════════════════════════════════════════════════════
    //  Location Tracking — DSR pushes its GPS location
    // ══════════════════════════════════════════════════════════

    /** POST /dsr/api/location/push
     *  Body JSON or form-data: { lat, lng, address?, accuracy? }
     *  Records the DSR's current GPS position in dsr_locations.
     */
    public function apiPushLocation(): void
    {
        $dsrId = Auth::id();
        if (!$dsrId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Accept both JSON body and form-data
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $lat      = isset($body['lat'])      ? (float)$body['lat']      : (isset($_POST['lat'])      ? (float)$_POST['lat']      : null);
        $lng      = isset($body['lng'])      ? (float)$body['lng']      : (isset($_POST['lng'])      ? (float)$_POST['lng']      : null);
        $address  = isset($body['address'])  ? trim($body['address'])   : (isset($_POST['address'])  ? trim($_POST['address'])   : null);
        $accuracy = isset($body['accuracy']) ? (float)$body['accuracy'] : (isset($_POST['accuracy']) ? (float)$_POST['accuracy'] : null);

        if ($lat === null || $lng === null || abs($lat) > 90 || abs($lng) > 180) {
            $this->json(['success' => false, 'message' => 'Invalid coordinates'], 422);
            return;
        }

        $this->db->prepare("
            INSERT INTO dsr_locations (dsr_id, lat, lng, address, accuracy, recorded_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ")->execute([$dsrId, $lat, $lng, $address ?: null, $accuracy ?: null]);

        $this->json(['success' => true, 'message' => 'Location recorded']);
    }

    /**
     * GET /dsr/api/van-stock
     * Returns products currently available on DSR's van for the selected date
     */
    public function apiVanStock(): void
    {
        $dsrId = Auth::id();
        $date = $_GET['date'] ?? date('Y-m-d');

        $vanQ = $this->db->prepare("
            SELECT di.product_id, 
                   di.lot_id,
                   p.name as product_name,
                   p.sku,
                   p.price as base_price,
                   p.pieces_per_box,
                   c.name as company_name,
                   SUM(di.quantity) as dispatched_qty,
                   SUM(COALESCE(di.delivered_quantity, 0)) as delivered_qty
            FROM dispatch_items di
            JOIN dispatches d ON d.id = di.dispatch_id
            JOIN products p ON p.id = di.product_id
            LEFT JOIN companies c ON c.id = p.company_id
            WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status != 'pending'
            GROUP BY di.product_id, di.lot_id, p.id, p.name, p.sku, p.price, p.pieces_per_box, c.name
        ");
        $vanQ->execute([$dsrId, $date]);
        $items = [];
        foreach ($vanQ->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $available = (int)$row['dispatched_qty'] - (int)$row['delivered_qty'];
            if ($available > 0) {
                $items[] = [
                    'product_id'     => (int)$row['product_id'],
                    'lot_id'         => $row['lot_id'] ? (int)$row['lot_id'] : null,
                    'product_name'   => $row['product_name'],
                    'sku'            => $row['sku'],
                    'company_name'   => $row['company_name'] ?: 'No Company',
                    'base_price'     => (float)$row['base_price'],
                    'pieces_per_box' => (int)$row['pieces_per_box'],
                    'available_qty'  => $available
                ];
            }
        }

        $this->json(['success' => true, 'items' => $items]);
    }

    /**
     * POST /dsr/ready-sale/store
     * Processes on-the-spot Ready Sale by DSR
     */
    public function readySaleStore(): void
    {
        $this->verifyCsrf();
        $dsrId = Auth::id();
        $date = $this->post('date', date('Y-m-d'));
        $retailerId = (int)$this->post('retailer_id', 0);
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true) ?? [];

        if ($retailerId <= 0) {
            $this->json(['success' => false, 'message' => 'রিটেলার সিলেক্ট করুন।']);
            return;
        }

        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'অন্তত একটি প্রোডাক্ট নির্বাচন করুন।']);
            return;
        }

        // Get DSR user info to get warehouse_id
        $dsrUser = $this->db->prepare("SELECT warehouse_id FROM users WHERE id = ?");
        $dsrUser->execute([$dsrId]);
        $warehouseId = (int)$dsrUser->fetchColumn();

        if (!$warehouseId) {
            $wStmt = $this->db->prepare("SELECT warehouse_id FROM dispatches WHERE dsr_id = ? AND dispatch_date = ? LIMIT 1");
            $wStmt->execute([$dsrId, $date]);
            $warehouseId = (int)$wStmt->fetchColumn();
        }
        if (!$warehouseId) {
            $warehouseId = 1;
        }

        try {
            $this->db->beginTransaction();

            $totalAmount = 0.0;
            $orderItemsToInsert = [];

            foreach ($items as $item) {
                $pid       = (int)($item['product_id'] ?? 0);
                $qty       = (int)($item['qty'] ?? 0);
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $lotId     = !empty($item['lot_id']) ? (int)$item['lot_id'] : null;

                if ($pid <= 0 || $qty <= 0 || $unitPrice < 0) {
                    continue;
                }

                // Check available van stock
                $checkQ = $this->db->prepare("
                    SELECT SUM(di.quantity) - SUM(COALESCE(di.delivered_quantity, 0)) as avail
                    FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    WHERE d.dsr_id = ? AND d.dispatch_date = ? AND di.product_id = ? AND d.status != 'pending'
                ");
                $checkQ->execute([$dsrId, $date, $pid]);
                $avail = (int)$checkQ->fetchColumn();

                if ($qty > $avail) {
                    $pNameStmt = $this->db->prepare("SELECT name FROM products WHERE id = ?");
                    $pNameStmt->execute([$pid]);
                    $pName = $pNameStmt->fetchColumn();
                    $this->db->rollBack();
                    $this->json(['success' => false, 'message' => "প্রোডাক্ট '{$pName}'-এর পর্যাপ্ত স্টক ভ্যানে নেই। এভেলেবল: {$avail}, আপনি চেয়েছেন: {$qty}"]);
                    return;
                }

                $lineTotal = round($qty * $unitPrice, 2);
                $totalAmount += $lineTotal;

                $orderItemsToInsert[] = [
                    'product_id'  => $pid,
                    'lot_id'      => $lotId,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal
                ];
            }

            if (empty($orderItemsToInsert)) {
                $this->db->rollBack();
                $this->json(['success' => false, 'message' => 'সঠিক প্রোডাক্ট ও পরিমাণ দিন।']);
                return;
            }

            // 1. Insert Order
            $orderStmt = $this->db->prepare("
                INSERT INTO orders (sr_id, retailer_id, warehouse_id, status, total_amount, notes, is_ready_sale, created_at, updated_at)
                VALUES (?, ?, ?, 'delivered', ?, 'Ready Sale by DSR', 1, NOW(), NOW())
            ");
            $orderStmt->execute([$dsrId, $retailerId, $warehouseId, $totalAmount]);
            $orderId = (int)$this->db->lastInsertId();

            // 2. Insert Order Items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, lot_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($orderItemsToInsert as $oi) {
                $itemStmt->execute([$orderId, $oi['product_id'], $oi['lot_id'], $oi['quantity'], $oi['unit_price'], $oi['total_price']]);
            }

            // 3. Insert Dispatch
            $dispatchStmt = $this->db->prepare("
                INSERT INTO dispatches (order_id, dsr_id, warehouse_id, dispatch_date, status, notes, paid_amount, is_ready_sale, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'delivered', 'Ready Sale by DSR', ?, 1, NOW(), NOW())
            ");
            $dispatchStmt->execute([$orderId, $dsrId, $warehouseId, $date, $totalAmount]);
            $dispatchId = (int)$this->db->lastInsertId();

            // 4. Insert Dispatch Items & update existing delivered_quantity
            $dItemStmt = $this->db->prepare("
                INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, delivered_quantity)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($orderItemsToInsert as $oi) {
                $dItemStmt->execute([$dispatchId, $oi['product_id'], $oi['lot_id'], $oi['quantity'], $oi['quantity']]);

                // Allocate delivered_quantity to earlier active dispatch_items
                $activeDiStmt = $this->db->prepare("
                    SELECT di.id, di.quantity, COALESCE(di.delivered_quantity, 0) as del_qty
                    FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    WHERE d.dsr_id = ? AND d.dispatch_date = ? AND di.product_id = ? AND d.status != 'pending' AND d.id != ?
                    ORDER BY di.id ASC
                ");
                $activeDiStmt->execute([$dsrId, $date, $oi['product_id'], $dispatchId]);
                $remainingToAllocate = $oi['quantity'];
                foreach ($activeDiStmt->fetchAll(PDO::FETCH_ASSOC) as $diRow) {
                    if ($remainingToAllocate <= 0) break;
                    $canAllocate = (int)$diRow['quantity'] - (int)$diRow['del_qty'];
                    if ($canAllocate > 0) {
                        $allocate = min($remainingToAllocate, $canAllocate);
                        $updDi = $this->db->prepare("UPDATE dispatch_items SET delivered_quantity = COALESCE(delivered_quantity, 0) + ? WHERE id = ?");
                        $updDi->execute([$allocate, $diRow['id']]);
                        $remainingToAllocate -= $allocate;
                    }
                }

                // Deduct from van_stock table
                $this->db->prepare("UPDATE van_stock SET quantity = GREATEST(0, quantity - ?) WHERE dsr_id = ? AND product_id = ?")
                         ->execute([$oi['quantity'], $dsrId, $oi['product_id']]);
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'রেডি সেল সফলভাবে সম্পন্ন হয়েছে!']);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'রেডি সেল জমা দিতে ব্যর্থ হয়েছে: ' . $e->getMessage()]);
        }
    }
}
