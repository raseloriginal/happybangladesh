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
    //  Products CRUD
    // ══════════════════════════════════════════════════════════
    public function products(): void
    {
        $wid = Auth::warehouseId();
        $items = $this->db->query("
            SELECT p.*, c.name AS company_name, cat.name AS category_name,
                   IFNULL(SUM(i.qty_boxes), 0) AS stock_boxes,
                   IFNULL(SUM(i.qty_pieces), 0) AS stock_pieces
            FROM products p
            LEFT JOIN companies c ON c.id = p.company_id
            LEFT JOIN categories cat ON cat.id = p.category_id
            LEFT JOIN inventory i ON i.product_id = p.id AND i.warehouse_id = $wid
            WHERE p.status=1
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ")->fetchAll();
        $companies = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $categories = $this->db->query("SELECT * FROM categories WHERE status=1 ORDER BY name")->fetchAll();
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

        // Build an ordered list of row indices to pair items with uploaded files
        $rowIndices = isset($_POST['row_indices']) ? (array)$_POST['row_indices'] : [];

        $uploadDir = PUB_PATH . '/assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $this->db->beginTransaction();
        try {
            foreach ($items as $i => $p) {
                // Handle image upload for this row
                $imagePath = null;
                $rowIdx = $rowIndices[$i] ?? null;
                if ($rowIdx !== null && !empty($_FILES['images']['tmp_name'][$rowIdx])) {
                    $ext = strtolower(pathinfo($_FILES['images']['name'][$rowIdx], PATHINFO_EXTENSION));
                    $filename = 'prod_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['images']['tmp_name'][$rowIdx], $uploadDir . $filename);
                    $imagePath = 'assets/uploads/' . $filename;
                }

                $sku = 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $this->db->prepare("INSERT INTO products (company_id, category_id, name, sku, box_type, pieces_per_box, dealer_percentage, buying_price, image) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([
                        $input['company_id'] ?: null,
                        $p['category_id'] ?: null,
                        trim($p['name']),
                        $sku,
                        $p['box_type'] ?: 'বক্স',
                        $p['pieces_per_box'] ?: 1,
                        $p['dealer_percentage'] ?: 0,
                        0,
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
                $filename = 'prod_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                $image = 'assets/uploads/' . $filename;
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
            echo json_encode(['success' => true]);
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
            SELECT c.*, co.name as company_name 
            FROM categories c 
            LEFT JOIN companies co ON co.id=c.company_id 
            WHERE c.status=1 
            ORDER BY c.id DESC
        ")->fetchAll();
        $companies = $this->db->query('SELECT id, name FROM companies WHERE status=1 ORDER BY name')->fetchAll();
        $this->render('categories/index', compact('items', 'companies'));
    }

    public function apiCategoryStore(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['names'])) exit;
        
        $cid = $input['company_id'] ?: null;
        $stmt = $this->db->prepare("INSERT INTO categories (company_id, name) VALUES (?, ?)");
        
        foreach ($input['names'] as $name) {
            $stmt->execute([$cid, $name]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function apiCategoryUpdate(): void
    {
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && !empty($input['id'])) {
            $this->db->prepare("UPDATE categories SET company_id=?, name=? WHERE id=?")
                     ->execute([$input['company_id'] ?: null, trim($input['name']), $input['id']]);
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
        $items = $this->db->query("
            SELECT l.*, p.name AS product_name, p.pieces_per_box
            FROM lots l JOIN products p ON p.id = l.product_id
            ORDER BY l.created_at DESC
        ")->fetchAll();
        $products = $this->db->query("
            SELECT p.id, p.name, p.sku, p.company_id, p.image, p.pieces_per_box, p.box_type,
                   COALESCE(SUM(i.qty_boxes), 0) AS stock_boxes,
                   COALESCE(SUM(i.qty_pieces), 0) AS stock_pieces
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id AND i.warehouse_id = " . (int)Auth::warehouseId() . "
            WHERE p.status=1
            GROUP BY p.id
            ORDER BY p.name
        ")->fetchAll();
        $companies = $this->db->query("SELECT id, name FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('lots/index', compact('items', 'products', 'companies'));
    }

    public function apiLotStore(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['lots'])) {
            echo json_encode(['success' => false, 'message' => 'No lots provided']); exit;
        }

        $lot_date = !empty($input['lot_date']) ? $input['lot_date'] : date('Y-m-d');
        $wid = Auth::warehouseId();

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

                if (!$product_id || !$qty_pieces) continue;

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
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']); exit;
        }

        $wid = Auth::warehouseId();
        $this->db->beginTransaction();
        try {
            // 1. Fetch old lot to revert its inventory contribution
            $old = $this->db->prepare("SELECT product_id, qty_boxes FROM lots WHERE id=?");
            $old->execute([$input['id']]);
            $oldLot = $old->fetch();

            if ($oldLot) {
                $this->db->prepare(
                    "UPDATE inventory SET qty_boxes = GREATEST(0, qty_boxes - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$oldLot['qty_boxes'], $oldLot['product_id'], $wid, $input['id']]);
            }

            // 2. Update the lot row
            $this->db->prepare(
                "UPDATE lots SET product_id=?, expiry_date=?, qty_boxes=?, buying_price=?, lot_date=? WHERE id=?"
            )->execute([
                $input['product_id'],
                $input['expiry_date'] ?: null,
                $input['qty_boxes'] ?? 0,
                $input['buying_price'] ?? 0,
                $input['lot_date'] ?? date('Y-m-d'),
                $input['id']
            ]);

            // 3. Re-apply inventory
            $new_qty   = (int)($input['qty_boxes'] ?? 0);
            $new_price = (float)($input['buying_price'] ?? 0);
            $pid       = (int)$input['product_id'];

            $this->db->prepare(
                "INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces)
                 VALUES (?,?,?,?,0)
                 ON DUPLICATE KEY UPDATE qty_boxes = qty_boxes + VALUES(qty_boxes)"
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
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']); exit;
        }

        $wid = Auth::warehouseId();
        $this->db->beginTransaction();
        try {
            // Get lot data before deleting
            $lot = $this->db->prepare("SELECT product_id, qty_boxes FROM lots WHERE id=?");
            $lot->execute([$input['id']]);
            $lotData = $lot->fetch();

            if ($lotData) {
                // Reduce inventory, cap at 0
                $this->db->prepare(
                    "UPDATE inventory SET qty_boxes = GREATEST(0, qty_boxes - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                )->execute([$lotData['qty_boxes'], $lotData['product_id'], $wid, $input['id']]);
            }

            $this->db->prepare("DELETE FROM lots WHERE id=?")->execute([$input['id']]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Inventory (view only)
    // ══════════════════════════════════════════════════════════
    public function inventory(): void
    {
        $items = $this->db->query("
            SELECT i.*, p.name AS product_name, p.sku, w.name AS warehouse_name, l.lot_number
            FROM inventory i
            JOIN products p ON p.id = i.product_id
            JOIN warehouses w ON w.id = i.warehouse_id
            LEFT JOIN lots l ON l.id = i.lot_id
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
            $orderVal = $this->db->query("
                SELECT COALESCE(SUM(o.total_amount), 0)
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$sch['dispatch_date']}'
                WHERE dss.schedule_id = $sid
            ")->fetchColumn();
            
            $sch['total_dispatch_value'] = (float)$orderVal;
            $sch['total_return_value'] = 0;
            $sch['total_damage_value'] = 0;
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
            SELECT u.id, u.name, u.avatar, COUNT(o.id) as order_count
            FROM users u 
            JOIN roles r ON r.id = u.role_id 
            JOIN orders o ON o.sr_id = u.id AND DATE(o.created_at) = ?
            WHERE r.slug = 'sr' AND u.status = 1
            AND u.id NOT IN (
                SELECT sr_id FROM dispatch_schedule_srs dss 
                JOIN dispatch_schedules ds ON ds.id = dss.schedule_id 
                WHERE ds.dispatch_date = ?
            )
            GROUP BY u.id
        ");
        $srs->execute([$date, $date]);
        $srsList = $srs->fetchAll();
        
        echo json_encode(['dsrs' => $dsrs, 'srs' => $srsList]);
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
            foreach ($assignments as $dsr_id => $sr_ids) {
                if (empty($sr_ids)) continue;
                $stmt = $this->db->prepare("INSERT INTO dispatch_schedules (dsr_id, dispatch_date, status) VALUES (?, ?, 'assigned')");
                $stmt->execute([$dsr_id, $date]);
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

    public function apiDispatchSrDetails(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $schedule = $this->db->query("SELECT dispatch_date FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();
        if (!$schedule) exit;

        $srs = $this->db->query("
            SELECT u.id, u.name,
                   (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE sr_id = u.id AND DATE(created_at) = '{$schedule['dispatch_date']}') as orders_value,
                   0 as dispatch_items_value,
                   0 as return_items_value,
                   0 as damage_value
            FROM dispatch_schedule_srs dss
            JOIN users u ON u.id = dss.sr_id
            WHERE dss.schedule_id = " . (int)$id . "
        ")->fetchAll();
        
        foreach ($srs as &$sr) {
            $sr['products'] = $this->db->query("
                SELECT p.name, SUM(oi.quantity) as ordered_qty, 0 as extra_qty, 0 as dispatched_qty, 0 as returned_qty, 0 as damage_value, SUM(oi.total_price) as sale_value
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.id
                JOIN products p ON p.id = oi.product_id
                WHERE o.sr_id = {$sr['id']} AND DATE(o.created_at) = '{$schedule['dispatch_date']}'
                GROUP BY p.id
            ")->fetchAll();
        }

        echo json_encode($srs);
        exit;
    }

    public function apiDispatchOrganizeData(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $schedule = $this->db->query("SELECT dispatch_date FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();
        
        $products = $this->db->query("
            SELECT p.id as product_id, p.name, p.image, p.pieces_per_box, 
                   SUM(oi.quantity) as total_ordered_qty
            FROM dispatch_schedule_srs dss
            JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$schedule['dispatch_date']}'
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            WHERE dss.schedule_id = " . (int)$id . "
            GROUP BY p.id
        ")->fetchAll();
        
        echo json_encode($products);
        exit;
    }

    public function apiDispatchOrganizeSave(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $extras = $input['extras'] ?? []; 
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO dispatch_extras (schedule_id, product_id, qty_boxes, qty_pieces) VALUES (?, ?, ?, ?)");
            foreach ($extras as $ex) {
                if ($ex['boxes'] > 0 || $ex['pcs'] > 0) {
                    $stmt->execute([$id, $ex['product_id'], $ex['boxes'], $ex['pcs']]);
                }
            }
            $this->db->prepare("UPDATE dispatch_schedules SET status = 'organized' WHERE id = ?")->execute([$id]);
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
        $this->db->prepare("UPDATE dispatch_schedules SET status = ? WHERE id = ?")->execute([$status, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Returns
    // ══════════════════════════════════════════════════════════
    public function returns(): void
    {
        $items = $this->db->query("
            SELECT r.*, u.name AS dsr_name
            FROM returns r
            LEFT JOIN users u ON u.id = r.dsr_id
            ORDER BY r.created_at DESC
        ")->fetchAll();
        $this->render('returns', compact('items'));
    }

    public function returnApprove(string $id): void
    {
        $this->db->prepare("UPDATE returns SET status='approved' WHERE id=?")->execute([$id]);
        $this->flash('success', 'Return approved.'); $this->redirect('manager/returns');
    }

    // ══════════════════════════════════════════════════════════
    //  Attendance
    // ══════════════════════════════════════════════════════════
    public function attendance(): void
    {
        $date  = $this->get('date', date('Y-m-d'));
        $items = $this->db->prepare("
            SELECT a.*, u.name AS user_name, r.name AS role_name
            FROM attendance a
            JOIN users u ON u.id = a.user_id
            JOIN roles r ON r.id = u.role_id
            WHERE a.date = ?
            ORDER BY u.name
        ");
        $items->execute([$date]);
        $items = $items->fetchAll();

        $users = $this->db->query("
            SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id=u.role_id
            WHERE r.slug IN ('sr','dsr') AND u.status=1 ORDER BY u.name
        ")->fetchAll();

        $this->render('attendance', compact('items', 'users', 'date'));
    }

    public function attendanceStore(): void
    {
        $this->verifyCsrf();
        $userId = $this->post('user_id');
        $date   = $this->post('date', date('Y-m-d'));
        $status = $this->post('status', 'present');
        $checkIn  = $this->post('check_in') ?: null;
        $checkOut = $this->post('check_out') ?: null;

        // Upsert
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
        $this->db->prepare("INSERT INTO readysales (warehouse_id,product_id,lot_id,quantity,price) VALUES (?,?,?,?,?)")
                 ->execute([$this->post('warehouse_id'), $this->post('product_id'), $this->post('lot_id') ?: null, $this->post('quantity',0), $this->post('price',0)]);
        $this->flash('success', 'Ready sale record added.'); $this->redirect('manager/readysale');
    }
}
