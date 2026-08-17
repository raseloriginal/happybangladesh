<?php
/**
 * ManagerController — handles manager panel pages
 */
class ManagerController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

    // ══════════════════════════════════════════════════════════
    //  Dashboard
    // ══════════════════════════════════════════════════════════
    public function dashboard(): void
    {
        $wId = Auth::warehouseId();
        $stats = [
            'total_products'  => $this->db->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn(),
            'total_lots'      => $this->db->query("SELECT COUNT(*) FROM lots")->fetchColumn(),
            'total_inventory' => $this->db->prepare("SELECT COALESCE(SUM(qty_boxes),0) FROM inventory WHERE warehouse_id=?")->execute([$wId]) ? $this->db->query("SELECT COALESCE(SUM(qty_boxes),0) FROM inventory WHERE warehouse_id=" . (int)$wId)->fetchColumn() : 0,
            'pending_dispatch'=> $this->db->query("SELECT COUNT(*) FROM dispatches WHERE status='pending'")->fetchColumn(),
            'pending_returns' => $this->db->query("SELECT COUNT(*) FROM returns WHERE status='pending'")->fetchColumn(),
            'today_attendance'=> $this->db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE()")->fetchColumn(),
            'total_readysale' => $this->db->query("SELECT COUNT(*) FROM readysales WHERE status=1")->fetchColumn(),
        ];

        $recentProducts = $this->db->query("SELECT p.*, c.name AS company_name FROM products p LEFT JOIN companies c ON c.id=p.company_id ORDER BY p.created_at DESC LIMIT 6")->fetchAll();

        $this->render('dashboard', compact('stats', 'recentProducts'));
    }

    // ══════════════════════════════════════════════════════════
    //  Orders (Nested drill-down)
    // ══════════════════════════════════════════════════════════
    public function orders(): void
    {
        $limit = 15;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $where = " WHERE 1=1 ";
        $params = [];
        if (!empty($dateFrom)) {
            $where .= " AND DATE(o.created_at) >= ? ";
            $params[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(o.created_at) <= ? ";
            $params[] = $dateTo;
        }

        $qCount = $this->db->prepare("SELECT COUNT(DISTINCT DATE(o.created_at)) FROM orders o $where");
        $qCount->execute($params);
        $totalDates = $qCount->fetchColumn();
        $totalPages = ceil($totalDates / $limit);

        $q = $this->db->prepare("
            SELECT DATE(o.created_at) as order_date,
                   SUM(oi.quantity * (oi.buying_price * (1 + COALESCE(p.dealer_percentage, 0) / 100))) as total_base_value,
                   SUM(oi.total_price) as total_sr_value
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            $where
            GROUP BY DATE(o.created_at)
            ORDER BY DATE(o.created_at) DESC
            LIMIT $limit OFFSET $offset
        ");
        $q->execute($params);
        $orderDates = $q->fetchAll(PDO::FETCH_ASSOC);

        $this->render('orders/index', compact('orderDates', 'page', 'totalPages', 'dateFrom', 'dateTo'));
    }

    public function apiOrdersCompanies(): void
    {
        header('Content-Type: application/json');
        $date = $_GET['date'] ?? '';
        if (!$date) { echo json_encode([]); exit; }

        $q = $this->db->prepare("
            SELECT c.id as company_id,
                   c.name as company_name,
                   SUM(oi.quantity * (oi.buying_price * (1 + COALESCE(p.dealer_percentage, 0) / 100))) as total_base_value,
                   SUM(oi.total_price) as total_sr_value
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN companies c ON c.id = p.company_id
            WHERE DATE(o.created_at) = ?
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $q->execute([$date]);
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    public function apiOrdersSrs(): void
    {
        header('Content-Type: application/json');
        $date = $_GET['date'] ?? '';
        $companyId = $_GET['company_id'] ?? '';
        if (!$date || $companyId === '') { echo json_encode([]); exit; }

        $q = $this->db->prepare("
            SELECT u.id as sr_id,
                   u.name as sr_name,
                   SUM(oi.quantity * (oi.buying_price * (1 + COALESCE(p.dealer_percentage, 0) / 100))) as total_base_value,
                   SUM(oi.total_price) as total_sr_value,
                   SUM(oi.total_price - (oi.quantity * p.price)) as total_oc
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            JOIN users u ON u.id = o.sr_id
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE DATE(o.created_at) = ? AND (p.company_id = ? OR (? = 0 AND p.company_id IS NULL))
            GROUP BY u.id
            ORDER BY u.name ASC
        ");
        $q->execute([$date, $companyId, $companyId]);
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    public function apiOrdersProducts(): void
    {
        header('Content-Type: application/json');
        $date = $_GET['date'] ?? '';
        $companyId = $_GET['company_id'] ?? '';
        $srId = $_GET['sr_id'] ?? '';
        if (!$date || $companyId === '' || !$srId) { echo json_encode([]); exit; }
        
        $wid = Auth::warehouseId();

        $q = $this->db->prepare("
            SELECT p.id as product_id,
                   p.name as product_name,
                   p.pieces_per_box,
                   p.box_type,
                   SUM(oi.quantity) as total_qty,
                   SUM(oi.quantity * (oi.buying_price * (1 + COALESCE(p.dealer_percentage, 0) / 100))) as total_base_value,
                   SUM(oi.total_price) as total_sr_value
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            WHERE DATE(o.created_at) = ? AND (p.company_id = ? OR (? = 0 AND p.company_id IS NULL)) AND o.sr_id = ?
            GROUP BY p.id
            ORDER BY p.name ASC
        ");
        $q->execute([$date, $companyId, $companyId, $srId]);
        $products = $q->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($products)) {
            $pIds = array_column($products, 'product_id');
            $inClause = implode(',', array_fill(0, count($pIds), '?'));
            $invParams = array_merge([$wid], $pIds);
            
            $qInv = $this->db->prepare("
                SELECT p.id as product_id, 
                       (
                           COALESCE((SELECT SUM(qty_boxes * p.pieces_per_box + qty_pieces) FROM lots WHERE product_id = p.id), 0)
                           -
                           COALESCE((SELECT SUM(quantity) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled'), 0)
                           +
                           COALESCE((SELECT SUM(quantity) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0)
                       ) as stock_pieces, 
                       0 as stock_boxes
                FROM products p 
                WHERE p.id IN ($inClause)
            ");
            $qInv->execute($pIds);
            $stockData = [];
            while ($row = $qInv->fetch(PDO::FETCH_ASSOC)) {
                $stockData[$row['product_id']] = $row;
            }

            foreach ($products as &$p) {
                $pId = $p['product_id'];
                $p['stock_pieces'] = $stockData[$pId]['stock_pieces'] ?? 0;
                $p['stock_boxes'] = $stockData[$pId]['stock_boxes'] ?? 0;
            }
        }
        
        echo json_encode($products);
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Products CRUD
    // ══════════════════════════════════════════════════════════
    public function products(): void
    {
        $wid = Auth::warehouseId();
        $items = $this->db->query("
            SELECT p.*, c.name AS company_name, cat.name AS category_name,
                   (
                       COALESCE((SELECT SUM(qty_boxes * pieces_per_box + qty_pieces) FROM lots WHERE product_id = p.id), 0)
                       -
                       COALESCE((SELECT SUM(quantity) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled'), 0)
                       +
                       COALESCE((SELECT SUM(quantity) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0)
                   ) AS stock_pieces,
                   0 AS stock_boxes
            FROM products p
            LEFT JOIN companies c ON c.id = p.company_id
            LEFT JOIN categories cat ON cat.id = p.category_id
            WHERE p.status=1
            ORDER BY p.created_at DESC
        ")->fetchAll();
        $companies = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $categories = $this->db->query("
            SELECT c.*, mc.name as main_category_name 
            FROM categories c 
            LEFT JOIN main_categories mc ON mc.id = c.main_category_id 
            WHERE c.status=1 
            ORDER BY COALESCE(mc.name, 'zzz'), c.name
        ")->fetchAll();
        $this->render('products/index', compact('items', 'companies', 'categories'));
    }

    public function apiProductStore(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        // Support both JSON and FormData (multipart)
        $isJson = (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $items = $input['items'] ?? [];
        } else {
            $input = $_POST;
            $items = json_decode($_POST['items'] ?? '[]', true) ?? [];
        }

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'No products to save']);
            exit;
        }

        $uploadDir = PUB_PATH . '/assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $debugLogPath = $uploadDir . 'image_download_debug.log';
        @file_put_contents($debugLogPath, "[" . date('Y-m-d H:i:s') . "] apiProductStore received items: " . json_encode($items) . "\n", FILE_APPEND);

        // Build an ordered list of row indices to pair items with uploaded files
        $rowIndices = isset($_POST['row_indices']) ? (array)$_POST['row_indices'] : [];

        $this->db->beginTransaction();
        try {
            foreach ($items as $i => $p) {
                // Handle image upload for this row
                $imagePath = null;
                $rowIdx = $rowIndices[$i] ?? null;
                if ($rowIdx !== null && !empty($_FILES['images']['tmp_name'][$rowIdx])) {
                    $ext = strtolower(pathinfo($_FILES['images']['name'][$rowIdx], PATHINFO_EXTENSION));
                    $tmpName = $_FILES['images']['tmp_name'][$rowIdx];
                    
                    $filename = 'prod_' . uniqid() . '.webp';
                    if ($this->convertToWebp($tmpName, $uploadDir . $filename)) {
                        $imagePath = 'assets/uploads/' . $filename;
                    } else {
                        // Fallback
                        $filename = 'prod_' . uniqid() . '.' . ($ext ?: 'jpg');
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            $imagePath = 'assets/uploads/' . $filename;
                        }
                    }
                } elseif (!empty($p['image_url'])) {
                    $imagePath = $this->saveProductImageFromUrlOrData($p['image_url'], $uploadDir);
                }
                
                $piecesPerBox = (int)($p['pieces_per_box'] ?: 1);
                $buyingPrice = 0;
                if (!empty($p['price_piece'])) {
                    $buyingPrice = (float)$p['price_piece'] * $piecesPerBox;
                }

                $sku = 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $this->db->prepare("INSERT INTO products (company_id, category_id, name, sku, box_type, pieces_per_box, dealer_percentage, buying_price, image) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([
                        $input['company_id'] ?: null,
                        $p['category_id'] ?: null,
                        trim($p['name']),
                        $sku,
                        $p['box_type'] ?: 'পিস',
                        $piecesPerBox,
                        $p['dealer_percentage'] ?: 0,
                        $buyingPrice,
                        $imagePath
                    ]);
            }
            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiProductUpdate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $id = $_POST['id'] ?? null;
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Missing ID']); exit; }

        try {
            $image = null;
            if (!empty($_FILES['image']['tmp_name'])) {
                $uploadDir = PUB_PATH . '/assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $tmpName = $_FILES['image']['tmp_name'];
                
                $filename = 'prod_' . uniqid() . '.webp';
                if ($this->convertToWebp($tmpName, $uploadDir . $filename)) {
                    $image = 'assets/uploads/' . $filename;
                } else {
                    // Fallback to direct move if conversion fails
                    $filename = 'prod_' . uniqid() . '.' . ($ext ?: 'jpg');
                    if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                        $image = 'assets/uploads/' . $filename;
                    }
                }
            }

            $query = "UPDATE products SET company_id=?, category_id=?, name=?, box_type=?, pieces_per_box=?, dealer_percentage=?";
            $params = [
                $_POST['company_id'] ?: null,
                $_POST['category_id'] ?: null,
                trim($_POST['name']),
                $_POST['box_type'],
                $_POST['pieces_per_box'],
                $_POST['dealer_percentage']
            ];

            if ($image) {
                $query .= ", image=?";
                $params[] = $image;
            }

            $query .= " WHERE id=?";
            $params[] = $id;

            $this->db->prepare($query)->execute($params);
            \Helpers::logManagerActivity(\Auth::id(), 'edit_product', 'Edited product: ' . trim($_POST['name']), $id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiProductDelete(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if ($id = $input['id'] ?? null) {
            $this->db->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            \Helpers::logManagerActivity(\Auth::id(), 'delete_product', 'Deleted product ID: ' . $id, $id);
            echo json_encode(['success' => true]);
        }
        exit;
    }

    public function apiAdjustBuyingPrice(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $pid = (int)($input['product_id'] ?? $input['id'] ?? 0);
        $new_buying_price = (float)($input['buying_price'] ?? 0);

        if (!$pid) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT name, pieces_per_box, dealer_percentage FROM products WHERE id=?");
            $stmt->execute([$pid]);
            $p = $stmt->fetch();

            if (!$p) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }

            $ppb = max(1, (float)$p['pieces_per_box']);
            $dp  = (float)$p['dealer_percentage'];
            
            // Calculate new selling price per piece
            $selling_price = round($new_buying_price * (1 + $dp / 100) / $ppb, 2);

            $this->db->prepare("UPDATE products SET buying_price=?, price=? WHERE id=?")
                     ->execute([$new_buying_price, $selling_price, $pid]);

            \Helpers::logManagerActivity(
                \Auth::id(), 
                'adjust_buying_price', 
                "Adjusted buying price for {$p['name']} to ৳{$new_buying_price}", 
                $pid
            );

            echo json_encode(['success' => true, 'new_buying_price' => $new_buying_price, 'new_selling_price' => $selling_price]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiStockAdjust(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        $pid = $input['product_id'] ?? null;
        if (!$pid) exit;

        $wid = Auth::warehouseId();
        
        $this->db->beginTransaction();
        try {
            // Update product buying price if provided
            if (isset($input['buying_price'])) {
                $this->db->prepare("UPDATE products SET buying_price=? WHERE id=?")
                         ->execute([$input['buying_price'], $pid]);
            }

            // Upsert inventory
            $exists = $this->db->prepare("SELECT id FROM inventory WHERE product_id=? AND warehouse_id=?");
            $exists->execute([$pid, $wid]);
            
            if ($exists->fetch()) {
                $this->db->prepare("UPDATE inventory SET qty_boxes=?, qty_pieces=? WHERE product_id=? AND warehouse_id=?")
                         ->execute([$input['new_boxes'] ?? 0, $input['new_pieces'] ?? 0, $pid, $wid]);
            } else {
                $this->db->prepare("INSERT INTO inventory (warehouse_id, product_id, qty_boxes, qty_pieces) VALUES (?,?,?,?)")
                         ->execute([$wid, $pid, $input['new_boxes'] ?? 0, $input['new_pieces'] ?? 0]);
            }
            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Categories
    // ══════════════════════════════════════════════════════════
    public function categories(): void
    {
        $items = $this->db->query("
            SELECT c.*, co.name as company_name, mc.name as main_category_name 
            FROM categories c 
            LEFT JOIN companies co ON co.id=c.company_id 
            LEFT JOIN main_categories mc ON mc.id=c.main_category_id
            WHERE c.status=1 
            ORDER BY c.id DESC
        ")->fetchAll();
        $companies = $this->db->query('SELECT id, name FROM companies WHERE status=1 ORDER BY name')->fetchAll();
        $main_categories = $this->db->query('SELECT id, name FROM main_categories ORDER BY name')->fetchAll();
        $this->render('categories/index', compact('items', 'companies', 'main_categories'));
    }

    public function apiCategoryStore(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['names'])) exit;
        
        $cid = $input['company_id'] ?: null;
        $mcid = $input['main_category_id'] ?: null;
        
        if ($mcid === 'new' && !empty($input['new_main_category_name'])) {
            $stmt = $this->db->prepare("SELECT id FROM main_categories WHERE name = ?");
            $stmt->execute([trim($input['new_main_category_name'])]);
            $mcid = $stmt->fetchColumn();
            if (!$mcid) {
                $stmt = $this->db->prepare("INSERT INTO main_categories (name) VALUES (?)");
                $stmt->execute([trim($input['new_main_category_name'])]);
                $mcid = $this->db->lastInsertId();
            }
        }
        
        $stmt = $this->db->prepare("INSERT INTO categories (company_id, main_category_id, name) VALUES (?, ?, ?)");
        
        foreach ($input['names'] as $name) {
            $stmt->execute([$cid, $mcid ?: null, $name]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function apiCategoryUpdate(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && !empty($input['id'])) {
            $mcid = $input['main_category_id'] ?: null;
            if ($mcid === 'new' && !empty($input['new_main_category_name'])) {
                $stmt = $this->db->prepare("SELECT id FROM main_categories WHERE name = ?");
                $stmt->execute([trim($input['new_main_category_name'])]);
                $mcid = $stmt->fetchColumn();
                if (!$mcid) {
                    $stmt = $this->db->prepare("INSERT INTO main_categories (name) VALUES (?)");
                    $stmt->execute([trim($input['new_main_category_name'])]);
                    $mcid = $this->db->lastInsertId();
                }
            }
            $this->db->prepare("UPDATE categories SET company_id=?, main_category_id=?, name=? WHERE id=?")
                     ->execute([$input['company_id'] ?: null, $mcid ?: null, trim($input['name']), $input['id']]);
            echo json_encode(['success' => true]);
        }
        exit;
    }

    public function apiCategoryDelete(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && !empty($input['id'])) {
            $this->db->prepare("DELETE FROM categories WHERE id=?")->execute([$input['id']]);
            echo json_encode(['success' => true]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Lots CRUD
    // ══════════════════════════════════════════════════════════
    public function lots(): void
    {
        $rawLots = $this->db->query("
            SELECT l.*, p.name AS product_name, p.pieces_per_box, p.box_type, p.sku, p.company_id, c.name AS company_name
            FROM lots l 
            JOIN products p ON p.id = l.product_id
            LEFT JOIN companies c ON c.id = p.company_id
            ORDER BY COALESCE(l.lot_date, DATE(l.created_at)) DESC, l.id DESC
        ")->fetchAll();

        $batches = [];
        foreach ($rawLots as $lot) {
            $lotDate = !empty($lot['lot_date']) ? $lot['lot_date'] : date('Y-m-d', strtotime($lot['created_at']));
            $compId  = $lot['company_id'] ?? 0;
            $compKey = $compId . '_' . $lotDate;

            if (!isset($batches[$compKey])) {
                $batches[$compKey] = [
                    'company_id'   => $compId,
                    'company_name' => $lot['company_name'] ?: 'Unknown Company',
                    'lot_date'     => $lotDate,
                    'min_lot_id'   => $lot['id'],
                    'items_count'  => 0,
                    'total_amount' => 0,
                    'items'        => []
                ];
            }
            $ppb = max(1, (float)($lot['pieces_per_box'] ?? 1));
            $unitPrice = (float)$lot['buying_price'] / $ppb;
            $rowTotal  = ((float)$lot['qty_pieces'] / $ppb) * (float)$lot['buying_price'];
            
            $lot['unit_price'] = $unitPrice;
            $lot['row_total']  = $rowTotal;
            
            $batches[$compKey]['items_count']++;
            $batches[$compKey]['total_amount'] += $rowTotal;
            $batches[$compKey]['items'][] = $lot;
        }
        $batches = array_values($batches);

        $products = $this->db->query("
            SELECT p.id, p.name, p.sku, p.company_id, p.image, p.pieces_per_box, p.box_type, p.buying_price,
                   COALESCE(SUM(i.qty_boxes), 0) AS stock_boxes,
                   COALESCE(SUM(i.qty_pieces), 0) AS stock_pieces
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id AND i.warehouse_id = " . (int)Auth::warehouseId() . "
            WHERE p.status=1
            GROUP BY p.id
            ORDER BY p.name
        ")->fetchAll();
        $companies = $this->db->query("SELECT id, name FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('lots/index', compact('batches', 'products', 'companies'));
    }

    public function apiLotStore(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['lots'])) {
            echo json_encode(['success' => false, 'message' => 'No lots provided']); exit;
        }

        $lot_date = !empty($input['lot_date']) ? $input['lot_date'] : date('Y-m-d');
        $wid = Auth::warehouseId() ?: ($this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1);

        $this->db->beginTransaction();
        try {
            $lotStmt = $this->db->prepare(
                "INSERT INTO lots (product_id, lot_date, expiry_date, qty_boxes, buying_price, lot_number, qty_pieces) VALUES (?,?,?,0,?,NULL,?)"
            );

            foreach ($input['lots'] as $lot) {
                $product_id   = (int)($lot['product_id'] ?? 0);
                $qty_pieces   = (int)($lot['qty_pieces'] ?? 0);
                $buying_price = (float)($lot['buying_price'] ?? 0);
                $expiry_date  = $lot['expiry_date'] ?: null;

                if (!$product_id) continue;

                // 1. Insert lot row
                $lotStmt->execute([$product_id, $lot_date, $expiry_date, $buying_price, $qty_pieces]);
                $lot_id = $this->db->lastInsertId();

                // 2. Upsert inventory — each lot gets its own row (unique: warehouse+product+lot)
                $this->db->prepare(
                    "INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces)
                     VALUES (?,?,?,0,?)
                     ON DUPLICATE KEY UPDATE qty_pieces = qty_pieces + VALUES(qty_pieces)"
                )->execute([$wid, $product_id, $lot_id, $qty_pieces]);

                // 3. Auto-update product buying_price and calculate selling price
                $prod = $this->db->prepare("SELECT pieces_per_box, dealer_percentage FROM products WHERE id=?");
                $prod->execute([$product_id]);
                $p = $prod->fetch();

                if ($p) {
                    $ppb = max(1, (float)$p['pieces_per_box']);
                    $dp  = (float)$p['dealer_percentage'];
                    // selling price per piece = buying_price_per_box * (1 + dealer%) / pieces_per_box
                    $selling_price = round($buying_price * (1 + $dp / 100) / $ppb, 2);

                    $this->db->prepare(
                        "UPDATE products SET buying_price=?, price=? WHERE id=?"
                    )->execute([$buying_price, $selling_price, $product_id]);
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Lot saved and inventory updated']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLotUpdate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']); exit;
        }

        $wid = Auth::warehouseId() ?: ($this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1);
        $this->db->beginTransaction();
        try {
            // 1. Fetch old lot to revert its inventory contribution
            $old = $this->db->prepare("SELECT product_id, qty_pieces FROM lots WHERE id=?");
            $old->execute([$input['id']]);
            $oldLot = $old->fetch();

            if ($oldLot) {
                $this->db->prepare(
                    "UPDATE inventory SET qty_pieces = GREATEST(0, qty_pieces - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$oldLot['qty_pieces'], $oldLot['product_id'], $wid, $input['id']]);
            }

            // 2. Update the lot row
            $this->db->prepare(
                "UPDATE lots SET product_id=?, expiry_date=?, qty_pieces=?, buying_price=?, lot_date=?, lot_number=?, manufacturing_date=?, notes=? WHERE id=?"
            )->execute([
                $input['product_id'],
                $input['expiry_date'] ?: null,
                $input['qty_pieces'] ?? 0,
                $input['buying_price'] ?? 0,
                $input['lot_date'] ?? date('Y-m-d'),
                $input['lot_number'] ?? null,
                $input['manufacturing_date'] ?: null,
                $input['notes'] ?? null,
                $input['id']
            ]);

            // 3. Re-apply inventory
            $new_qty   = (int)($input['qty_pieces'] ?? 0);
            $new_price = (float)($input['buying_price'] ?? 0);
            $pid       = (int)$input['product_id'];

            $this->db->prepare(
                "INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces)
                 VALUES (?,?,?,0,?)
                 ON DUPLICATE KEY UPDATE qty_pieces = qty_pieces + VALUES(qty_pieces)"
            )->execute([$wid, $pid, $input['id'], $new_qty]);

            // 4. Recalculate selling price
            $prod = $this->db->prepare("SELECT pieces_per_box, dealer_percentage FROM products WHERE id=?");
            $prod->execute([$pid]);
            $p = $prod->fetch();
            if ($p) {
                $ppb = max(1, (float)$p['pieces_per_box']);
                $dp  = (float)$p['dealer_percentage'];
                $selling_price = round($new_price * (1 + $dp / 100) / $ppb, 2);
                $this->db->prepare("UPDATE products SET buying_price=?, price=? WHERE id=?")
                         ->execute([$new_price, $selling_price, $pid]);
            }

            $this->db->commit();
            \Helpers::logManagerActivity(\Auth::id(), 'edit_lot', 'Edited lot ID: ' . $input['id'], $input['id']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLotDelete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']); exit;
        }

        $wid = Auth::warehouseId() ?: ($this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1);
        $this->db->beginTransaction();
        try {
            // Get lot data before deleting
            $lot = $this->db->prepare("SELECT product_id, qty_pieces FROM lots WHERE id=?");
            $lot->execute([$input['id']]);
            $lotData = $lot->fetch();

            if ($lotData) {
                // Reduce inventory, cap at 0
                $this->db->prepare(
                    "UPDATE inventory SET qty_pieces = GREATEST(0, qty_pieces - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$lotData['qty_pieces'], $lotData['product_id'], $wid, $input['id']]);
            }

            $this->db->prepare("DELETE FROM lots WHERE id=?")->execute([$input['id']]);

            $this->db->commit();
            \Helpers::logManagerActivity(\Auth::id(), 'delete_lot', 'Deleted lot ID: ' . $input['id'], $input['id']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLotBatchDelete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);
        if (empty($input['company_id']) || empty($input['lot_date'])) {
            echo json_encode(['success' => false, 'message' => 'Missing company or date']); exit;
        }

        $company_id = (int)$input['company_id'];
        $lot_date   = $input['lot_date'];
        $wid        = Auth::warehouseId() ?: ($this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1);

        $this->db->beginTransaction();
        try {
            $lots = $this->db->prepare("
                SELECT l.id, l.product_id, l.qty_pieces 
                FROM lots l
                JOIN products p ON p.id = l.product_id
                WHERE p.company_id = ? AND (l.lot_date = ? OR (l.lot_date IS NULL AND DATE(l.created_at) = ?))
            ");
            $lots->execute([$company_id, $lot_date, $lot_date]);
            $lotRows = $lots->fetchAll();

            foreach ($lotRows as $lRow) {
                $this->db->prepare(
                    "UPDATE inventory SET qty_pieces = GREATEST(0, qty_pieces - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$lRow['qty_pieces'], $lRow['product_id'], $wid, $lRow['id']]);

                $this->db->prepare("DELETE FROM lots WHERE id=?")->execute([$lRow['id']]);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Batch lots deleted successfully']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLotBatchUpdate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['lots'])) {
            echo json_encode(['success' => false, 'message' => 'No lots provided']); exit;
        }

        $orig_company_id = !empty($input['original_company_id']) ? (int)$input['original_company_id'] : (int)($input['company_id'] ?? 0);
        $orig_lot_date   = !empty($input['original_lot_date']) ? $input['original_lot_date'] : ($input['lot_date'] ?? date('Y-m-d'));
        
        $new_company_id  = (int)($input['company_id'] ?? $orig_company_id);
        $new_lot_date    = !empty($input['lot_date']) ? $input['lot_date'] : $orig_lot_date;

        $wid = Auth::warehouseId() ?: ($this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1);

        $this->db->beginTransaction();
        try {
            // 1. Fetch all old lots in this batch to revert inventory
            $oldLots = $this->db->prepare("
                SELECT l.id, l.product_id, l.qty_pieces 
                FROM lots l
                JOIN products p ON p.id = l.product_id
                WHERE p.company_id = ? AND (l.lot_date = ? OR (l.lot_date IS NULL AND DATE(l.created_at) = ?))
            ");
            $oldLots->execute([$orig_company_id, $orig_lot_date, $orig_lot_date]);
            $oldRows = $oldLots->fetchAll();

            foreach ($oldRows as $o) {
                $this->db->prepare(
                    "UPDATE inventory SET qty_pieces = GREATEST(0, qty_pieces - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$o['qty_pieces'], $o['product_id'], $wid, $o['id']]);

                $this->db->prepare("DELETE FROM lots WHERE id=?")->execute([$o['id']]);
            }

            // 2. Insert new lots
            $lotStmt = $this->db->prepare(
                "INSERT INTO lots (product_id, lot_date, expiry_date, qty_boxes, buying_price, lot_number, qty_pieces) VALUES (?,?,?,0,?,NULL,?)"
            );

            foreach ($input['lots'] as $lot) {
                $product_id   = (int)($lot['product_id'] ?? 0);
                $qty_pieces   = (int)($lot['qty_pieces'] ?? 0);
                $buying_price = (float)($lot['buying_price'] ?? 0);
                $expiry_date  = $lot['expiry_date'] ?: null;

                if (!$product_id) continue;

                $lotStmt->execute([$product_id, $new_lot_date, $expiry_date, $buying_price, $qty_pieces]);
                $lot_id = $this->db->lastInsertId();

                $this->db->prepare(
                    "INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces)
                     VALUES (?,?,?,0,?)
                     ON DUPLICATE KEY UPDATE qty_pieces = qty_pieces + VALUES(qty_pieces)"
                )->execute([$wid, $product_id, $lot_id, $qty_pieces]);

                // Update product buying_price and selling price
                $prod = $this->db->prepare("SELECT pieces_per_box, dealer_percentage FROM products WHERE id=?");
                $prod->execute([$product_id]);
                $p = $prod->fetch();

                if ($p) {
                    $ppb = max(1, (float)$p['pieces_per_box']);
                    $dp  = (float)$p['dealer_percentage'];
                    $selling_price = round($buying_price * (1 + $dp / 100) / $ppb, 2);

                    $this->db->prepare(
                        "UPDATE products SET buying_price=?, price=? WHERE id=?"
                    )->execute([$buying_price, $selling_price, $product_id]);
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Lot batch updated successfully']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Submit a lot-batch edit request for admin approval.
     * The actual DB update happens when admin approves via AdminController::approvalApprove().
     */
    public function apiLotBatchEditRequest(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = $GLOBALS['_PARSED_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['lots'])) {
            echo json_encode(['success' => false, 'message' => 'No lots provided']); exit;
        }

        $orig_company_id = !empty($input['original_company_id']) ? (int)$input['original_company_id'] : (int)($input['company_id'] ?? 0);
        $orig_lot_date   = !empty($input['original_lot_date'])   ? $input['original_lot_date']   : ($input['lot_date'] ?? date('Y-m-d'));
        $new_company_id  = (int)($input['company_id'] ?? $orig_company_id);
        $new_lot_date    = !empty($input['lot_date']) ? $input['lot_date'] : $orig_lot_date;

        // Fetch old lot data for the approval record
        $oldLots = $this->db->prepare("
            SELECT l.id, l.product_id, l.qty_pieces, l.buying_price, l.expiry_date, l.lot_date,
                   p.name AS product_name, c.name AS company_name
            FROM lots l
            JOIN products p ON p.id = l.product_id
            LEFT JOIN companies c ON c.id = p.company_id
            WHERE p.company_id = ? AND (l.lot_date = ? OR (l.lot_date IS NULL AND DATE(l.created_at) = ?))
        ");
        $oldLots->execute([$orig_company_id, $orig_lot_date, $orig_lot_date]);
        $oldData = $oldLots->fetchAll();

        if (empty($oldData)) {
            echo json_encode(['success' => false, 'message' => 'Original lot batch not found']); exit;
        }

        // Store approval request — record_id is the original company_id (batch key)
        $this->db->prepare("
            INSERT INTO approvals (requested_by, module, action, record_id, old_data, new_data, status)
            VALUES (?, 'lots_batch', 'edit', ?, ?, ?, 'pending')
        ")->execute([
            \Auth::id(),
            $orig_company_id,
            json_encode([
                'company_id'  => $orig_company_id,
                'lot_date'    => $orig_lot_date,
                'lots'        => $oldData,
            ]),
            json_encode([
                'original_company_id' => $orig_company_id,
                'original_lot_date'   => $orig_lot_date,
                'company_id'          => $new_company_id,
                'lot_date'            => $new_lot_date,
                'lots'                => $input['lots'],
            ]),
        ]);

        \Helpers::logManagerActivity(\Auth::id(), 'request_lot_edit', "Requested edit approval for lot batch: company_id={$orig_company_id}, date={$orig_lot_date}", $orig_company_id);
        echo json_encode(['success' => true, 'message' => 'Edit request submitted. Waiting for admin approval.']);
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Inventory (view only)
    // ══════════════════════════════════════════════════════════
    public function inventory(): void
    {
        $items = $this->db->query("
            SELECT p.id AS product_id, p.name AS product_name, p.sku, 
                   'All Warehouses' AS warehouse_name, 
                   '-' AS lot_number,
                   0 AS qty_boxes,
                   (
                       COALESCE((SELECT SUM(qty_boxes * pieces_per_box + qty_pieces) FROM lots WHERE product_id = p.id), 0)
                       -
                       COALESCE((SELECT SUM(quantity) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled'), 0)
                       +
                       COALESCE((SELECT SUM(quantity) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0)
                   ) AS qty_pieces
            FROM products p
            WHERE p.status=1
            ORDER BY p.name
        ")->fetchAll();
        $this->render('inventory', compact('items'));
    }

    // ══════════════════════════════════════════════════════════
    //  Dispatch
    // ══════════════════════════════════════════════════════════
    public function dispatch(): void
    {
        $this->render('dispatch');
    }

    public function apiDispatchData(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $schedules = $this->db->query("
            SELECT ds.*, u.name AS dsr_name 
            FROM dispatch_schedules ds
            JOIN users u ON u.id = ds.dsr_id
            ORDER BY ds.dispatch_date DESC, ds.created_at DESC
        ")->fetchAll();

        foreach ($schedules as &$sch) {
            $sid = $sch['id'];
            $delivery_date = $sch['delivery_date'] ?: $sch['dispatch_date'];
            
            $orderVal = $this->db->query("
                SELECT COALESCE(SUM(o.total_amount), 0)
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$sch['dispatch_date']}'
                WHERE dss.schedule_id = $sid
            ")->fetchColumn();
            
            $sch['total_order_value'] = (float)$orderVal;

            $orderOC = $this->db->query("
                SELECT COALESCE(SUM((oi.unit_price - oi.buying_price) * oi.quantity), 0)
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$sch['dispatch_date']}'
                JOIN order_items oi ON oi.order_id = o.id
                WHERE dss.schedule_id = $sid
            ")->fetchColumn();
            $sch['total_order_oc'] = (float)$orderOC;

            // Use dispatch_items as actual dispatched value
            $dispatchValStmt = $this->db->prepare("
                SELECT COALESCE(SUM(di.quantity * di.unit_price), 0)
                FROM dispatches d
                JOIN dispatch_items di ON di.dispatch_id = d.id
                WHERE d.dsr_id = ? AND d.dispatch_date = ?
            ");
            $dispatchValStmt->execute([$sch['dsr_id'], $delivery_date]);
            $sch['total_dispatch_value'] = (float)$dispatchValStmt->fetchColumn();

            $dispatchOC = $this->db->query("
                SELECT COALESCE(SUM(di.quantity * (di.unit_price - di.buying_price)), 0)
                FROM dispatches d
                JOIN dispatch_items di ON di.dispatch_id = d.id
                WHERE d.dsr_id = {$sch['dsr_id']} AND d.dispatch_date = '{$delivery_date}'
            ")->fetchColumn();
            $sch['total_dispatch_oc'] = (float)$dispatchOC;
            $sch['total_return_value'] = (float)$this->db->query("
                SELECT COALESCE(SUM(ri.quantity * ri.unit_price), 0)
                FROM returns r
                JOIN return_items ri ON ri.return_id = r.id
                WHERE r.dsr_id = {$sch['dsr_id']} AND r.return_date = '{$delivery_date}' AND (r.reason != 'Damage' OR r.reason IS NULL)
            ")->fetchColumn();
            
            $sch['total_damage_value'] = (float)$this->db->query("
                SELECT 
                    COALESCE((
                        SELECT SUM(ri.quantity * ri.unit_price)
                        FROM returns r
                        JOIN return_items ri ON ri.return_id = r.id
                        JOIN products p ON p.id = ri.product_id
                        WHERE r.dsr_id = {$sch['dsr_id']} AND r.return_date = '{$delivery_date}' AND r.reason = 'Damage'
                    ), 0)
                    +
                    COALESCE((
                        SELECT SUM(CAST(SUBSTRING_INDEX(r.reason, 'Amount: ', -1) AS DECIMAL(14,2)))
                        FROM returns r
                        LEFT JOIN return_items ri ON ri.return_id = r.id
                        WHERE r.dsr_id = {$sch['dsr_id']} AND r.return_date = '{$delivery_date}' AND r.reason LIKE '%Amount:%' AND ri.id IS NULL
                    ), 0)
            ")->fetchColumn();
            
            $saleVal = $this->db->query("
                SELECT COALESCE(SUM(di.delivered_quantity * di.unit_price), 0)
                FROM dispatch_items di
                JOIN dispatches d ON d.id = di.dispatch_id
                WHERE d.dsr_id = {$sch['dsr_id']} AND d.dispatch_date = '{$delivery_date}'
            ")->fetchColumn();
            
            $sch['total_sale_value'] = (float)$saleVal;
        }

        echo json_encode($schedules);
        exit;
    }

    public function apiDispatchNewPopupData(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $dsrs = $this->db->query("
            SELECT u.id, u.name, u.avatar 
            FROM users u JOIN roles r ON r.id = u.role_id 
            WHERE r.slug = 'dsr' AND u.status = 1
        ")->fetchAll();
        
        $srs = $this->db->prepare("
            SELECT u.id, u.name, u.avatar, COUNT(o.id) as order_count,
                   MAX(CASE WHEN soc.id IS NOT NULL THEN 1 ELSE 0 END) AS is_cutoff,
                   MAX(soc.cutoff_at) AS cutoff_at,
                   MAX(soc.is_auto) AS is_auto
            FROM users u 
            JOIN roles r ON r.id = u.role_id 
            JOIN orders o ON o.sr_id = u.id AND DATE(o.created_at) = ?
            LEFT JOIN sr_order_cutoffs soc ON soc.sr_id = u.id 
                AND soc.cutoff_date = ? 
                AND soc.undone_by IS NULL
            WHERE r.slug = 'sr' AND u.status = 1
            AND u.id NOT IN (
                SELECT sr_id FROM dispatch_schedule_srs dss 
                JOIN dispatch_schedules ds ON ds.id = dss.schedule_id 
                WHERE ds.dispatch_date = ?
            )
            GROUP BY u.id, u.name, u.avatar
        ");
        $srs->execute([$date, $date, $date]);
        $srsList = $srs->fetchAll();
        
        echo json_encode(['dsrs' => $dsrs, 'srs' => $srsList]);
        exit;
    }

    // ── SR Cutoff Status API (for manager overview) ────────────
    /**
     * GET /manager/api/sr-cutoff-status?date=Y-m-d
     * Returns today's cutoff status for all active SRs
     */
    public function apiSrCutoffStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $date = $_GET['date'] ?? date('Y-m-d');

        $rows = $this->db->prepare("
            SELECT u.id, u.name,
                   MAX(CASE WHEN soc.id IS NOT NULL THEN 1 ELSE 0 END) AS is_cutoff,
                   MAX(soc.cutoff_at) AS cutoff_at,
                   MAX(soc.is_auto) AS is_auto,
                   COUNT(o.id) as order_count,
                   COALESCE(SUM(o.total_amount), 0) as order_value
            FROM users u
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN orders o ON o.sr_id = u.id AND DATE(o.created_at) = ?
            LEFT JOIN sr_order_cutoffs soc ON soc.sr_id = u.id
                AND soc.cutoff_date = ?
                AND soc.undone_by IS NULL
            WHERE r.slug = 'sr' AND u.status = 1
            GROUP BY u.id, u.name
            ORDER BY u.name
        ");
        $rows->execute([$date, $date]);
        echo json_encode($rows->fetchAll());
        exit;
    }

    // ── Manager Undo Order Cutoff ────────────────────────────
    /**
     * POST /manager/api/order-cutoff/undo/{srId}
     * Manager only — undo an SR's order cutoff for today
     */
    public function apiUndoOrderCutoff(string $srId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $managerId = Auth::id();
        $today = date('Y-m-d');

        $stmt = $this->db->prepare("
            UPDATE sr_order_cutoffs
            SET undone_by = ?, undone_at = NOW()
            WHERE sr_id = ? AND cutoff_date = ? AND undone_by IS NULL
        ");
        $stmt->execute([$managerId, (int)$srId, $today]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'SR-এর অর্ডার কাটা রেস্টোর করা হয়েছে।']);
        } else {
            echo json_encode(['success' => false, 'message' => 'কোনো সক্্রিয় কাটা পাওয়া যায়নি।']);
        }
        exit;
    }


    public function apiDispatchAssign(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $date = $input['date'] ?? null;
        $assignments = $input['assignments'] ?? [];
        
        if (!$date || empty($assignments)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $this->db->beginTransaction();
        try {
            foreach ($assignments as $dsr_id => $data) {
                $sr_ids = $data['sr_ids'] ?? [];
                $delivery_date = $data['delivery_date'] ?? $date;
                
                if (empty($sr_ids)) continue;
                
                // Validate if all SRs have completed their order cutoff for the assignment date
                if (!empty($sr_ids)) {
                    $placeholders = implode(',', array_fill(0, count($sr_ids), '?'));
                    $checkParams = array_merge($sr_ids, [$date]);
                    $checkStmt = $this->db->prepare("
                        SELECT COUNT(DISTINCT sr_id) 
                        FROM sr_order_cutoffs 
                        WHERE sr_id IN ($placeholders) 
                        AND cutoff_date = ? 
                        AND undone_by IS NULL
                    ");
                    $checkStmt->execute($checkParams);
                    $completedCount = (int)$checkStmt->fetchColumn();
                    
                    if ($completedCount < count(array_unique($sr_ids))) {
                        throw new \Exception("One or more SRs have not completed their order cutoff. Cannot assign.");
                    }
                }

                $stmt = $this->db->prepare("INSERT INTO dispatch_schedules (dsr_id, dispatch_date, delivery_date, status) VALUES (?, ?, ?, 'assigned')");
                $stmt->execute([$dsr_id, $date, $delivery_date]);
                $schedule_id = $this->db->lastInsertId();
                
                $srStmt = $this->db->prepare("INSERT INTO dispatch_schedule_srs (schedule_id, sr_id) VALUES (?, ?)");
                foreach ($sr_ids as $sr_id) {
                    $srStmt->execute([$schedule_id, $sr_id]);
                }
            }
            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchUpdateDsr(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $newDsrId = (int)($input['dsr_id'] ?? 0);

        if (!$scheduleId || !$newDsrId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $sch = $this->db->prepare("SELECT dsr_id, dispatch_date, delivery_date FROM dispatch_schedules WHERE id = ?");
        $sch->execute([$scheduleId]);
        $schData = $sch->fetch();

        if (!$schData) {
            echo json_encode(['success' => false, 'message' => 'Dispatch schedule not found']);
            exit;
        }

        // Do not allow changing DSR if the schedule is already dispatched or returned
        if (in_array($schData['status'], ['dispatched', 'returned', 'in_transit'])) {
            echo json_encode(['success' => false, 'message' => 'Cannot change DSR for a schedule that is already dispatched. Please return or delete the schedule instead.']);
            exit;
        }

        $oldDsrId = $schData['dsr_id'];
        $date = $schData['dispatch_date'];
        $deliv_date = $schData['delivery_date'] ?: $date;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE dispatch_schedules SET dsr_id = ? WHERE id = ?");
            $stmt->execute([$newDsrId, $scheduleId]);

            // Only update dispatches belonging to this schedule's orders
            // Find all order_ids associated with this schedule
            $ordersQuery = $this->db->prepare("
                SELECT o.id 
                FROM orders o
                JOIN dispatch_schedule_srs dss ON dss.sr_id = o.sr_id
                WHERE dss.schedule_id = ? AND DATE(o.created_at) = ?
            ");
            $ordersQuery->execute([$scheduleId, $date]);
            $orderIds = $ordersQuery->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($orderIds)) {
                $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                $params = array_merge([$newDsrId], $orderIds);
                // Also ensure we match the old DSR and date just to be safe
                $params[] = $oldDsrId;
                $params[] = $deliv_date;
                $this->db->prepare("UPDATE dispatches SET dsr_id = ? WHERE order_id IN ($placeholders) AND dsr_id = ? AND dispatch_date = ?")->execute($params);
            }
            
            // For the extra items (order_id IS NULL) dispatch created during organize,
            // it is very difficult to uniquely identify which one belongs to this schedule if there are multiple.
            // But since status is only 'assigned' or 'organized', there shouldn't be returns or settlements yet.
            // We just update the order_id IS NULL dispatch if it exists and belongs to this DSR and Date.
            // However, this might move another schedule's extras if they share the same DSR and date.
            // To be safe, we can move 1 limit if possible, or just move it.
            $this->db->prepare("UPDATE dispatches SET dsr_id = ? WHERE order_id IS NULL AND dsr_id = ? AND dispatch_date = ? LIMIT 1")->execute([$newDsrId, $oldDsrId, $deliv_date]);

            // Note: returns and settlements are not expected to exist if status is not dispatched/returned.
            // So we can omit updating them here.

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchUpdateDeliveryDate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $newDeliveryDate = trim($input['delivery_date'] ?? '');

        if (!$scheduleId || !$newDeliveryDate) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $sch = $this->db->prepare("SELECT status FROM dispatch_schedules WHERE id = ?");
        $sch->execute([$scheduleId]);
        $schData = $sch->fetch();

        if (!$schData) {
            echo json_encode(['success' => false, 'message' => 'Dispatch schedule not found']);
            exit;
        }

        // Returned schedules cannot have their date changed
        if ($schData['status'] === 'returned') {
            echo json_encode(['success' => false, 'message' => 'Returned dispatch এর delivery date পরিবর্তন করা যাবে না।']);
            exit;
        }

        try {
            // Simply update only the delivery_date column. Nothing else.
            $this->db->prepare("UPDATE dispatch_schedules SET delivery_date = ? WHERE id = ?")
                     ->execute([$newDeliveryDate, $scheduleId]);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchDelete(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $schedule = $this->db->prepare("SELECT * FROM dispatch_schedules WHERE id = ?");
        $schedule->execute([$id]);
        $sch = $schedule->fetch();

        if (!$sch) {
            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
            exit;
        }

        if (!in_array($sch['status'], ['assigned', 'organized'])) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete a schedule that is already dispatched or returned.']);
            exit;
        }

        $dsrId = $sch['dsr_id'];
        $date = $sch['dispatch_date'];
        $deliv_date = $sch['delivery_date'] ?: $date;

        $this->db->beginTransaction();
        try {
            // Revert orders status to 'confirmed' so they can be dispatched again
            $dispatches = $this->db->prepare("SELECT order_id FROM dispatches WHERE dsr_id = ? AND dispatch_date = ? AND order_id IS NOT NULL");
            $dispatches->execute([$dsrId, $deliv_date]);
            $orderIds = $dispatches->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($orderIds)) {
                $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                $this->db->prepare("UPDATE orders SET status = 'confirmed' WHERE id IN ($placeholders)")->execute($orderIds);
            }

            // Delete dispatch_items
            $this->db->prepare("
                DELETE di FROM dispatch_items di
                JOIN dispatches d ON d.id = di.dispatch_id
                WHERE d.dsr_id = ? AND d.dispatch_date = ?
            ")->execute([$dsrId, $deliv_date]);

            // Delete dispatches
            $this->db->prepare("DELETE FROM dispatches WHERE dsr_id = ? AND dispatch_date = ?")->execute([$dsrId, $deliv_date]);

            // Delete extras
            $this->db->prepare("DELETE FROM dispatch_extras WHERE schedule_id = ?")->execute([$id]);

            // Delete schedule srs
            $this->db->prepare("DELETE FROM dispatch_schedule_srs WHERE schedule_id = ?")->execute([$id]);

            // Delete schedule
            $this->db->prepare("DELETE FROM dispatch_schedules WHERE id = ?")->execute([$id]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchSrDetails(string $id): void
    {
        $this->apiDispatchCompanyDetails($id);
    }

    public function apiDispatchCompanyDetails(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $schedule = $this->db->query("SELECT id, dispatch_date, delivery_date, dsr_id FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();
        if (!$schedule) {
            echo json_encode([]);
            exit;
        }

        $scheduleId = (int)$schedule['id'];
        $dispatchDate = $schedule['dispatch_date'];
        $deliveryDate = $schedule['delivery_date'] ?: $schedule['dispatch_date'];
        $dsrId = (int)$schedule['dsr_id'];

        $companies = $this->db->query("
            SELECT DISTINCT 
                IFNULL(c.id, 0) AS id, 
                IFNULL(c.name, 'General') AS name
            FROM (
                SELECT p.id AS product_id, p.company_id
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                JOIN order_items oi ON oi.order_id = o.id
                JOIN products p ON p.id = oi.product_id
                WHERE dss.schedule_id = {$scheduleId}

                UNION

                SELECT p.id AS product_id, p.company_id
                FROM van_stock vs
                JOIN products p ON p.id = vs.product_id
                WHERE vs.dsr_id = {$dsrId} AND DATE(vs.loaded_at) = '{$deliveryDate}'

                UNION

                SELECT p.id AS product_id, p.company_id
                FROM returns r
                JOIN return_items ri ON ri.return_id = r.id
                JOIN products p ON p.id = ri.product_id
                WHERE r.dsr_id = {$dsrId} AND r.return_date = '{$deliveryDate}'
            ) active_prods
            LEFT JOIN companies c ON c.id = active_prods.company_id
            ORDER BY name ASC
        ")->fetchAll();

        foreach ($companies as &$company) {
            $cId = (int)$company['id'];
            $companyCondition = $cId > 0 ? "p.company_id = {$cId}" : "(p.company_id IS NULL OR p.company_id = 0)";

            $orderedVal = $this->db->query("
                SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                JOIN order_items oi ON oi.order_id = o.id
                JOIN products p ON p.id = oi.product_id
                WHERE dss.schedule_id = {$scheduleId} AND {$companyCondition}
            ")->fetchColumn();
            $company['ordered_value'] = (float)$orderedVal;

            $dispatchVal = $this->db->query("
                SELECT COALESCE(SUM(di.quantity * di.unit_price), 0)
                FROM dispatches d
                JOIN dispatch_items di ON di.dispatch_id = d.id
                JOIN products p ON p.id = di.product_id
                WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND {$companyCondition}
            ")->fetchColumn();
            $company['dispatch_items_value'] = (float)$dispatchVal;

            $returnVal = $this->db->query("
                SELECT COALESCE(SUM(ri.quantity * ri.unit_price), 0)
                FROM returns r
                JOIN return_items ri ON ri.return_id = r.id
                JOIN products p ON p.id = ri.product_id
                WHERE r.dsr_id = {$dsrId} AND r.return_date = '{$deliveryDate}' AND (r.reason != 'Damage' OR r.reason IS NULL) AND {$companyCondition}
            ")->fetchColumn();
            $company['return_value'] = (float)$returnVal;

            $damageVal = $this->db->query("
                SELECT COALESCE(SUM(ri.quantity * ri.unit_price), 0)
                FROM returns r
                JOIN return_items ri ON ri.return_id = r.id
                JOIN products p ON p.id = ri.product_id
                WHERE r.dsr_id = {$dsrId} AND r.return_date = '{$deliveryDate}' AND r.reason = 'Damage' AND {$companyCondition}
            ")->fetchColumn();
            $company['damage_value'] = (float)$damageVal;

            $saleVal = $this->db->query("
                SELECT COALESCE(SUM(di.delivered_quantity * di.unit_price), 0)
                FROM dispatch_items di
                JOIN products p ON p.id = di.product_id
                JOIN dispatches d ON d.id = di.dispatch_id
                WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND {$companyCondition}
            ")->fetchColumn();
            $company['sale_value'] = (float)$saleVal;

            $products = $this->db->query("
                SELECT p.id, p.name,
                       (
                           SELECT COALESCE(SUM(oi.quantity), 0)
                           FROM dispatch_schedule_srs dss
                           JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                           JOIN order_items oi ON oi.order_id = o.id
                           WHERE dss.schedule_id = {$scheduleId} AND oi.product_id = p.id
                       ) as ordered_qty,
                       (
                           SELECT COALESCE(SUM(vs.initial_qty), 0)
                           FROM van_stock vs
                           WHERE vs.dsr_id = {$dsrId} AND DATE(vs.loaded_at) = '{$deliveryDate}' AND vs.product_id = p.id
                       ) as dispatched_qty,
                       (
                           SELECT COALESCE(SUM(di.delivered_quantity), 0)
                           FROM dispatch_items di
                           JOIN dispatches d ON d.id = di.dispatch_id
                           WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND di.product_id = p.id
                       ) as sale_qty,
                       (
                           SELECT COALESCE(SUM(ri.quantity), 0)
                           FROM returns r
                           JOIN return_items ri ON ri.return_id = r.id
                           WHERE r.dsr_id = {$dsrId} AND r.return_date = '{$deliveryDate}' AND ri.product_id = p.id AND (r.reason != 'Damage' OR r.reason IS NULL)
                       ) as returned_qty
                FROM products p
                WHERE {$companyCondition}
                HAVING ordered_qty > 0 OR dispatched_qty > 0 OR sale_qty > 0 OR returned_qty > 0
                ORDER BY p.name ASC
            ")->fetchAll();

            $company['products'] = $products;

            $srs = $this->db->query("
                SELECT u.id, u.name,
                       (
                           SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
                           FROM orders o
                           JOIN order_items oi ON oi.order_id = o.id
                           JOIN products p ON p.id = oi.product_id
                           WHERE o.sr_id = u.id AND DATE(o.created_at) = '{$dispatchDate}' AND {$companyCondition}
                       ) as order_value,
                       (
                           SELECT COALESCE(SUM((oi.unit_price - oi.buying_price) * oi.quantity), 0)
                           FROM orders o
                           JOIN order_items oi ON oi.order_id = o.id
                           JOIN products p ON p.id = oi.product_id
                           WHERE o.sr_id = u.id AND DATE(o.created_at) = '{$dispatchDate}' AND {$companyCondition}
                       ) as total_oc,
                       (
                           SELECT COALESCE(SUM(di.delivered_quantity * IFNULL(oi.unit_price, p.price)), 0)
                           FROM dispatch_items di
                           JOIN products p ON p.id = di.product_id
                           JOIN dispatches d ON d.id = di.dispatch_id
                           LEFT JOIN orders o ON o.id = d.order_id
                           LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
                           WHERE d.dispatch_date = '{$deliveryDate}' AND o.sr_id = u.id AND {$companyCondition}
                       ) as sale_value
                FROM dispatch_schedule_srs dss
                JOIN users u ON u.id = dss.sr_id
                WHERE dss.schedule_id = {$scheduleId}
                HAVING order_value > 0 OR sale_value > 0 OR total_oc > 0
                ORDER BY u.name ASC
            ")->fetchAll();

            foreach ($srs as &$sr) {
                $srId = (int)$sr['id'];
                $sr['products'] = $this->db->query("
                    SELECT p.name,
                           SUM(oi.quantity) as ordered_qty
                    FROM orders o
                    JOIN order_items oi ON oi.order_id = o.id
                    JOIN products p ON p.id = oi.product_id
                    WHERE o.sr_id = {$srId} AND DATE(o.created_at) = '{$dispatchDate}' AND {$companyCondition}
                    GROUP BY p.id, p.name
                    ORDER BY p.name ASC
                ")->fetchAll();
            }

            $company['srs'] = $srs;
        }

        echo json_encode($companies);
        exit;
    }

    public function apiDispatchOrganizeData(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $schedule = $this->db->query("SELECT dispatch_date FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();

            if (!$schedule) {
                echo json_encode([]);
                exit;
            }

            $products = $this->db->query("
                SELECT p.id as product_id, p.name, p.image, p.pieces_per_box, p.box_type,
                       SUM(oi.quantity)          as total_ordered_qty,
                       IFNULL(MAX(de.qty_boxes),  0) as extra_boxes,
                       IFNULL(MAX(de.qty_pieces), 0) as extra_pieces
                FROM dispatch_schedule_srs dss
                JOIN orders o     ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$schedule['dispatch_date']}'
                JOIN order_items oi ON oi.order_id = o.id
                JOIN products p   ON p.id = oi.product_id
                LEFT JOIN dispatch_extras de ON de.schedule_id = dss.schedule_id AND de.product_id = p.id
                WHERE dss.schedule_id = " . (int)$id . "
                GROUP BY p.id, p.name, p.image, p.pieces_per_box, p.box_type
            ")->fetchAll();

            echo json_encode($products);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchOrganizeSave(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $extras = $input['extras'] ?? []; 
        
        $this->db->beginTransaction();
        try {
            // Delete existing extras for this schedule before saving new ones
            $this->db->prepare("DELETE FROM dispatch_extras WHERE schedule_id = ?")->execute([$id]);

            // Save extras
            $stmt = $this->db->prepare("INSERT INTO dispatch_extras (schedule_id, product_id, qty_boxes, qty_pieces) VALUES (?, ?, ?, ?)");
            foreach ($extras as $ex) {
                if ($ex['boxes'] != 0 || $ex['pcs'] != 0) {
                    $stmt->execute([$id, $ex['product_id'], $ex['boxes'], $ex['pcs']]);
                }
            }
            
            // Set schedule to organized
            $this->db->prepare("UPDATE dispatch_schedules SET status = 'organized' WHERE id = ?")->execute([$id]);

            // Create Dispatches so DSR can see them for collection
            $schedule = $this->db->prepare("SELECT * FROM dispatch_schedules WHERE id=?");
            $schedule->execute([$id]);
            $sch = $schedule->fetch();
            
            if ($sch) {
                $dsrId = $sch['dsr_id'];
                $date = $sch['dispatch_date']; // Order date
                $deliv_date = $sch['delivery_date'] ?: $date;
                
                // 1. Convert Orders into Dispatches
                $orders = $this->db->prepare("
                    SELECT o.id, o.warehouse_id 
                    FROM orders o 
                    JOIN dispatch_schedule_srs dss ON dss.sr_id = o.sr_id
                    WHERE dss.schedule_id = ? AND DATE(o.created_at) = ? AND o.status IN ('pending', 'confirmed')
                ");
                $orders->execute([$id, $date]);
                $ordersList = $orders->fetchAll();
                
                foreach ($ordersList as $o) {
                    $this->db->prepare("INSERT INTO dispatches (order_id, dsr_id, warehouse_id, dispatch_date, status) VALUES (?, ?, ?, ?, 'pending')")
                             ->execute([$o['id'], $dsrId, $o['warehouse_id'], $deliv_date]);
                    $dispatchId = $this->db->lastInsertId();
                    
                    $items = $this->db->prepare("SELECT * FROM order_items WHERE order_id=?");
                    $items->execute([$o['id']]);
                    foreach($items->fetchAll() as $item) {
                        $this->db->prepare("INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, product_name, box_type, pieces_per_box, unit_price, buying_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                                 ->execute([$dispatchId, $item['product_id'], $item['lot_id'], $item['quantity'], $item['product_name'], $item['box_type'], $item['pieces_per_box'], $item['unit_price'], $item['buying_price'] ?? $item['unit_price'], $item['total_price']]);
                    }
                    
                    // Update order status so they don't get dispatched twice
                    $this->db->prepare("UPDATE orders SET status='dispatched' WHERE id=?")->execute([$o['id']]);
                }
                
                // Reset existing dispatch_items to original order quantities before applying new organize adjustments.
                // This handles re-organize scenarios and fixes dispatches created with old code.
                // Only reset pending dispatches (not yet in_transit/delivered) that belong to orders.
                $this->db->prepare("
                    UPDATE dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
                    SET di.quantity = oi.quantity
                    WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.status = 'pending' AND d.order_id IS NOT NULL
                ")->execute([$dsrId, $deliv_date]);

                
                // 2. Apply organized qty adjustments to dispatch_items
                // Fetch all extras (differences set by manager during organize)
                $extrasQuery = $this->db->prepare("
                    SELECT de.product_id, p.pieces_per_box, de.qty_boxes, de.qty_pieces 
                    FROM dispatch_extras de
                    JOIN products p ON p.id = de.product_id
                    WHERE de.schedule_id = ?
                ");
                $extrasQuery->execute([$id]);
                $extraList = $extrasQuery->fetchAll();
                
                // Separate into positive extras (add stock) and negative adjustments (reduce qty)
                $positiveExtras = [];
                $negativeAdjustments = [];
                foreach ($extraList as $ex) {
                    $ppb = max(1, (int)$ex['pieces_per_box']);
                    $diffQty = ((int)$ex['qty_boxes'] * $ppb) + (int)$ex['qty_pieces'];
                    if ($diffQty > 0) {
                        $positiveExtras[] = array_merge($ex, ['diffQty' => $diffQty, 'ppb' => $ppb]);
                    } elseif ($diffQty < 0) {
                        $negativeAdjustments[] = array_merge($ex, ['diffQty' => $diffQty]);
                    }
                }
                
                // APPROACH 1 (Van-Level Dispatch):
                // Negative adjustments are NO LONGER applied to dispatch_items.
                // dispatch_items.quantity keeps the original ordered qty for billing/order reference.
                // The actual van load qty is controlled via dispatch_extras at collection time.
                // (See apiDispatchStatusUpdate where van_stock is loaded with the corrected qty.)
                
                // For positive extras: create a separate "extra stock" dispatch
                if (!empty($positiveExtras)) {
                    $wId = Auth::warehouseId() ?: $this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn();
                    $this->db->prepare("INSERT INTO dispatches (order_id, dsr_id, warehouse_id, dispatch_date, status) VALUES (NULL, ?, ?, ?, 'pending')")
                             ->execute([$dsrId, $wId, $deliv_date]);
                    $extraDispatchId = $this->db->lastInsertId();
                    
                    foreach ($positiveExtras as $ex) {
                        $qty = $ex['diffQty'];
                        
                        $pQuery = $this->db->prepare("SELECT name, box_type, pieces_per_box, price FROM products WHERE id=?");
                        $pQuery->execute([$ex['product_id']]);
                        $pd = $pQuery->fetch(PDO::FETCH_ASSOC);
                        
                        $this->db->prepare("INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, product_name, box_type, pieces_per_box, unit_price, buying_price, total_price) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)")
                                 ->execute([$extraDispatchId, $ex['product_id'], $qty, $pd['name'], $pd['box_type'], $pd['pieces_per_box'], $pd['price'], $pd['price'], $qty * $pd['price']]);
                    }
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiDispatchStatusUpdate(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? 'assigned';
        
        $sch = $this->db->query("SELECT dsr_id, dispatch_date, delivery_date FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();
        if (!$sch) {
            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
            exit;
        }
        $dsrId = $sch['dsr_id'];
        $date = $sch['dispatch_date'];
        $deliv_date = $sch['delivery_date'] ?: $date;

        $this->db->beginTransaction();
        try {
            if ($status === 'dispatched') {
                // 1. Get all ordered dispatch_items (original order quantities, pending status)
                $q = $this->db->prepare("
                    SELECT di.product_id, di.lot_id, d.warehouse_id, SUM(di.quantity) as total_qty
                    FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    WHERE d.dsr_id=? AND d.dispatch_date=? AND d.status='pending'
                    GROUP BY di.product_id, di.lot_id, d.warehouse_id
                ");
                $q->execute([$dsrId, $deliv_date]);
                $itemsToLoad = $q->fetchAll();

                // 2. Get dispatch_extras adjustments for this schedule (negative = manager reduced van load)
                //    Only apply NEGATIVE adjustments — positive extras are already in separate dispatch_items.
                $extrasQ = $this->db->prepare("
                    SELECT de.product_id,
                           de.qty_boxes,
                           de.qty_pieces,
                           p.pieces_per_box
                    FROM dispatch_extras de
                    JOIN products p ON p.id = de.product_id
                    WHERE de.schedule_id = ?
                ");
                $extrasQ->execute([$id]);
                $extrasAdjMap = [];
                foreach ($extrasQ->fetchAll() as $exRow) {
                    $ppb = max(1, (int)$exRow['pieces_per_box']);
                    $adj = ((int)$exRow['qty_boxes'] * $ppb) + (int)$exRow['qty_pieces'];
                    if ($adj < 0) { // Only negative (reduction) — positive already in extra dispatch
                        $extrasAdjMap[(int)$exRow['product_id']] = ($extrasAdjMap[(int)$exRow['product_id']] ?? 0) + $adj;
                    }
                }

                // 3. Apply negative adjustments to get actual van load qty per product
                $appliedProducts = [];
                foreach ($itemsToLoad as &$item) {
                    $pid = (int)$item['product_id'];
                    if (!isset($appliedProducts[$pid]) && isset($extrasAdjMap[$pid])) {
                        $item['total_qty'] = max(0, (int)$item['total_qty'] + $extrasAdjMap[$pid]);
                        $appliedProducts[$pid] = true;
                    }
                }
                unset($item);

                // 4. Load van_stock with actual organized qty, save initial_qty, and guard against date accumulation
                foreach ($itemsToLoad as $item) {
                    if ((int)$item['total_qty'] <= 0) continue; // Skip products with 0 organized qty

                    $lotCondition = $item['lot_id'] === null ? "IS NULL" : "= ?";
                    $params = [$dsrId, $item['product_id']];
                    if ($item['lot_id'] !== null) $params[] = $item['lot_id'];
                    
                    $check = $this->db->prepare("SELECT id, loaded_at FROM van_stock WHERE dsr_id=? AND product_id=? AND lot_id $lotCondition LIMIT 1");
                    $check->execute($params);
                    
                    if ($row = $check->fetch()) {
                        // If previous load was from a different date, reset initial_qty and quantity for the new day
                        if ($row['loaded_at'] !== $deliv_date) {
                            $this->db->prepare("UPDATE van_stock SET quantity = ?, initial_qty = ?, loaded_at = ? WHERE id=?")
                                     ->execute([$item['total_qty'], $item['total_qty'], $deliv_date, $row['id']]);
                        } else {
                            $this->db->prepare("UPDATE van_stock SET quantity = quantity + ?, initial_qty = initial_qty + ?, loaded_at = ? WHERE id=?")
                                     ->execute([$item['total_qty'], $item['total_qty'], $deliv_date, $row['id']]);
                        }
                    } else {
                        $this->db->prepare("INSERT INTO van_stock (dsr_id, product_id, lot_id, quantity, initial_qty, loaded_at) VALUES (?, ?, ?, ?, ?, ?)")
                                 ->execute([$dsrId, $item['product_id'], $item['lot_id'], $item['total_qty'], $item['total_qty'], $deliv_date]);
                    }

                    // 5. Deduct actual dispatched qty from warehouse inventory
                    $lotCondition = $item['lot_id'] === null ? "IS NULL" : "= ?";
                    $lotParams = $item['lot_id'] === null ? [] : [$item['lot_id']];
                    
                    $invQuery = $this->db->prepare("
                        SELECT i.id, i.qty_boxes, i.qty_pieces, p.pieces_per_box 
                        FROM inventory i 
                        JOIN products p ON p.id = i.product_id 
                        WHERE i.product_id=? AND i.warehouse_id=? AND i.lot_id $lotCondition
                    ");
                    $invQuery->execute(array_merge([$item['product_id'], $item['warehouse_id']], $lotParams));
                    $invRow = $invQuery->fetch();
                    
                    if ($invRow) {
                        $ppb = max(1, (int)$invRow['pieces_per_box']);
                        $totalStockPcs = ((int)$invRow['qty_boxes'] * $ppb) + (int)$invRow['qty_pieces'];
                        $newStockPcs = max(0, $totalStockPcs - (int)$item['total_qty']);
                        
                        $newBoxes = floor($newStockPcs / $ppb);
                        $newPcs = $newStockPcs % $ppb;
                        
                        $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                 ->execute([$newBoxes, $newPcs, $invRow['id']]);
                    }
                }

                // 2. Mark dispatches as in_transit
                $this->db->prepare("UPDATE dispatches SET status='in_transit', updated_at=NOW() WHERE dsr_id=? AND dispatch_date=? AND status='pending'")
                         ->execute([$dsrId, $deliv_date]);
            }

            $this->db->prepare("UPDATE dispatch_schedules SET status = ? WHERE id = ?")->execute([$status, $id]);
            
            $this->db->commit();
            \Helpers::logManagerActivity(\Auth::id(), 'dispatch_status_change', "Changed dispatch schedule $id status to $status", $id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Settlements
    // ══════════════════════════════════════════════════════════
    public function settlements(): void
    {
        $items = $this->db->query("
            SELECT s.*, u.name AS dsr_name,
            (
                COALESCE((
                    SELECT SUM(ri.quantity * ri.unit_price)
                    FROM returns r
                    JOIN return_items ri ON ri.return_id=r.id
                    JOIN products p ON p.id=ri.product_id
                    WHERE r.dsr_id=s.dsr_id AND r.return_date=s.date AND r.reason='Damage'
                ), 0)
                +
                COALESCE((
                    SELECT SUM(CAST(SUBSTRING_INDEX(r.reason, 'Amount: ', -1) AS DECIMAL(14,2)))
                    FROM returns r
                    LEFT JOIN return_items ri ON ri.return_id=r.id
                    WHERE r.dsr_id=s.dsr_id AND r.return_date=s.date AND r.reason LIKE 'Damage%' AND ri.id IS NULL
                ), 0)
            ) AS live_damage,
            (
                SELECT COALESCE(SUM(amount), 0)
                FROM expenses
                WHERE dsr_id=s.dsr_id AND date=s.date
            ) AS live_expense,
            (
                SELECT COALESCE(SUM(COALESCE(di.delivered_quantity, 0) * (COALESCE(di.unit_price, p.price) - di.buying_price)), 0)
                FROM dispatches d
                JOIN dispatch_items di ON d.id = di.dispatch_id
                JOIN products p ON p.id = di.product_id
                LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
                WHERE d.dsr_id = s.dsr_id AND d.dispatch_date = s.date AND d.status IN ('delivered', 'partial')
            ) AS live_delivery_oc
            FROM settlements s
            LEFT JOIN users u ON u.id = s.dsr_id
            ORDER BY s.date DESC, s.created_at DESC
        ")->fetchAll();
        $this->render('settlements', compact('items'));
    }

    public function apiSettlementUpdate(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        // Allow fallback to POST array if fetch didn't send JSON (though frontend should send JSON)
        $isJson = (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
        $input = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        
        $status = $input['status'] ?? 'pending';
        $managerNotes = $input['manager_notes'] ?? null;
        $totalDamage = (float)($input['total_damage'] ?? 0);
        $totalExpense = (float)($input['total_expense'] ?? 0);
        $deliveryOc = (float)($input['delivery_oc'] ?? 0);
        $countedCash = (float)($input['counted_cash'] ?? 0);
        $cashBreakdown = $input['cash_breakdown'] ?? '{}';
        
        // Fetch existing settlement to calculate new should_pay and difference
        $stmt = $this->db->prepare("SELECT total_dispatched, total_returned FROM settlements WHERE id=?");
        $stmt->execute([$id]);
        $settlement = $stmt->fetch();
        if (!$settlement) {
            echo json_encode(['success' => false, 'message' => 'Settlement not found']);
            exit;
        }

        $shouldPay = $settlement['total_dispatched'] - $settlement['total_returned'] - $totalDamage - $totalExpense + $deliveryOc;
        $difference = $countedCash - $shouldPay;

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE settlements SET status=?, manager_notes=?, total_damage=?, total_expense=?, delivery_oc=?, should_pay=?, counted_cash=?, difference=?, cash_breakdown=?, updated_at=NOW() WHERE id=?")
                     ->execute([$status, $managerNotes, $totalDamage, $totalExpense, $deliveryOc, $shouldPay, $countedCash, $difference, $cashBreakdown, $id]);
            
            $this->db->commit();
            \Helpers::logManagerActivity(\Auth::id(), 'settlement_status_change', "Changed settlement $id status to $status", $id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Attendance
    // ══════════════════════════════════════════════════════════
    public function attendance(): void
    {
        $this->ensureAttendanceQrTable();
        $date = $this->get('date', date('Y-m-d'));

        // DSR attendance from QR scans
        $q = $this->db->prepare("
            SELECT da.*, u.name AS user_name, u.phone
            FROM dsr_attendance da
            JOIN users u ON u.id = da.dsr_id
            WHERE da.attendance_date = ?
            ORDER BY da.scan_time ASC
        ");
        $q->execute([$date]);
        $items = $q->fetchAll();

        // All DSRs
        try {
            $dsrs = $this->db->query("
                SELECT u.id, u.name, u.phone FROM users u
                WHERE u.role_id = (SELECT id FROM roles WHERE slug='dsr' LIMIT 1)
                ORDER BY u.name
            ")->fetchAll();
        } catch (PDOException $e) { $dsrs = []; }

        // Active QR code
        try {
            $qrRow = $this->db->query("SELECT * FROM attendance_qr_codes WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch();
        } catch (PDOException $e) { $qrRow = null; }

        // Users for manual form (kept for backwards compat)
        $users = $this->db->query("
            SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id=u.role_id
            WHERE r.slug IN ('sr','dsr') AND u.status=1 ORDER BY u.name
        ")->fetchAll();

        $this->render('attendance', compact('items', 'users', 'date', 'dsrs', 'qrRow'));
    }

    public function attendanceStore(): void
    {
        $this->verifyCsrf();
        $userId   = $this->post('user_id');
        $date     = $this->post('date', date('Y-m-d'));
        $status   = $this->post('status', 'present');
        $checkIn  = $this->post('check_in') ?: null;
        $checkOut = $this->post('check_out') ?: null;

        $exists = $this->db->prepare("SELECT id FROM attendance WHERE user_id=? AND date=?");
        $exists->execute([$userId, $date]);
        if ($exists->fetch()) {
            $this->db->prepare("UPDATE attendance SET status=?,check_in=?,check_out=? WHERE user_id=? AND date=?")
                     ->execute([$status, $checkIn, $checkOut, $userId, $date]);
        } else {
            $this->db->prepare("INSERT INTO attendance (user_id,date,check_in,check_out,status) VALUES (?,?,?,?,?)")
                     ->execute([$userId, $date, $checkIn, $checkOut, $status]);
        }
        $this->flash('success', 'Attendance saved.'); $this->redirect('manager/attendance?date='.$date);
    }

    // ══════════════════════════════════════════════════════════
    //  Ready Sale
    // ══════════════════════════════════════════════════════════
    public function readysale(): void
    {
        $items = $this->db->query("
            SELECT rs.*, p.name AS product_name, w.name AS warehouse_name, l.lot_number
            FROM readysales rs
            JOIN products p ON p.id = rs.product_id
            JOIN warehouses w ON w.id = rs.warehouse_id
            LEFT JOIN lots l ON l.id = rs.lot_id
            ORDER BY rs.created_at DESC
        ")->fetchAll();
        $products   = $this->db->query("SELECT * FROM products WHERE status=1 ORDER BY name")->fetchAll();
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $lots       = $this->db->query("SELECT l.*, p.name AS product_name FROM lots l JOIN products p ON p.id=l.product_id ORDER BY p.name")->fetchAll();
        $this->render('readysale', compact('items', 'products', 'warehouses', 'lots'));
    }

    public function readysaleStore(): void
    {
        $this->verifyCsrf();
        $pId = $this->post('product_id');
        $pQuery = $this->db->prepare("SELECT name, box_type, pieces_per_box FROM products WHERE id=?");
        $pQuery->execute([$pId]);
        $pd = $pQuery->fetch(PDO::FETCH_ASSOC);

        $this->db->prepare("INSERT INTO readysales (warehouse_id,product_id,lot_id,quantity,price,buying_price,product_name,box_type,pieces_per_box) VALUES (?,?,?,?,?,?,?,?,?)")
                 ->execute([$this->post('warehouse_id'), $pId, $this->post('lot_id') ?: null, $this->post('quantity',0), $this->post('price',0), $pd['price'] ?? 0, $pd['name'] ?? null, $pd['box_type'] ?? null, $pd['pieces_per_box'] ?? 1]);
        $this->flash('success', 'Ready sale record added.'); $this->redirect('manager/readysale');
    }

    private function convertToWebp(string $source, string $destination): bool
    {
        $info = @getimagesize($source);
        if ($info === false) return false;

        $mime = $info['mime'];
        $image = null;

        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($source);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($source);
                break;
            case 'image/webp':
                if (is_uploaded_file($source)) {
                    return move_uploaded_file($source, $destination);
                }
                return @copy($source, $destination);
            default:
                if (is_uploaded_file($source)) {
                    return move_uploaded_file($source, $destination);
                }
                return @copy($source, $destination);
        }

        if ($image) {
            $result = @imagewebp($image, $destination, 80);
            imagedestroy($image);
            return $result;
        }

        if (is_uploaded_file($source)) {
            return move_uploaded_file($source, $destination);
        }
        return @copy($source, $destination);
    }

    /**
     * Download or decode product image from URL, data URL, or local path and save to assets/uploads/
     */
    private function saveProductImageFromUrlOrData(string $rawUrl, string $uploadDir): ?string
    {
        $rawUrl = trim($rawUrl);
        if (empty($rawUrl)) return null;

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // 1. Data URL (Base64)
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/is', $rawUrl, $matches)) {
            $imgType = strtolower($matches[1]);
            $base64Data = $matches[2];
            $decoded = base64_decode($base64Data);
            if ($decoded !== false && strlen($decoded) > 0) {
                $tempFile = rtrim($uploadDir, '/') . '/img_data_' . uniqid() . '.' . $imgType;
                file_put_contents($tempFile, $decoded);
                
                $filename = 'prod_' . uniqid() . '.webp';
                if ($this->convertToWebp($tempFile, $uploadDir . $filename)) {
                    @unlink($tempFile);
                    return 'assets/uploads/' . $filename;
                } else {
                    $ext = ($imgType === 'jpeg' ? 'jpg' : $imgType);
                    $filename = 'prod_' . uniqid() . '.' . ($ext ?: 'jpg');
                    if (@copy($tempFile, $uploadDir . $filename)) {
                        @unlink($tempFile);
                        return 'assets/uploads/' . $filename;
                    }
                    @unlink($tempFile);
                }
            }
            return null;
        }

        // 2. Check if local relative/absolute path or file already in assets/uploads/
        $parsedPath = parse_url($rawUrl, PHP_URL_PATH) ?? '';
        $cleanPath = ltrim($parsedPath, '/');
        if (!empty($cleanPath)) {
            if (file_exists(PUB_PATH . '/' . $cleanPath) && is_file(PUB_PATH . '/' . $cleanPath)) {
                return $cleanPath;
            }
            $baseName = basename($cleanPath);
            if (file_exists($uploadDir . $baseName) && is_file($uploadDir . $baseName)) {
                return 'assets/uploads/' . $baseName;
            }
        }

        // 3. HTTP / HTTPS URL download
        $url = str_replace(' ', '%20', $rawUrl);
        if (strpos($url, 'drive.google.com') !== false) {
            if (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $m)) {
                $url = 'https://drive.google.com/uc?export=download&id=' . $m[1];
            } elseif (preg_match('/d\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                $url = 'https://drive.google.com/uc?export=download&id=' . $m[1];
            }
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}/i', $url)) {
                $url = 'http://' . $url;
            } else {
                return null;
            }
        }

        $imgData = $this->fetchUrlContent($url);
        $debugLogPath = rtrim($uploadDir, '/') . '/image_download_debug.log';
        if ($imgData === false || strlen($imgData) == 0) {
            @file_put_contents($debugLogPath, "[" . date('Y-m-d H:i:s') . "] fetchUrlContent failed or empty for URL: " . $url . "\n", FILE_APPEND);
        }
        if ($imgData !== false && strlen($imgData) > 0) {
            $tempFile = rtrim($uploadDir, '/') . '/dl_img_' . uniqid();
            file_put_contents($tempFile, $imgData);
            
            $imgInfo = @getimagesize($tempFile);
            if ($imgInfo !== false) {
                $filename = 'prod_' . uniqid() . '.webp';
                if ($this->convertToWebp($tempFile, $uploadDir . $filename)) {
                    @unlink($tempFile);
                    return 'assets/uploads/' . $filename;
                } else {
                    $mime = $imgInfo['mime'] ?? 'image/jpeg';
                    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                    $ext = $extMap[$mime] ?? 'jpg';
                    $filename = 'prod_' . uniqid() . '.' . $ext;
                    if (@copy($tempFile, $uploadDir . $filename)) {
                        @unlink($tempFile);
                        return 'assets/uploads/' . $filename;
                    } else {
                        @file_put_contents($debugLogPath, "[" . date('Y-m-d H:i:s') . "] copy fallback failed for URL: " . $url . "\n", FILE_APPEND);
                    }
                }
            } else {
                @file_put_contents($debugLogPath, "[" . date('Y-m-d H:i:s') . "] getimagesize failed for URL: " . $url . ". First 100 chars: " . substr($imgData, 0, 100) . "\n", FILE_APPEND);
            }
            @unlink($tempFile);
        }

        return null;
    }

    /**
     * Fetch URL content using cURL with fallback to stream context
     */
    private function fetchUrlContent(string $url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HTTPHEADER => [
                    'Accept: image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ]
            ]);
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($data !== false && $httpCode >= 200 && $httpCode < 300) {
                return $data;
            }
        }

        if (ini_get('allow_url_fopen')) {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n" .
                                "Accept: image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8\r\n",
                    'timeout' => 25,
                    'follow_location' => 1
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
            $context = stream_context_create($opts);
            $data = @file_get_contents($url, false, $context);
            if ($data !== false) {
                return $data;
            }
        }

        return false;
    }

    public function apiDispatchVanStock(string $dsrId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $date = $_GET['date'] ?? date('Y-m-d');
        $stock = $this->db->query("
            SELECT vs.product_id, p.name as product_name, SUM(vs.quantity) as qty
            FROM van_stock vs
            JOIN products p ON p.id = vs.product_id
            WHERE vs.dsr_id = " . (int)$dsrId . " AND vs.quantity > 0
            GROUP BY vs.product_id
        ")->fetchAll();
        echo json_encode(['success' => true, 'stock' => $stock]);
        exit;
    }

    public function apiDispatchReturnSave(string $scheduleId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $products = $input['products'] ?? [];

        $sch = $this->db->query("SELECT dsr_id, dispatch_date, delivery_date FROM dispatch_schedules WHERE id = " . (int)$scheduleId)->fetch();
        if (!$sch) {
            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
            exit;
        }

        $dsrId = $sch['dsr_id'];
        $deliv_date = $sch['delivery_date'] ?: $sch['dispatch_date'];

        $wIdRow = $this->db->prepare("SELECT warehouse_id FROM dispatches WHERE dsr_id=? AND dispatch_date=? LIMIT 1");
        $wIdRow->execute([$dsrId, $deliv_date]);
        $wId = $wIdRow->fetchColumn() ?: (\App\Core\Auth::warehouseId() ?: 1);

        $this->db->beginTransaction();
        try {
            if (!empty($products)) {
                $this->db->prepare("INSERT INTO returns (dsr_id, return_date, status) VALUES (?, ?, 'pending')")
                         ->execute([$dsrId, $deliv_date]);
                $returnId = $this->db->lastInsertId();

                foreach ($products as $p) {
                    $pid = (int)$p['id'];
                    $qty = (int)$p['qty'];
                    if ($qty <= 0) continue;
                    
                    $this->db->prepare("UPDATE van_stock SET quantity = GREATEST(0, quantity - ?) WHERE dsr_id = ? AND product_id = ?")
                             ->execute([$qty, $dsrId, $pid]);
                             
                    $pQuery = $this->db->prepare("SELECT name, box_type, pieces_per_box, price FROM products WHERE id=?");
                    $pQuery->execute([$pid]);
                    $pd = $pQuery->fetch(PDO::FETCH_ASSOC);

                    $this->db->prepare("INSERT INTO return_items (return_id, product_id, quantity, reason, product_name, box_type, pieces_per_box, unit_price, buying_price) VALUES (?, ?, ?, 'good', ?, ?, ?, ?, ?)")
                             ->execute([$returnId, $pid, $qty, $pd['name'] ?? null, $pd['box_type'] ?? null, $pd['pieces_per_box'] ?? 1, $pd['price'] ?? 0, $pd['price'] ?? 0]);
                             
                    // Restore to warehouse inventory
                    $invQuery = $this->db->prepare("
                        SELECT i.id, i.qty_boxes, i.qty_pieces, p.pieces_per_box 
                        FROM inventory i 
                        JOIN products p ON p.id = i.product_id 
                        WHERE i.product_id=? AND i.warehouse_id=? AND i.lot_id IS NULL
                    ");
                    $invQuery->execute([$pid, $wId]);
                    $invRow = $invQuery->fetch();
                    
                    if ($invRow) {
                        $ppb = max(1, (int)$invRow['pieces_per_box']);
                        $totalStockPcs = ((int)$invRow['qty_boxes'] * $ppb) + (int)$invRow['qty_pieces'];
                        $newStockPcs = $totalStockPcs + $qty;
                        
                        $newBoxes = floor($newStockPcs / $ppb);
                        $newPcs = $newStockPcs % $ppb;
                        
                        $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                 ->execute([$newBoxes, $newPcs, $invRow['id']]);
                    } else {
                        $pQuery = $this->db->prepare("SELECT pieces_per_box FROM products WHERE id=?");
                        $pQuery->execute([$pid]);
                        $pRow = $pQuery->fetch();
                        $ppb = $pRow ? max(1, (int)$pRow['pieces_per_box']) : 1;
                        
                        $newBoxes = floor($qty / $ppb);
                        $newPcs = $qty % $ppb;
                        
                        $this->db->prepare("INSERT INTO inventory (warehouse_id, product_id, qty_boxes, qty_pieces, lot_id) VALUES (?, ?, ?, ?, NULL)")
                                 ->execute([$wId, $pid, $newBoxes, $newPcs]);
                    }
                }
            }

            $this->db->prepare("UPDATE dispatch_schedules SET status = 'returned' WHERE id = ?")->execute([$scheduleId]);
            
            $this->db->prepare("UPDATE dispatches SET status='returned', updated_at=NOW() WHERE dsr_id=? AND dispatch_date=? AND status IN ('pending', 'in_transit')")
                     ->execute([$dsrId, $deliv_date]);

            // Reset remaining van_stock quantity for this DSR on return completion (preserve initial_qty for historical records & settlements)
            $this->db->prepare("UPDATE van_stock SET quantity = 0 WHERE dsr_id = ?")->execute([$dsrId]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Attendance QR API
    // ══════════════════════════════════════════════════════════
    private function ensureAttendanceQrTable(): void
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS attendance_qr_codes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    qr_code VARCHAR(100) NOT NULL UNIQUE,
                    generated_by INT NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (PDOException $e) {}

        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS dsr_attendance (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    dsr_id INT NOT NULL,
                    attendance_date DATE NOT NULL,
                    scan_time TIME NOT NULL,
                    status ENUM('present','late','absent') NOT NULL,
                    latitude DECIMAL(10,8) DEFAULT NULL,
                    longitude DECIMAL(11,8) DEFAULT NULL,
                    address TEXT DEFAULT NULL,
                    device_info VARCHAR(500) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_daily (dsr_id, attendance_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (PDOException $e) {}
    }

    public function apiAttendanceQrGet(): void
    {
        $this->ensureAttendanceQrTable();
        $row = $this->db->query("SELECT * FROM attendance_qr_codes WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'qr' => $row ?: null], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function apiAttendanceQrGenerate(): void
    {
        $this->ensureAttendanceQrTable();
        $this->db->exec("UPDATE attendance_qr_codes SET is_active=0");
        $code = 'HAPPYBANGLADESH_DSR_ATTENDANCE_' . strtoupper(bin2hex(random_bytes(6)));
        $this->db->prepare("INSERT INTO attendance_qr_codes (qr_code, generated_by, is_active) VALUES (?, ?, 1)")
                 ->execute([$code, Auth::id()]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'qr_code' => $code], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    //  Operations Panel (Correcting SR/DSR Mistakes)
    // ──────────────────────────────────────────────────────────────
    public function operations(): void
    {
        $this->render('operations');
    }

    public function apiOperationsOrders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $wId = Auth::warehouseId();
            // Get orders from last 2 days
            $sql = "SELECT o.*, u.name as sr_name, r.name as retailer_name, r.address as retailer_address 
                    FROM orders o
                    LEFT JOIN users u ON o.sr_id = u.id
                    LEFT JOIN retailers r ON o.retailer_id = r.id
                    WHERE o.warehouse_id = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                    ORDER BY o.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$wId]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $stmt = $this->db->prepare("SELECT oi.*, p.name as product_name, p.pieces_per_box as pack_size 
                                            FROM order_items oi 
                                            JOIN products p ON oi.product_id = p.id 
                                            WHERE oi.order_id = ?");
                $stmt->execute([$order['id']]);
                $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            echo json_encode(['success' => true, 'data' => $orders]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiOperationsDeliveries(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $wId = Auth::warehouseId();
            // Get dispatches from last 2 days
            $sql = "SELECT d.*, o.id as invoice_no, o.total_amount as order_total, u.name as dsr_name, r.name as retailer_name,
                           (o.total_amount - d.paid_amount) as due_amount
                    FROM dispatches d
                    JOIN orders o ON d.order_id = o.id
                    LEFT JOIN users u ON d.dsr_id = u.id
                    LEFT JOIN retailers r ON o.retailer_id = r.id
                    WHERE d.warehouse_id = ? AND d.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                    ORDER BY d.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$wId]);
            $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($deliveries as &$del) {
                $itemsStmt = $this->db->prepare("SELECT di.*, p.name as product_name, p.pieces_per_box as pack_size 
                                                 FROM dispatch_items di 
                                                 JOIN products p ON di.product_id = p.id 
                                                 WHERE di.dispatch_id = ?");
                $itemsStmt->execute([$del['id']]);
                $del['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            echo json_encode(['success' => true, 'data' => $deliveries]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiOperationsEditOrder(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$id;
        $reason = $this->post('reason');
        $orderDateInput = $this->post('order_date');
        $itemsJson = $this->post('items');
        
        if (empty($reason) || empty($itemsJson)) {
            echo json_encode(['success' => false, 'message' => 'Reason and items are required.']);
            exit;
        }
        
        $items = json_decode($itemsJson, true);
        if (!is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Invalid items format.']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $order = $this->db->query("SELECT * FROM orders WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new \Exception("Order not found.");
            }

            // Check if within 2 days
            $orderDate = new \DateTime($order['created_at']);
            $now = new \DateTime();
            if ($now->diff($orderDate)->days > 2) {
                throw new \Exception("Cannot edit orders older than 2 days.");
            }

            $originalDateStr = $orderDate->format('Y-m-d');
            $newDateStr = $orderDateInput ? date('Y-m-d', strtotime($orderDateInput)) : $originalDateStr;
            
            if ($newDateStr !== $originalDateStr) {
                // Check if SR is assigned on the original date OR the new date
                $checkAssign = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM dispatch_schedules ds 
                    JOIN dispatch_schedule_srs dss ON ds.id = dss.schedule_id 
                    WHERE dss.sr_id = ? AND (ds.dispatch_date = ? OR ds.dispatch_date = ?)
                ");
                $checkAssign->execute([$order['sr_id'], $originalDateStr, $newDateStr]);
                $isAssigned = $checkAssign->fetchColumn();

                if ($isAssigned > 0) {
                    throw new \Exception("Cannot change date. The SR is already assigned to a dispatch on the original or new date.");
                }

                // Update created_at with new date but keep original time
                $originalTime = $orderDate->format('H:i:s');
                $newTimestamp = $newDateStr . ' ' . $originalTime;
                $this->db->prepare("UPDATE orders SET created_at = ? WHERE id = ?")->execute([$newTimestamp, $id]);
            }

            $oldTotal = (float)$order['total_amount'];
            $newTotal = 0;

            foreach ($items as $item) {
                $itemId = (int)$item['id'];
                $price = (float)$item['price'];
                $qty = (int)$item['qty'];
                
                $newTotal += ($price * $qty);
                
                $this->db->prepare("UPDATE order_items SET quantity = ?, unit_price = ?, total_price = ? WHERE id = ? AND order_id = ?")
                         ->execute([$qty, $price, $qty * $price, $itemId, $id]);
            }

            // Update order total
            $this->db->prepare("UPDATE orders SET total_amount = ? WHERE id = ?")->execute([$newTotal, $id]);

            $oldLogData = ['total_amount' => $oldTotal];
            $newLogData = ['total_amount' => $newTotal];
            if (isset($newDateStr) && isset($originalDateStr) && $newDateStr !== $originalDateStr) {
                $oldLogData['order_date'] = $originalDateStr;
                $newLogData['order_date'] = $newDateStr;
            }

            // Log the operation
            $logStmt = $this->db->prepare("INSERT INTO operations_logs (action_type, reference_id, manager_id, reason, old_data, new_data) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                'edit_order',
                $id,
                Auth::id(),
                $reason,
                json_encode($oldLogData),
                json_encode($newLogData)
            ]);

            \Helpers::logManagerActivity(\Auth::id(), 'edit_order', 'Edited operation order ID: ' . $id . ' for reason: ' . $reason, $id);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiOperationsBulkChangeOrderDate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $orderIdsRaw = $this->post('order_ids');
        $newDateInput = $this->post('order_date');
        $reason = trim($this->post('reason') ?? '');

        if (empty($orderIdsRaw) || empty($newDateInput) || empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please select orders, enter a valid date, and provide a reason.']);
            exit;
        }

        $orderIds = is_array($orderIdsRaw) ? $orderIdsRaw : json_decode($orderIdsRaw, true);
        if (!is_array($orderIds) || empty($orderIds)) {
            echo json_encode(['success' => false, 'message' => 'No valid orders selected.']);
            exit;
        }

        $newDateStr = date('Y-m-d', strtotime($newDateInput));
        $managerId = Auth::id();
        $updatedCount = 0;
        $errors = [];

        foreach ($orderIds as $orderId) {
            $id = (int)$orderId;
            try {
                $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$order) {
                    $errors[] = "Order #{$id}: Not found.";
                    continue;
                }

                $orderDate = new \DateTime($order['created_at']);
                $originalDateStr = $orderDate->format('Y-m-d');

                if ($originalDateStr === $newDateStr) {
                    $updatedCount++;
                    continue;
                }

                // Check SR dispatch schedule assignment
                $checkAssign = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM dispatch_schedules ds 
                    JOIN dispatch_schedule_srs dss ON ds.id = dss.schedule_id 
                    WHERE dss.sr_id = ? AND (ds.dispatch_date = ? OR ds.dispatch_date = ?)
                ");
                $checkAssign->execute([$order['sr_id'], $originalDateStr, $newDateStr]);
                if ($checkAssign->fetchColumn() > 0) {
                    $errors[] = "Order #{$id}: SR is already assigned to a dispatch on {$originalDateStr} or {$newDateStr}.";
                    continue;
                }

                $originalTime = $orderDate->format('H:i:s');
                $newTimestamp = $newDateStr . ' ' . $originalTime;

                $this->db->beginTransaction();

                $this->db->prepare("UPDATE orders SET created_at = ? WHERE id = ?")->execute([$newTimestamp, $id]);

                $logStmt = $this->db->prepare("INSERT INTO operations_logs (action_type, reference_id, manager_id, reason, old_data, new_data) VALUES (?, ?, ?, ?, ?, ?)");
                $logStmt->execute([
                    'bulk_change_order_date',
                    $id,
                    $managerId,
                    $reason,
                    json_encode(['order_date' => $originalDateStr]),
                    json_encode(['order_date' => $newDateStr])
                ]);

                \Helpers::logManagerActivity($managerId, 'bulk_change_order_date', 'Bulk updated date for Order #' . $id . ' to ' . $newDateStr . ' (Reason: ' . $reason . ')', $id);

                $this->db->commit();
                $updatedCount++;
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $errors[] = "Order #{$id}: " . $e->getMessage();
            }
        }

        if ($updatedCount > 0 && empty($errors)) {
            echo json_encode(['success' => true, 'message' => "Successfully updated date for {$updatedCount} order(s)."]);
        } elseif ($updatedCount > 0 && !empty($errors)) {
            echo json_encode([
                'success' => true,
                'message' => "Updated {$updatedCount} order(s) successfully. Some orders could not be updated:\n" . implode("\n", $errors)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => "Failed to update orders:\n" . implode("\n", $errors)]);
        }
        exit;
    }

    public function apiOperationsDeleteOrder(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$id;
        $reason = $this->post('reason');
        
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Reason is required.']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $order = $this->db->query("SELECT * FROM orders WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new \Exception("Order not found.");
            }

            // Check if within 2 days
            $orderDate = new \DateTime($order['created_at']);
            $now = new \DateTime();
            if ($now->diff($orderDate)->days > 2) {
                throw new \Exception("Cannot delete orders older than 2 days.");
            }

            $orderDateStr = $orderDate->format('Y-m-d');
            
            // Check if SR is assigned on the date
            $checkAssign = $this->db->prepare("
                SELECT COUNT(*) 
                FROM dispatch_schedules ds 
                JOIN dispatch_schedule_srs dss ON ds.id = dss.schedule_id 
                WHERE dss.sr_id = ? AND ds.dispatch_date = ?
            ");
            $checkAssign->execute([$order['sr_id'], $orderDateStr]);
            $isAssigned = $checkAssign->fetchColumn();

            if ($isAssigned > 0) {
                throw new \Exception("Cannot delete order. The SR is already assigned to a dispatch on this date.");
            }

            // Log the operation
            $logStmt = $this->db->prepare("INSERT INTO operations_logs (action_type, reference_id, manager_id, reason, old_data, new_data) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                'delete_order',
                $id,
                Auth::id(),
                $reason,
                json_encode(['order_data' => $order]),
                json_encode(['status' => 'deleted'])
            ]);

            \Helpers::logManagerActivity(\Auth::id(), 'delete_order', 'Deleted operation order ID: ' . $id . ' for reason: ' . $reason, $id);

            // Delete dispatches & dispatch items if any
            $dispatchesStmt = $this->db->prepare("SELECT id FROM dispatches WHERE order_id = ?");
            $dispatchesStmt->execute([$id]);
            $dispatchIds = $dispatchesStmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($dispatchIds)) {
                $inQuery = implode(',', array_fill(0, count($dispatchIds), '?'));
                $this->db->prepare("DELETE FROM dispatch_items WHERE dispatch_id IN ($inQuery)")->execute($dispatchIds);
                $this->db->prepare("DELETE FROM dispatches WHERE order_id = ?")->execute([$id]);
            }

            // Delete order items
            $this->db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);
            
            // Delete order
            $this->db->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiOperationsEditDelivery(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$id;
        $reason = $this->post('reason');
        $paidAmount = (float)$this->post('paid_amount');
        $status = $this->post('status');
        $itemsJson = $this->post('items');
        
        if (empty($reason) || empty($itemsJson)) {
            echo json_encode(['success' => false, 'message' => 'Reason and items are required.']);
            exit;
        }
        
        $items = json_decode($itemsJson, true);
        if (!is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Invalid items format.']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $dispatch = $this->db->query("SELECT * FROM dispatches WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            if (!$dispatch) {
                throw new \Exception("Delivery/Dispatch not found.");
            }

            // Check if within 2 days
            $dispatchDate = new \DateTime($dispatch['created_at']);
            $now = new \DateTime();
            if ($now->diff($dispatchDate)->days > 2) {
                throw new \Exception("Cannot edit deliveries older than 2 days.");
            }

            $oldPaid = (float)$dispatch['paid_amount'];

            foreach ($items as $item) {
                $itemId = (int)$item['id'];
                $qty = (int)$item['qty'];
                
                $oldItem = $this->db->prepare("SELECT product_id, COALESCE(delivered_quantity, 0) as delivered_quantity FROM dispatch_items WHERE id = ? AND dispatch_id = ?");
                $oldItem->execute([$itemId, $id]);
                $oldItemData = $oldItem->fetch(PDO::FETCH_ASSOC);

                if ($oldItemData) {
                    $oldQty = (int)$oldItemData['delivered_quantity'];
                    $productId = (int)$oldItemData['product_id'];
                    $diff = $qty - $oldQty;

                    $this->db->prepare("UPDATE dispatch_items SET delivered_quantity = ? WHERE id = ? AND dispatch_id = ?")
                             ->execute([$qty, $itemId, $id]);

                    if ($diff != 0) {
                        $this->db->prepare("UPDATE van_stock SET quantity = GREATEST(0, CAST(quantity AS SIGNED) - ?) WHERE dsr_id = ? AND product_id = ?")
                                 ->execute([$diff, $dispatch['dsr_id'], $productId]);
                    }
                }
            }

            // Update dispatch paid_amount and status
            $this->db->prepare("UPDATE dispatches SET paid_amount = ?, status = ? WHERE id = ?")->execute([$paidAmount, $status, $id]);

            // Log the operation
            $logStmt = $this->db->prepare("INSERT INTO operations_logs (action_type, reference_id, manager_id, reason, old_data, new_data) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                'edit_delivery',
                $id,
                Auth::id(),
                $reason,
                json_encode(['paid_amount' => $oldPaid]),
                json_encode(['paid_amount' => $paidAmount])
            ]);

            \Helpers::logManagerActivity(\Auth::id(), 'edit_delivery', 'Edited operation delivery ID: ' . $id . ' for reason: ' . $reason, $id);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiOperationsPlaceOrder(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
        exit;
    }

    public function apiOperationsMakeDelivery(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
        exit;
    }
}
