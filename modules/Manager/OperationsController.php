<?php

class OperationsController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

    public function operations(): void
        {
            $wId = Auth::warehouseId();
            
            $q = $this->db->prepare("
                SELECT p.*, c.name AS company_name, p.pieces_per_box AS pieces_per_carton, p.pieces_per_box as ppb
                FROM products p
                LEFT JOIN companies c ON c.id=p.company_id
                WHERE p.status=1
                ORDER BY p.name
            ");
            $q->execute();
            $allProducts = $q->fetchAll(PDO::FETCH_ASSOC);

            $this->render('operations', ['allProducts' => $allProducts]);
        }


    public function apiOperationsOrders(): void
        {
            header('Content-Type: application/json; charset=utf-8');
            try {
                $wId = Auth::warehouseId();
                
                $dateFilter = $_GET['date'] ?? date('Y-m-d');
                $srIdFilter = $_GET['sr_id'] ?? '';
                
                $whereSql = "WHERE o.warehouse_id = ?";
                $params = [$wId];
                
                if (!empty($dateFilter)) {
                    $whereSql .= " AND DATE(o.created_at) = ?";
                    $params[] = $dateFilter;
                }
                if (!empty($srIdFilter)) {
                    $whereSql .= " AND o.sr_id = ?";
                    $params[] = $srIdFilter;
                }

                // Get SRs for filter dropdown
                $srStmt = $this->db->prepare("SELECT id, name FROM users WHERE role_id = (SELECT id FROM roles WHERE slug = 'sr' LIMIT 1)");
                $srStmt->execute();
                $srs = $srStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get orders
                $sql = "SELECT o.*, u.name as sr_name, d.name AS dealer_name, r.name as retailer_name, r.phone as retailer_phone, r.address as retailer_address,
                               (SELECT COUNT(*) FROM dispatch_schedules ds 
                                JOIN dispatch_schedule_srs dss ON ds.id = dss.schedule_id 
                                WHERE dss.sr_id = o.sr_id AND ds.dispatch_date = DATE(o.created_at)) as is_assigned
                        FROM orders o
                        LEFT JOIN users u ON o.sr_id = u.id
                        LEFT JOIN dealers d ON d.id = o.dealer_id
                        LEFT JOIN retailers r ON o.retailer_id = r.id
                        $whereSql
                        ORDER BY o.created_at DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get products (exact same fields as SR panel)
                foreach ($orders as &$order) {
                    $stmt = $this->db->prepare("
                        SELECT oi.*, p.name AS product_name, p.image AS product_image, p.pieces_per_box, p.box_type, oi.base_selling_price AS base_price, c.name AS company_name
                        FROM order_items oi
                        JOIN products p ON p.id = oi.product_id
                        LEFT JOIN companies c ON c.id = p.company_id
                        WHERE oi.order_id = ?
                    ");
                    $stmt->execute([$order['id']]);
                    $order['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC); // SR panel uses 'products' key
                }
                
                echo json_encode(['success' => true, 'data' => $orders, 'srs' => $srs]);
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


    public function apiOperationsDsrDeliveries(): void
        {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            try {
                $wId = Auth::warehouseId();
                $date = $_GET['date'] ?? date('Y-m-d');
                $dsrId = $_GET['dsr_id'] ?? '';

                // Get DSRs list
                $dsrsStmt = $this->db->query("
                    SELECT u.id, u.name, u.phone 
                    FROM users u 
                    JOIN roles r ON r.id = u.role_id 
                    WHERE r.slug = 'dsr' AND u.status = 1 
                    ORDER BY u.name
                ");
                $dsrs = $dsrsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Build SQL
                $whereSql = "WHERE d.warehouse_id = ? AND DATE(d.created_at) = ?";
                $params = [$wId, $date];

                if (!empty($dsrId)) {
                    $whereSql .= " AND d.dsr_id = ?";
                    $params[] = $dsrId;
                }

                $sql = "SELECT d.id as dispatch_id, d.order_id, d.dsr_id, d.status, d.paid_amount, d.is_ready_sale, d.created_at as dispatch_date,
                               o.total_amount as order_total, o.is_ready_sale as order_ready_sale,
                               r.id as retailer_id, r.name as retailer_name, r.phone as retailer_phone, r.address as retailer_address, r.lat as latitude, r.lng as longitude,
                               u.name as dsr_name
                        FROM dispatches d
                        JOIN orders o ON o.id = d.order_id
                        JOIN retailers r ON r.id = o.retailer_id
                        LEFT JOIN users u ON u.id = d.dsr_id
                        $whereSql
                        ORDER BY d.id DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($dispatches as &$del) {
                    // Determine display status and color code
                    $status = strtolower($del['status'] ?? 'pending');
                    $paid = (float)($del['paid_amount'] ?? 0);
                    $total = (float)($del['order_total'] ?? 0);

                    if ($status === 'canceled' || $status === 'cancelled') {
                        $del['status_code'] = 'canceled';
                        $del['status_label'] = 'Canceled';
                        $del['color'] = 'red';
                    } elseif ($status === 'delivered' && $paid >= $total && $total > 0) {
                        $del['status_code'] = 'delivered';
                        $del['status_label'] = 'Delivered (Full)';
                        $del['color'] = 'green';
                    } elseif ($status === 'partial' || ($status === 'delivered' && $paid < $total)) {
                        $del['status_code'] = 'partial';
                        $del['status_label'] = 'Partial / Due';
                        $del['color'] = 'orange';
                    } else {
                        $del['status_code'] = 'pending';
                        $del['status_label'] = 'Pending';
                        $del['color'] = 'blue';
                    }

                    // Fetch items
                    $itemsStmt = $this->db->prepare("
                        SELECT di.*, p.name as product_name, p.buying_price, p.dealer_percentage, p.image as product_image,
                               COALESCE(c.id, 0) as company_id, COALESCE(c.name, 'Uncategorized') as company_name
                        FROM dispatch_items di
                        JOIN products p ON p.id = di.product_id
                        LEFT JOIN companies c ON c.id = p.company_id
                        WHERE di.dispatch_id = ?
                    ");
                    $itemsStmt->execute([$del['dispatch_id']]);
                    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($items as &$item) {
                        $bp = (float)($item['buying_price'] ?? 0);
                        $dp = (float)($item['dealer_percentage'] ?? 0);
                        $basePrice = $bp + ($bp * ($dp / 100.0));
                        $unitPrice = (float)($item['unit_price'] ?? 0);

                        $item['base_price'] = $basePrice;
                        $item['oc_per_unit'] = $unitPrice - $basePrice;
                        $item['order_qty'] = (int)($item['quantity'] ?? 0);
                        $item['delivered_qty'] = isset($item['delivered_quantity']) && $item['delivered_quantity'] !== null 
                            ? (int)$item['delivered_quantity'] 
                            : (int)($item['quantity'] ?? 0);

                        // Fetch current van stock for product if DSR ID exists
                        $vanStock = 0;
                        if (!empty($del['dsr_id'])) {
                            $stockStmt = $this->db->prepare("
                                SELECT COALESCE(SUM(quantity), 0) FROM van_stock WHERE dsr_id = ? AND product_id = ?
                            ");
                            $stockStmt->execute([$del['dsr_id'], $item['product_id']]);
                            $vanStock = (int)($stockStmt->fetchColumn() ?: 0);
                        }
                        $item['van_stock'] = $vanStock;
                    }

                    $del['items'] = $items;
                }

                echo json_encode(['success' => true, 'dsrs' => $dsrs, 'deliveries' => $dispatches]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }


    public function apiOperationsDsrDeliveryAction(): void
        {
            header('Content-Type: application/json; charset=utf-8');
            try {
                $dispatchId = (int)($_POST['dispatch_id'] ?? 0);
                $action = trim($_POST['action'] ?? '');
                $itemsJson = $_POST['items'] ?? '[]';
                $paidAmountInput = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : null;

                if ($dispatchId <= 0 || empty($action)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid dispatch ID or action.']);
                    exit;
                }

                $items = json_decode($itemsJson, true) ?: [];

                $this->db->beginTransaction();

                $dispStmt = $this->db->prepare("SELECT * FROM dispatches WHERE id = ?");
                $dispStmt->execute([$dispatchId]);
                $dispatch = $dispStmt->fetch(PDO::FETCH_ASSOC);

                if (!$dispatch) {
                    throw new \Exception("Dispatch record not found.");
                }

                $orderId = (int)$dispatch['order_id'];

                // Calculate updated total based on items and adjust van_stock
                $newTotalAmount = 0;
                foreach ($items as $item) {
                    $itemId = (int)($item['id'] ?? 0);
                    $qty = (int)($item['qty'] ?? 0);
                    $unitPrice = (float)($item['unit_price'] ?? 0);

                    if ($itemId > 0) {
                        $oldItem = $this->db->prepare("SELECT product_id, COALESCE(delivered_quantity, 0) as delivered_quantity FROM dispatch_items WHERE id = ? AND dispatch_id = ?");
                        $oldItem->execute([$itemId, $dispatchId]);
                        $oldItemData = $oldItem->fetch(PDO::FETCH_ASSOC);

                        if ($oldItemData) {
                            $oldQty = (int)$oldItemData['delivered_quantity'];
                            $productId = (int)$oldItemData['product_id'];
                            $diff = $qty - $oldQty;

                            $upItem = $this->db->prepare("UPDATE dispatch_items SET delivered_quantity = ? WHERE id = ? AND dispatch_id = ?");
                            $upItem->execute([$qty, $itemId, $dispatchId]);

                            if ($diff != 0) {
                                $this->db->prepare("UPDATE van_stock SET quantity = GREATEST(0, CAST(quantity AS SIGNED) - ?) WHERE dsr_id = ? AND product_id = ?")
                                         ->execute([$diff, $dispatch['dsr_id'], $productId]);
                            }
                        } else {
                            $upItem = $this->db->prepare("UPDATE dispatch_items SET delivered_quantity = ? WHERE id = ? AND dispatch_id = ?");
                            $upItem->execute([$qty, $itemId, $dispatchId]);
                        }

                        $newTotalAmount += ($qty * $unitPrice);
                    }
                }

                if (empty($items)) {
                    // If items not sent, fetch current total from orders
                    $ordRow = $this->db->query("SELECT total_amount FROM orders WHERE id = $orderId")->fetch();
                    $newTotalAmount = (float)($ordRow['total_amount'] ?? $dispatch['paid_amount']);
                }

                if ($action === 'complete') {
                    $upDisp = $this->db->prepare("UPDATE dispatches SET status = 'delivered', paid_amount = ? WHERE id = ?");
                    $upDisp->execute([$newTotalAmount, $dispatchId]);
                    $this->db->prepare("UPDATE orders SET status = 'delivered', total_amount = ? WHERE id = ?")->execute([$newTotalAmount, $orderId]);
                } elseif ($action === 'cancel') {
                    $upDisp = $this->db->prepare("UPDATE dispatches SET status = 'cancelled', paid_amount = 0 WHERE id = ?");
                    $upDisp->execute([$dispatchId]);
                    $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$orderId]);
                } elseif ($action === 'undo') {
                    $upDisp = $this->db->prepare("UPDATE dispatches SET status = 'pending', paid_amount = 0 WHERE id = ?");
                    $upDisp->execute([$dispatchId]);
                    $this->db->prepare("UPDATE orders SET status = 'pending' WHERE id = ?")->execute([$orderId]);
                } elseif ($action === 'continue_partial') {
                    $paid = $paidAmountInput !== null ? $paidAmountInput : 0.00;
                    $status = ($paid >= $newTotalAmount && $newTotalAmount > 0) ? 'delivered' : 'partial';
                    $upDisp = $this->db->prepare("UPDATE dispatches SET status = ?, paid_amount = ? WHERE id = ?");
                    $upDisp->execute([$status, $paid, $dispatchId]);
                    $this->db->prepare("UPDATE orders SET status = ?, total_amount = ? WHERE id = ?")->execute([$status, $newTotalAmount, $orderId]);
                } elseif ($action === 'modify') {
                    $paid = $paidAmountInput !== null ? $paidAmountInput : (float)$dispatch['paid_amount'];
                    $status = ($paid >= $newTotalAmount && $newTotalAmount > 0) ? 'delivered' : 'partial';
                    $upDisp = $this->db->prepare("UPDATE dispatches SET status = ?, paid_amount = ? WHERE id = ?");
                    $upDisp->execute([$status, $paid, $dispatchId]);
                    $this->db->prepare("UPDATE orders SET status = ?, total_amount = ? WHERE id = ?")->execute([$status, $newTotalAmount, $orderId]);
                } else {
                    throw new \Exception("Unsupported action.");
                }

                $this->db->commit();
                echo json_encode(['success' => true, 'message' => 'Delivery action processed successfully.']);
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
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
                // 1. Check if SR is assigned on the original date
                $checkAssign = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM dispatch_schedules ds 
                    JOIN dispatch_schedule_srs dss ON ds.id = dss.schedule_id 
                    WHERE dss.sr_id = ? AND ds.dispatch_date = ?
                ");
                $checkAssign->execute([$order['sr_id'], $originalDateStr]);
                if ($checkAssign->fetchColumn() > 0) {
                    throw new \Exception("Cannot edit order. The SR is already assigned to a dispatch on this date.");
                }
                
                if ($newDateStr !== $originalDateStr) {
                    // 2. Check if the NEW date is assigned
                    $checkAssign->execute([$order['sr_id'], $newDateStr]);
                    if ($checkAssign->fetchColumn() > 0) {
                        throw new \Exception("Cannot change date. The SR is already assigned to a dispatch on the new date.");
                    }

                    // Update created_at with new date but keep original time
                    $originalTime = $orderDate->format('H:i:s');
                    $newTimestamp = $newDateStr . ' ' . $originalTime;
                    $this->db->prepare("UPDATE orders SET created_at = ? WHERE id = ?")->execute([$newTimestamp, $id]);
                }

                $oldTotal = (float)$order['total_amount'];
                $newTotal = 0;

                // Delete old items
                $this->db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);

                // Insert updated items
                $insStmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, base_selling_price, total_price, product_name, box_type, pieces_per_box, buying_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($items as $item) {
                    $pid = (int)($item['product_id'] ?? $item['id']);
                    $price = (float)$item['price'];
                    $qty = (int)$item['qty'];
                    
                    if ($qty <= 0) continue;

                    $newTotal += ($price * $qty);
                    
                    // Fetch product details for insertion
                    $pdStmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
                    $pdStmt->execute([$pid]);
                    $pd = $pdStmt->fetch(PDO::FETCH_ASSOC);

                    if ($pd) {
                        $basePrice = (float)($pd['buying_price'] + ($pd['buying_price'] * ($pd['dealer_percentage'] / 100)));
                        $insStmt->execute([
                            $id,
                            $pid,
                            $qty,
                            $price,
                            $basePrice,
                            $qty * $price,
                            $pd['name'],
                            $pd['box_type'] ?? 'Piece',
                            $pd['pieces_per_box'] ?? 1,
                            $pd['buying_price']
                        ]);
                    }
                }

                // Update order total
                $this->db->prepare("UPDATE orders SET total_amount = ? WHERE id = ?")->execute([$newTotal, $id]);

                // Refresh order data to return
                $stmt = $this->db->prepare("
                    SELECT o.*, u.name as sr_name, d.name AS dealer_name, r.name as retailer_name, r.phone as retailer_phone, r.address as retailer_address 
                    FROM orders o
                    LEFT JOIN users u ON o.sr_id = u.id
                    LEFT JOIN dealers d ON d.id = o.dealer_id
                    LEFT JOIN retailers r ON o.retailer_id = r.id
                    WHERE o.id = ?
                ");
                $stmt->execute([$id]);
                $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($updatedOrder) {
                    $stmt = $this->db->prepare("
                        SELECT oi.*, p.name AS product_name, p.image AS product_image, p.pieces_per_box, p.box_type, oi.base_selling_price AS base_price, c.name AS company_name
                        FROM order_items oi
                        JOIN products p ON p.id = oi.product_id
                        LEFT JOIN companies c ON c.id = p.company_id
                        WHERE oi.order_id = ?
                    ");
                    $stmt->execute([$id]);
                    $updatedOrder['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

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
                echo json_encode(['success' => true, 'order' => $updatedOrder ?? null]);
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

                // Delete dispatches & dispatch items if any, restoring van_stock if previously delivered
                $dispatchesStmt = $this->db->prepare("SELECT id, dsr_id FROM dispatches WHERE order_id = ?");
                $dispatchesStmt->execute([$id]);
                $dispatches = $dispatchesStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($dispatches)) {
                    foreach ($dispatches as $disp) {
                        $dispItems = $this->db->prepare("SELECT product_id, COALESCE(delivered_quantity, 0) as delivered_quantity FROM dispatch_items WHERE dispatch_id = ?");
                        $dispItems->execute([$disp['id']]);
                        foreach ($dispItems->fetchAll(PDO::FETCH_ASSOC) as $di) {
                            $delQty = (int)$di['delivered_quantity'];
                            if ($delQty > 0) {
                                $this->db->prepare("UPDATE van_stock SET quantity = quantity + ? WHERE dsr_id = ? AND product_id = ?")
                                         ->execute([$delQty, $disp['dsr_id'], $di['product_id']]);
                            }
                        }
                    }
                    $dispatchIds = array_column($dispatches, 'id');
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


}
