<?php
/**
 * DealerController — handles dealer panel pages
 */
class DealerController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        $this->viewPath = MOD_PATH . '/Dealer/views';
        $this->db = Database::getInstance();
    }

    protected function render(string $view, array $data = [], string $layout = 'dealer'): void
    {
        parent::render($view, $data, $layout);
    }

    // ══════════════════════════════════════════════════════════
    //  Authentication
    // ══════════════════════════════════════════════════════════
    public function login(): void
    {
        if (isset($_SESSION['dealer_id'])) {
            header('Location: ' . BASE_URL . '/dealer/dashboard');
            exit;
        }
        $this->render('login', [], '');
    }

    public function loginSubmit(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $stmt = $this->db->prepare("SELECT * FROM dealers WHERE username = ? AND status = 1");
        $stmt->execute([$username]);
        $dealer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dealer && password_verify($password, $dealer['password'])) {
            $_SESSION['dealer_id'] = $dealer['id'];
            $_SESSION['dealer_name'] = $dealer['name'];
            $_SESSION['dealer_warehouse_id'] = $dealer['warehouse_id'];
            
            header('Location: ' . BASE_URL . '/dealer/dashboard');
            exit;
        }

        $this->flash('error', 'Invalid username or password.');
        header('Location: ' . BASE_URL . '/dealer/login');
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['dealer_id']);
        unset($_SESSION['dealer_name']);
        unset($_SESSION['dealer_warehouse_id']);
        header('Location: ' . BASE_URL . '/dealer/login');
        exit;
    }
    
    private function checkAuth(): void
    {
        if (!isset($_SESSION['dealer_id'])) {
            header('Location: ' . BASE_URL . '/dealer/login');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  Dashboard
    // ══════════════════════════════════════════════════════════
    // Helper to get aggregated metrics and daily breakdown
    private function getMetricsForPeriod(int $dealerId, int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        $dStmt = $this->db->prepare("SELECT warehouse_id FROM dealers WHERE id = ?");
        $dStmt->execute([$dealerId]);
        $warehouseId = $dStmt->fetchColumn() ?: 0;

        $srStmt = $this->db->prepare("SELECT DISTINCT sr_id FROM dealer_companies WHERE dealer_id = ?");
        $srStmt->execute([$dealerId]);
        $srIds = $srStmt->fetchAll(PDO::FETCH_COLUMN);

        $totals = [
            'gross_sale' => 0,
            'net_sale' => 0,
            'gross_profit' => 0,
            'net_profit' => 0,
            'success_out' => 0,
            'success_sell' => 0,
            'success_rate' => 0,
            'damage' => 0
        ];
        
        $daily = [];

        if (empty($srIds)) {
            return ['totals' => $totals, 'daily' => $daily];
        }

        $inStr = implode(',', $srIds);
        
        // Get dates
        if ($startDate && $endDate) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT date FROM (
                    SELECT DATE(d.dispatch_date) as date FROM dispatches d LEFT JOIN orders o ON d.order_id = o.id WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ? OR d.warehouse_id = ?) AND DATE(d.dispatch_date) BETWEEN ? AND ?
                    UNION
                    SELECT DATE(r.return_date) as date FROM returns r LEFT JOIN dispatches d ON r.dispatch_id = d.id LEFT JOIN orders o ON d.order_id = o.id WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) AND DATE(r.return_date) BETWEEN ? AND ?
                    UNION
                    SELECT DATE(o.created_at) as date FROM orders o WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) AND DATE(o.created_at) BETWEEN ? AND ?
                ) as dates ORDER BY date ASC
            ");
            $stmt->execute([$dealerId, $warehouseId, $startDate, $endDate, $dealerId, $startDate, $endDate, $dealerId, $startDate, $endDate]);
        } else {
            $stmt = $this->db->prepare("
                SELECT DISTINCT date FROM (
                    SELECT DATE(d.dispatch_date) as date FROM dispatches d LEFT JOIN orders o ON d.order_id = o.id WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ? OR d.warehouse_id = ?) AND d.dispatch_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    UNION
                    SELECT DATE(r.return_date) as date FROM returns r LEFT JOIN dispatches d ON r.dispatch_id = d.id LEFT JOIN orders o ON d.order_id = o.id WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) AND r.return_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    UNION
                    SELECT DATE(o.created_at) as date FROM orders o WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ) as dates ORDER BY date ASC
            ");
            $stmt->execute([$dealerId, $warehouseId, $days, $dealerId, $days, $dealerId, $days]);
        }
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $priceStmt = $this->db->query("SELECT id, price, buying_price FROM products");
        $products = [];
        while ($row = $priceStmt->fetch(PDO::FETCH_ASSOC)) {
            $products[$row['id']] = $row;
        }

        foreach ($dates as $date) {
            $outStmt = $this->db->prepare("
                SELECT di.product_id, SUM(di.quantity) as qty
                FROM dispatch_items di
                JOIN dispatches d ON di.dispatch_id = d.id
                LEFT JOIN orders o ON d.order_id = o.id
                WHERE DATE(d.dispatch_date) = ? AND (o.sr_id IN ($inStr) OR o.dealer_id = ? OR d.warehouse_id = ?)
                GROUP BY di.product_id
            ");
            $outStmt->execute([$date, $dealerId, $warehouseId]);
            $outData = $outStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $inStmt = $this->db->prepare("
                SELECT ri.product_id, SUM(ri.quantity) as qty
                FROM return_items ri
                JOIN returns r ON ri.return_id = r.id
                LEFT JOIN dispatches d ON r.dispatch_id = d.id
                LEFT JOIN orders o ON d.order_id = o.id
                WHERE DATE(r.return_date) = ? AND (o.sr_id IN ($inStr) OR o.dealer_id = ?)
                GROUP BY ri.product_id
            ");
            $inStmt->execute([$date, $dealerId]);
            $inData = $inStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $orderStmt = $this->db->prepare("
                SELECT oi.product_id, SUM(oi.quantity) as qty
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE DATE(o.created_at) = ? AND (o.sr_id IN ($inStr) OR o.dealer_id = ?)
                GROUP BY oi.product_id
            ");
            $orderStmt->execute([$date, $dealerId]);
            $orderData = $orderStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $dayGrossSale = 0;
            $dayNetSale = 0;
            $dayGrossProfit = 0;

            $allProductIds = array_unique(array_merge(array_keys($outData), array_keys($inData), array_keys($orderData)));
            foreach ($allProductIds as $pid) {
                $outQty = $outData[$pid] ?? 0;
                $inQty = $inData[$pid] ?? 0;
                $orderQty = $orderData[$pid] ?? 0;
                
                $dispatchQty = $outQty > 0 ? $outQty : $orderQty;
                $sellQty = max(0, $dispatchQty - $inQty);
                
                if (isset($products[$pid])) {
                    $p = $products[$pid];
                    $itemNetSale = $sellQty * $p['buying_price'];
                    $itemTotalSale = $sellQty * $p['price'];
                    
                    $dayNetSale += $itemNetSale;
                    $dayGrossSale += $itemTotalSale;
                    $dayGrossProfit += ($itemTotalSale - $itemNetSale);
                }

                $totals['success_out'] += $dispatchQty;
                $totals['success_sell'] += $sellQty;
            }

            $dayNetProfit = $dayGrossProfit / 2;
            
            $totals['gross_sale'] += $dayGrossSale;
            $totals['net_sale'] += $dayNetSale;
            $totals['gross_profit'] += $dayGrossProfit;
            $totals['net_profit'] += $dayNetProfit;

            $daily[$date] = [
                'net_profit' => $dayNetProfit,
                'gross_sale' => $dayGrossSale
            ];
        }

        $totals['success_rate'] = $totals['success_out'] > 0 
            ? ($totals['success_sell'] / $totals['success_out']) * 100 
            : ($totals['success_sell'] > 0 ? 100 : 0);

        return ['totals' => $totals, 'daily' => $daily];
    }

    public function dashboard(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];

        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');

        $metrics = $this->getMetricsForPeriod($dealerId, 30, $startDate, $endDate);
        $stats = $metrics['totals'];
        $dailyData = $metrics['daily'];

        $chartData = [];
        $labels = [];
        
        if ($startDate && $endDate) {
            $curr = strtotime($startDate);
            $last = strtotime($endDate);
            while ($curr <= $last) {
                $date = date('Y-m-d', $curr);
                $labels[] = date('M j', $curr);
                $chartData[] = $dailyData[$date]['net_profit'] ?? 0;
                $curr = strtotime('+1 day', $curr);
            }
        } else {
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M j', strtotime($date));
                $chartData[] = $dailyData[$date]['net_profit'] ?? 0;
            }
        }

        $this->render('dashboard', compact('stats', 'chartData', 'labels'));
    }

    // ══════════════════════════════════════════════════════════
    //  Transactions
    // ══════════════════════════════════════════════════════════
    //  Daily Bills (Transactions)
    // ══════════════════════════════════════════════════════════
    public function transactions(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];

        $srStmt = $this->db->prepare("SELECT DISTINCT sr_id FROM dealer_companies WHERE dealer_id = ?");
        $srStmt->execute([$dealerId]);
        $srIds = $srStmt->fetchAll(PDO::FETCH_COLUMN);

        $bills = [];
        if (!empty($srIds)) {
            $inStr = implode(',', $srIds);
            
            $startDate = $this->get('start_date');
            $endDate = $this->get('end_date');
            
            if ($startDate && $endDate) {
                $stmt = $this->db->prepare("
                    SELECT DISTINCT date FROM (
                        SELECT DISTINCT DATE(dispatch_date) as date FROM dispatches WHERE dsr_id IN ($inStr) AND DATE(dispatch_date) BETWEEN ? AND ?
                        UNION
                        SELECT DISTINCT DATE(return_date) as date FROM returns WHERE dsr_id IN ($inStr) AND DATE(return_date) BETWEEN ? AND ?
                        UNION
                        SELECT DISTINCT DATE(created_at) as date FROM orders WHERE sr_id IN ($inStr) AND DATE(created_at) BETWEEN ? AND ?
                    ) as dates ORDER BY date DESC
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
            } else {
                $stmt = $this->db->query("
                    SELECT DISTINCT date FROM (
                        SELECT DISTINCT DATE(dispatch_date) as date FROM dispatches WHERE dsr_id IN ($inStr)
                        UNION
                        SELECT DISTINCT DATE(return_date) as date FROM returns WHERE dsr_id IN ($inStr)
                        UNION
                        SELECT DISTINCT DATE(created_at) as date FROM orders WHERE sr_id IN ($inStr)
                    ) as dates ORDER BY date DESC LIMIT 30
                ");
            }
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Fetch product prices to calculate totals
            $priceStmt = $this->db->query("SELECT id, price, buying_price FROM products");
            $products = [];
            while ($row = $priceStmt->fetch(PDO::FETCH_ASSOC)) {
                $products[$row['id']] = $row;
            }

            foreach ($dates as $date) {
                // Out Data
                $outStmt = $this->db->prepare("
                    SELECT di.product_id, SUM(di.quantity) as qty
                    FROM dispatch_items di
                    JOIN dispatches d ON di.dispatch_id = d.id
                    WHERE DATE(d.dispatch_date) = ? AND d.dsr_id IN ($inStr)
                    GROUP BY di.product_id
                ");
                $outStmt->execute([$date]);
                $outData = $outStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                // In Data
                $inStmt = $this->db->prepare("
                    SELECT ri.product_id, SUM(ri.quantity) as qty
                    FROM return_items ri
                    JOIN returns r ON ri.return_id = r.id
                    WHERE DATE(r.return_date) = ? AND r.dsr_id IN ($inStr)
                    GROUP BY ri.product_id
                ");
                $inStmt->execute([$date]);
                $inData = $inStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                // Order Data
                $orderStmt = $this->db->prepare("
                    SELECT oi.product_id, SUM(oi.quantity) as qty
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE DATE(o.created_at) = ? AND o.sr_id IN ($inStr)
                    GROUP BY oi.product_id
                ");
                $orderStmt->execute([$date]);
                $orderData = $orderStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $dayOutQty = 0;
                $dayOutValue = 0;
                $dayInQty = 0;
                $dayInValue = 0;
                $daySellQty = 0;
                $grossSale = 0;
                $netSale = 0;
                $grossProfit = 0;
                $successOut = 0;
                $successSell = 0;

                $allProductIds = array_unique(array_merge(array_keys($outData), array_keys($inData), array_keys($orderData)));
                foreach ($allProductIds as $pid) {
                    $outQty = $outData[$pid] ?? 0;
                    $inQty = $inData[$pid] ?? 0;
                    $orderQty = $orderData[$pid] ?? 0;
                    
                    // If there's an out_qty, calculate sold as out - in. If not, use order qty directly.
                    $sellQty = $outQty > 0 ? max(0, $outQty - $inQty) : $orderQty;
                    
                    if (isset($products[$pid])) {
                        $p = $products[$pid];
                        $itemNetSale = $sellQty * $p['buying_price'];
                        $itemTotalSale = $sellQty * $p['price'];
                        $itemProfit = $itemTotalSale - $itemNetSale;

                        $dayOutQty += $outQty;
                        $dayOutValue += ($outQty * $p['price']);
                        $dayInQty += $inQty;
                        $dayInValue += ($inQty * $p['price']);
                        $daySellQty += $sellQty;

                        $netSale += $itemNetSale;
                        $grossSale += $itemTotalSale;
                        $grossProfit += $itemProfit;
                    }

                    $successOut += ($outQty > 0) ? $outQty : $sellQty;
                    $successSell += $sellQty;
                }

                $netProfit = $grossProfit / 2; // Dealer gets 50% of the gross profit generated
                $successRate = $successOut > 0 ? ($successSell / $successOut) * 100 : ($successSell > 0 ? 100 : 0);

                $bills[] = [
                    'date' => $date,
                    'dispatch_qty' => $dayOutQty,
                    'dispatch_value' => $dayOutValue,
                    'return_qty' => $dayInQty,
                    'return_value' => $dayInValue,
                    'sold_qty' => $daySellQty,
                    'gross_sale' => $grossSale,
                    'net_sale' => $netSale,
                    'gross_profit' => $grossProfit,
                    'net_profit' => $netProfit,
                    'success_rate' => $successRate
                ];
            }
        }

        $this->render('transactions', compact('bills'));
    }

    public function billDetails(): void
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $dealerId = $_SESSION['dealer_id'];
        $date = $_GET['date'] ?? null;

        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            return;
        }

        $srStmt = $this->db->prepare("SELECT DISTINCT sr_id FROM dealer_companies WHERE dealer_id = ?");
        $srStmt->execute([$dealerId]);
        $srIds = $srStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($srIds)) {
            echo json_encode(['success' => true, 'summary' => [], 'data' => []]);
            return;
        }

        $inStr = implode(',', $srIds);
        
        $stmt = $this->db->prepare("
            SELECT 
                p.id as product_id, 
                p.name as product_name,
                p.price as price,
                p.buying_price as buying_price,
                COALESCE(out_data.out_qty, 0) as out_qty,
                COALESCE(in_data.in_qty, 0) as in_qty,
                COALESCE(order_data.order_qty, 0) as order_qty
            FROM products p
            LEFT JOIN (
                SELECT di.product_id, SUM(di.quantity) as out_qty
                FROM dispatch_items di
                JOIN dispatches d ON di.dispatch_id = d.id
                WHERE DATE(d.dispatch_date) = ? AND d.dsr_id IN ($inStr)
                GROUP BY di.product_id
            ) out_data ON p.id = out_data.product_id
            LEFT JOIN (
                SELECT ri.product_id, SUM(ri.quantity) as in_qty
                FROM return_items ri
                JOIN returns r ON ri.return_id = r.id
                WHERE DATE(r.return_date) = ? AND r.dsr_id IN ($inStr)
                GROUP BY ri.product_id
            ) in_data ON p.id = in_data.product_id
            LEFT JOIN (
                SELECT oi.product_id, SUM(oi.quantity) as order_qty
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE DATE(o.created_at) = ? AND o.sr_id IN ($inStr)
                GROUP BY oi.product_id
            ) order_data ON p.id = order_data.product_id
            WHERE COALESCE(out_data.out_qty, 0) > 0 OR COALESCE(in_data.in_qty, 0) > 0 OR COALESCE(order_data.order_qty, 0) > 0
            ORDER BY p.name ASC
        ");
        
        $stmt->execute([$date, $date, $date]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        $totalOutQty = 0;
        $totalOutValue = 0;
        $totalInQty = 0;
        $totalInValue = 0;
        $totalSellQty = 0;
        $totalNetSale = 0;
        $totalGrossSale = 0;
        $totalGrossProfit = 0;

        foreach ($products as $p) {
            $sellQty = $p['out_qty'] > 0 ? max(0, $p['out_qty'] - $p['in_qty']) : $p['order_qty'];
            
            $successRatio = $p['out_qty'] > 0 ? ($sellQty / $p['out_qty']) * 100 : ($sellQty > 0 ? 100 : 0);
            
            $outValue = $p['out_qty'] * $p['price'];
            $inValue = $p['in_qty'] * $p['price'];
            
            $netSale = $sellQty * $p['buying_price'];
            $totalSale = $sellQty * $p['price'];
            $profit = $totalSale - $netSale;

            $totalOutQty += $p['out_qty'];
            $totalOutValue += $outValue;
            $totalInQty += $p['in_qty'];
            $totalInValue += $inValue;
            $totalSellQty += $sellQty;
            $totalNetSale += $netSale;
            $totalGrossSale += $totalSale;
            $totalGrossProfit += $profit;

            $results[] = [
                'name' => $p['product_name'],
                'out_qty' => (int)$p['out_qty'],
                'in_qty' => (int)$p['in_qty'],
                'sell_qty' => (int)$sellQty,
                'out_value' => (float)$outValue,
                'in_value' => (float)$inValue,
                'net_sale' => (float)$netSale,
                'profit' => (float)$profit,
                'total_sale' => (float)$totalSale,
                'success_ratio' => (float)$successRatio
            ];
        }

        $summary = [
            'dispatch_qty' => $totalOutQty,
            'dispatch_value' => $totalOutValue,
            'return_qty' => $totalInQty,
            'return_value' => $totalInValue,
            'sold_qty' => $totalSellQty,
            'gross_sale' => $totalGrossSale,
            'net_sale' => $totalNetSale,
            'gross_profit' => $totalGrossProfit,
            'net_profit' => $totalGrossProfit / 2,
            'success_rate' => $totalOutQty > 0 ? ($totalSellQty / $totalOutQty) * 100 : ($totalSellQty > 0 ? 100 : 0)
        ];

        echo json_encode(['success' => true, 'summary' => $summary, 'data' => $results]);
    }

    // ══════════════════════════════════════════════════════════
    //  Inventory
    // ══════════════════════════════════════════════════════════
    public function inventory(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];
        $warehouseId = $_SESSION['dealer_warehouse_id'];

        $companies = $this->getDealerCompanies($dealerId);
        $companyIds = array_column($companies, 'company_id');
        
        if (empty($companyIds)) {
            $inventory = [];
        } else {
            $companyIdsStr = implode(',', $companyIds);
            $stmt = $this->db->prepare("
                SELECT p.id, p.name, p.sku, p.price, p.buying_price, c.name as category_name, co.name as company_name, 
                       COALESCE(SUM(i.qty_boxes), 0) as qty_boxes, COALESCE(SUM(i.qty_pieces), 0) as qty_pieces
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN companies co ON p.company_id = co.id
                LEFT JOIN inventory i ON i.product_id = p.id AND i.warehouse_id = ?
                WHERE p.company_id IN ($companyIdsStr) AND p.status = 1
                GROUP BY p.id
                ORDER BY p.name ASC
            ");
            $stmt->execute([$warehouseId]);
            $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('inventory', compact('inventory'));
    }

    // ══════════════════════════════════════════════════════════
    //  Profit Report
    // ══════════════════════════════════════════════════════════
    public function profitReport(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];

        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');

        $metrics = $this->getMetricsForPeriod($dealerId, 30, $startDate, $endDate);
        $dailyData = $metrics['daily'];

        $chartData = [];
        $labels = [];
        
        if ($startDate && $endDate) {
            $curr = strtotime($startDate);
            $last = strtotime($endDate);
            while ($curr <= $last) {
                $date = date('Y-m-d', $curr);
                $labels[] = date('M j', $curr);
                $chartData[] = $dailyData[$date]['net_profit'] ?? 0;
                $curr = strtotime('+1 day', $curr);
            }
        } else {
            // Generate continuous 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M j', strtotime($date));
                $chartData[] = $dailyData[$date]['net_profit'] ?? 0;
            }
        }

        $dStmt = $this->db->prepare("SELECT warehouse_id FROM dealers WHERE id = ?");
        $dStmt->execute([$dealerId]);
        $warehouseId = $dStmt->fetchColumn() ?: 0;

        $srStmt = $this->db->prepare("SELECT DISTINCT sr_id FROM dealer_companies WHERE dealer_id = ?");
        $srStmt->execute([$dealerId]);
        $srIds = $srStmt->fetchAll(PDO::FETCH_COLUMN);

        $productPerformance = [];
        $categoryPerformance = [];
        $totalOut = 0;
        $totalIn = 0;

        if (!empty($srIds)) {
            $inStr = implode(',', $srIds);
            
            $dateCondDispatch = "";
            $dateCondReturn = "";
            $dateCondOrder = "";
            $paramsDispatch = [$dealerId, $warehouseId];
            $paramsReturn = [$dealerId];
            $paramsOrder = [$dealerId];

            if ($startDate && $endDate) {
                $dateCondDispatch = "AND DATE(d.dispatch_date) BETWEEN ? AND ?";
                $dateCondReturn = "AND DATE(r.return_date) BETWEEN ? AND ?";
                $dateCondOrder = "AND DATE(o.created_at) BETWEEN ? AND ?";
                $paramsDispatch = array_merge($paramsDispatch, [$startDate, $endDate]);
                $paramsReturn = array_merge($paramsReturn, [$startDate, $endDate]);
                $paramsOrder = array_merge($paramsOrder, [$startDate, $endDate]);
            } else {
                $dateCondDispatch = "AND d.dispatch_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $dateCondReturn = "AND r.return_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $dateCondOrder = "AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            }

            $dispatchStmt = $this->db->prepare("
                SELECT di.product_id, SUM(di.quantity) as qty
                FROM dispatch_items di
                JOIN dispatches d ON di.dispatch_id = d.id
                LEFT JOIN orders o ON d.order_id = o.id
                WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ? OR d.warehouse_id = ?) $dateCondDispatch
                GROUP BY di.product_id
            ");
            $dispatchStmt->execute($paramsDispatch);
            $dispatches = $dispatchStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $returnStmt = $this->db->prepare("
                SELECT ri.product_id, SUM(ri.quantity) as qty
                FROM return_items ri
                JOIN returns r ON ri.return_id = r.id
                LEFT JOIN dispatches d ON r.dispatch_id = d.id
                LEFT JOIN orders o ON d.order_id = o.id
                WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) $dateCondReturn
                GROUP BY ri.product_id
            ");
            $returnStmt->execute($paramsReturn);
            $returns = $returnStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $orderStmt = $this->db->prepare("
                SELECT oi.product_id, SUM(oi.quantity) as qty
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE (o.sr_id IN ($inStr) OR o.dealer_id = ?) $dateCondOrder
                GROUP BY oi.product_id
            ");
            $orderStmt->execute($paramsOrder);
            $orders = $orderStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $prodStmt = $this->db->query("
                SELECT p.id, p.name, p.price, p.buying_price, cat.name as category_name
                FROM products p
                LEFT JOIN categories cat ON p.category_id = cat.id
            ");
            
            while ($p = $prodStmt->fetch(PDO::FETCH_ASSOC)) {
                $pid = $p['id'];
                $outQty = $dispatches[$pid] ?? 0;
                $inQty = $returns[$pid] ?? 0;
                $orderQty = $orders[$pid] ?? 0;

                $dispatchQty = $outQty > 0 ? $outQty : $orderQty;
                $sellQty = max(0, $dispatchQty - $inQty);
                
                if ($sellQty > 0) {
                    $sellVal = $sellQty * $p['price'];
                    $costVal = $sellQty * $p['buying_price'];
                    $profitVal = $sellVal - $costVal;

                    $productPerformance[$p['name']] = [
                        'qty' => $sellQty,
                        'value' => $sellVal,
                        'margin' => $p['price'] > 0 ? (($p['price'] - $p['buying_price']) / $p['price']) * 100 : 0
                    ];

                    $catName = $p['category_name'] ?? 'অন্যান্য';
                    if (!isset($categoryPerformance[$catName])) {
                        $categoryPerformance[$catName] = 0;
                    }
                    $categoryPerformance[$catName] += $sellVal;
                }

                $totalOut += ($outQty > 0) ? $outQty : $sellQty;
                $totalIn += $inQty;
            }
        }

        uasort($productPerformance, function($a, $b) { return $b['qty'] <=> $a['qty']; });
        $topProductsQty = array_slice($productPerformance, 0, 10, true);

        uasort($productPerformance, function($a, $b) { return $b['value'] <=> $a['value']; });
        $topProductsVal = array_slice($productPerformance, 0, 10, true);

        uasort($productPerformance, function($a, $b) { return $b['margin'] <=> $a['margin']; });
        $topProductsMargin = array_slice($productPerformance, 0, 10, true);

        $this->render('profit-report', compact(
            'chartData', 'labels', 
            'topProductsQty', 'topProductsVal', 'topProductsMargin',
            'categoryPerformance', 'totalOut', 'totalIn'
        ));
    }
    
    // ══════════════════════════════════════════════════════════
    //  Helper
    // ══════════════════════════════════════════════════════════
    private function getDealerCompanies(int $dealerId): array
    {
        $stmt = $this->db->prepare("
            SELECT dc.company_id, c.name 
            FROM dealer_companies dc
            JOIN companies c ON dc.company_id = c.id
            WHERE dc.dealer_id = ?
        ");
        $stmt->execute([$dealerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════
    //  Tracking (SR & DSR)
    // ══════════════════════════════════════════════════════════
    public function srTracking(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];

        $srStmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.name
            FROM dealer_companies dc
            JOIN users u ON u.id = dc.sr_id
            WHERE dc.dealer_id = ? AND u.status = 1
            ORDER BY u.name ASC
        ");
        $srStmt->execute([$dealerId]);
        $srList = $srStmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'SR Tracking';
        $this->render('sr_tracking', compact('srList', 'pageTitle'), 'dealer');
    }

    public function dsrTracking(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];

        $dsrStmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.name
            FROM users u
            JOIN roles r ON r.id = u.role_id AND r.slug = 'dsr'
            WHERE u.status = 1
              AND u.id IN (
                  SELECT DISTINCT d.dsr_id 
                  FROM dispatches d 
                  JOIN orders o ON o.id = d.order_id 
                  JOIN dealer_companies dc ON dc.sr_id = o.sr_id 
                  WHERE dc.dealer_id = ? AND d.dsr_id IS NOT NULL
              )
            ORDER BY u.name ASC
        ");
        $dsrStmt->execute([$dealerId]);
        $dsrList = $dsrStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($dsrList)) {
            $dsrList = $this->db->query("
                SELECT u.id, u.name 
                FROM users u 
                JOIN roles r ON r.id = u.role_id AND r.slug = 'dsr' 
                WHERE u.status = 1 
                ORDER BY u.name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageTitle = 'DSR Tracking';
        $this->render('dsr_tracking', compact('dsrList', 'pageTitle'), 'dealer');
    }

    /** GET /dealer/api/sr-tracking/live — Live SR locations (dealer's SRs only) */
    public function apiSrTrackingLive(): void
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $dealerId = $_SESSION['dealer_id'];

        $srs = $this->db->prepare("
            SELECT u.id, u.name,
                   sl.lat, sl.lng, sl.address, sl.recorded_at,
                   TIMESTAMPDIFF(SECOND, sl.recorded_at, NOW()) AS seconds_ago
            FROM users u
            JOIN dealer_companies dc ON dc.sr_id = u.id AND dc.dealer_id = ?
            LEFT JOIN sr_locations sl ON sl.id = (
                SELECT id FROM sr_locations
                WHERE sr_id = u.id
                ORDER BY recorded_at DESC LIMIT 1
            )
            WHERE u.status = 1
            GROUP BY u.id
            ORDER BY u.name ASC
        ");
        $srs->execute([$dealerId]);
        $rows = $srs->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $sr) {
            $result[] = [
                'id'          => (int)$sr['id'],
                'name'        => $sr['name'],
                'lat'         => $sr['lat'] !== null ? (float)$sr['lat'] : null,
                'lng'         => $sr['lng'] !== null ? (float)$sr['lng'] : null,
                'address'     => $sr['address'],
                'recorded_at' => $sr['recorded_at'],
                'is_online'   => ($sr['seconds_ago'] !== null && (int)$sr['seconds_ago'] <= 300),
            ];
        }

        echo json_encode(['success' => true, 'srs' => $result, 'dsrs' => $result]);
    }

    /** GET /dealer/api/sr-tracking/history — SR location history */
    public function apiSrTrackingHistory(): void
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $dealerId = $_SESSION['dealer_id'];

        $srId    = (int)$this->get('sr_id', 0);
        $date    = $this->get('date', date('Y-m-d'));
        $timeFrom = $this->get('time_from', '00:00');
        $timeTo   = $this->get('time_to', '23:59');

        if (!$srId) {
            echo json_encode(['success' => false, 'message' => 'sr_id required']);
            return;
        }

        // Verify this SR belongs to the dealer
        $check = $this->db->prepare("SELECT 1 FROM dealer_companies WHERE dealer_id = ? AND sr_id = ? LIMIT 1");
        $check->execute([$dealerId, $srId]);
        if (!$check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $from = $date . ' ' . $timeFrom . ':00';
        $to   = $date . ' ' . $timeTo   . ':59';

        $stmt = $this->db->prepare("
            SELECT id, lat, lng, address, accuracy, recorded_at
            FROM sr_locations
            WHERE sr_id = ? AND recorded_at BETWEEN ? AND ?
            ORDER BY recorded_at ASC
        ");
        $stmt->execute([$srId, $from, $to]);
        $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'points' => $points]);
    }

    /** GET /dealer/api/dsr-tracking/live — Live DSR locations (dealer's DSRs) */
    public function apiDsrTrackingLive(): void
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $dealerId = $_SESSION['dealer_id'];

        $dsrStmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.name,
                   dl.lat, dl.lng, dl.address, dl.recorded_at,
                   TIMESTAMPDIFF(SECOND, dl.recorded_at, NOW()) AS seconds_ago
            FROM users u
            JOIN roles r ON r.id = u.role_id AND r.slug = 'dsr'
            LEFT JOIN dsr_locations dl ON dl.id = (
                SELECT id FROM dsr_locations
                WHERE dsr_id = u.id
                ORDER BY recorded_at DESC LIMIT 1
            )
            WHERE u.status = 1
              AND u.id IN (
                  SELECT DISTINCT d.dsr_id 
                  FROM dispatches d 
                  JOIN orders o ON o.id = d.order_id 
                  JOIN dealer_companies dc ON dc.sr_id = o.sr_id 
                  WHERE dc.dealer_id = ? AND d.dsr_id IS NOT NULL
              )
            ORDER BY u.name ASC
        ");
        $dsrStmt->execute([$dealerId]);
        $rows = $dsrStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            $rows = $this->db->query("
                SELECT u.id, u.name,
                       dl.lat, dl.lng, dl.address, dl.recorded_at,
                       TIMESTAMPDIFF(SECOND, dl.recorded_at, NOW()) AS seconds_ago
                FROM users u
                JOIN roles r ON r.id = u.role_id AND r.slug = 'dsr'
                LEFT JOIN dsr_locations dl ON dl.id = (
                    SELECT id FROM dsr_locations
                    WHERE dsr_id = u.id
                    ORDER BY recorded_at DESC LIMIT 1
                )
                WHERE u.status = 1
                ORDER BY u.name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        $result = [];
        foreach ($rows as $dsr) {
            $result[] = [
                'id'          => (int)$dsr['id'],
                'name'        => $dsr['name'],
                'lat'         => $dsr['lat'] !== null ? (float)$dsr['lat'] : null,
                'lng'         => $dsr['lng'] !== null ? (float)$dsr['lng'] : null,
                'address'     => $dsr['address'],
                'recorded_at' => $dsr['recorded_at'],
                'is_online'   => ($dsr['seconds_ago'] !== null && (int)$dsr['seconds_ago'] <= 300),
            ];
        }

        echo json_encode(['success' => true, 'dsrs' => $result, 'srs' => $result]);
    }

    /** GET /dealer/api/dsr-tracking/history — DSR location history */
    public function apiDsrTrackingHistory(): void
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $dsrId    = (int)$this->get('dsr_id', 0);
        $date     = $this->get('date', date('Y-m-d'));
        $timeFrom = $this->get('time_from', '00:00');
        $timeTo   = $this->get('time_to', '23:59');

        if (!$dsrId) {
            echo json_encode(['success' => false, 'message' => 'dsr_id required']);
            return;
        }

        $from = $date . ' ' . $timeFrom . ':00';
        $to   = $date . ' ' . $timeTo   . ':59';

        $stmt = $this->db->prepare("
            SELECT id, lat, lng, address, accuracy, recorded_at
            FROM dsr_locations
            WHERE dsr_id = ? AND recorded_at BETWEEN ? AND ?
            ORDER BY recorded_at ASC
        ");
        $stmt->execute([$dsrId, $from, $to]);
        $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'points' => $points]);
    }
}
