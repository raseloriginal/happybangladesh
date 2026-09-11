<?php

class ProductController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

    public function products(): void
        {
            $wid = Auth::warehouseId();
            $items = $this->db->query("
                SELECT p.*, c.name AS company_name, cat.name AS category_name,
                       (
                           CAST(COALESCE((SELECT SUM(CAST(qty_boxes AS SIGNED) * CAST(p.pieces_per_box AS SIGNED) + CAST(qty_pieces AS SIGNED)) FROM lots WHERE product_id = p.id), 0) AS SIGNED)
                           -
                           CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled' AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)), 0) AS SIGNED)
                           +
                           CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0) AS SIGNED)
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
                    $newProdId = (int)$this->db->lastInsertId();
                    if ($buyingPrice > 0) {
                        $dp = (float)($p['dealer_percentage'] ?: 0);
                        $initSellingPrice = round($buyingPrice * (1 + $dp / 100) / $piecesPerBox, 2);
                        \Helpers::logProductPriceChange(
                            $newProdId,
                            null,
                            $buyingPrice,
                            null,
                            $initSellingPrice,
                            \Auth::id(),
                            'initial_creation',
                            'Initial product creation'
                        );
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
                $stmt = $this->db->prepare("SELECT name, buying_price, price, pieces_per_box, dealer_percentage FROM products WHERE id=?");
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

                if ($new_buying_price != (float)$p['buying_price'] || $selling_price != (float)$p['price']) {
                    \Helpers::logProductPriceChange(
                        $pid,
                        (float)$p['buying_price'],
                        $new_buying_price,
                        (float)$p['price'],
                        $selling_price,
                        \Auth::id(),
                        'manual_adjust',
                        $input['reason'] ?? 'Manual price adjustment by Manager'
                    );
                }

                echo json_encode(['success' => true, 'new_buying_price' => $new_buying_price, 'new_selling_price' => $selling_price]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }


    public function apiProductPriceHistory(): void
        {
            header('Content-Type: application/json; charset=utf-8');
            $productId = (int)($_GET['product_id'] ?? 0);
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Product ID is required']);
                exit;
            }

            try {
                $stmtProd = $this->db->prepare("SELECT id, name, sku, buying_price, price, pieces_per_box, dealer_percentage FROM products WHERE id = ?");
                $stmtProd->execute([$productId]);
                $product = $stmtProd->fetch();

                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    exit;
                }

                $stmt = $this->db->prepare("
                    SELECT h.*, u.name as user_name, r.name as role_name
                    FROM product_price_history h
                    LEFT JOIN users u ON u.id = h.changed_by
                    LEFT JOIN roles r ON r.id = u.role_id
                    WHERE h.product_id = ?
                    ORDER BY h.created_at DESC, h.id DESC
                ");
                $stmt->execute([$productId]);
                $history = $stmt->fetchAll();

                echo json_encode([
                    'success' => true,
                    'product' => $product,
                    'history' => $history
                ]);
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
                    $prod = $this->db->prepare("SELECT buying_price, price, pieces_per_box, dealer_percentage FROM products WHERE id=?");
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

                        if ($buying_price != (float)$p['buying_price'] || $selling_price != (float)$p['price']) {
                            \Helpers::logProductPriceChange(
                                $product_id,
                                (float)$p['buying_price'],
                                $buying_price,
                                (float)$p['price'],
                                $selling_price,
                                \Auth::id(),
                                'lot_entry',
                                "Updated via Lot Entry (Date: {$lot_date})"
                            );
                        }
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
                $prod = $this->db->prepare("SELECT buying_price, price, pieces_per_box, dealer_percentage FROM products WHERE id=?");
                $prod->execute([$pid]);
                $p = $prod->fetch();
                if ($p) {
                    $ppb = max(1, (float)$p['pieces_per_box']);
                    $dp  = (float)$p['dealer_percentage'];
                    $selling_price = round($new_price * (1 + $dp / 100) / $ppb, 2);
                    $this->db->prepare("UPDATE products SET buying_price=?, price=? WHERE id=?")
                             ->execute([$new_price, $selling_price, $pid]);

                    if ($new_price != (float)$p['buying_price'] || $selling_price != (float)$p['price']) {
                        \Helpers::logProductPriceChange(
                            $pid,
                            (float)$p['buying_price'],
                            $new_price,
                            (float)$p['price'],
                            $selling_price,
                            \Auth::id(),
                            'lot_edit',
                            "Updated via Lot Edit (Lot ID: {$input['id']})"
                        );
                    }
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
                    CURLOPT_SSL_VERIFYPEER => true,
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
}
