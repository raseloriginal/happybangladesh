<?php

class DispatchController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

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
                    SELECT COALESCE(SUM(oi.quantity * (oi.unit_price - oi.base_selling_price)), 0)
                    FROM dispatch_schedule_srs dss
                    JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$sch['dispatch_date']}'
                    JOIN order_items oi ON oi.order_id = o.id
                    WHERE dss.schedule_id = $sid
                ")->fetchColumn();
                $sch['total_order_oc'] = (float)$orderOC;

                // Calculate dispatch value (dispatch_items base value + negative dispatch_extras)
                $dispatchValStmt = $this->db->prepare("
                    SELECT 
                        (SELECT COALESCE(SUM(di.quantity * COALESCE(di.base_selling_price, p.price)), 0)
                         FROM dispatches d
                         JOIN dispatch_items di ON di.dispatch_id = d.id
                         JOIN products p ON p.id = di.product_id
                         WHERE d.dsr_id = ? AND d.dispatch_date = ? AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL))
                        + 
                        (SELECT COALESCE(SUM(
                             (CAST(de.qty_boxes AS SIGNED) * CAST(p.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED)) * p.price
                         ), 0)
                         FROM dispatch_extras de
                         JOIN products p ON p.id = de.product_id
                         WHERE de.schedule_id = ? AND (de.qty_boxes < 0 OR de.qty_pieces < 0))
                ");
                $dispatchValStmt->execute([$sch['dsr_id'], $delivery_date, $sid]);
                $sch['total_dispatch_value'] = (float)$dispatchValStmt->fetchColumn();

                $dispatchOC = $this->db->query("
                    SELECT COALESCE(SUM(di.quantity * (di.unit_price - di.base_selling_price)), 0)
                    FROM dispatches d
                    JOIN dispatch_items di ON di.dispatch_id = d.id
                    WHERE d.dsr_id = {$sch['dsr_id']} AND d.dispatch_date = '{$delivery_date}' AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)
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
                    SELECT COALESCE(SUM(oi.quantity * oi.base_selling_price), 0)
                    FROM dispatch_schedule_srs dss
                    JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                    JOIN order_items oi ON oi.order_id = o.id
                    JOIN products p ON p.id = oi.product_id
                    WHERE dss.schedule_id = {$scheduleId} AND {$companyCondition}
                ")->fetchColumn();
                $company['ordered_value'] = (float)$orderedVal;

                $dispatchVal = $this->db->query("
                    SELECT 
                        (SELECT COALESCE(SUM(di.quantity * COALESCE(di.base_selling_price, p.price)), 0)
                         FROM dispatches d
                         JOIN dispatch_items di ON di.dispatch_id = d.id
                         JOIN products p ON p.id = di.product_id
                         WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND {$companyCondition})
                        + 
                        (SELECT COALESCE(SUM(
                             (CAST(de.qty_boxes AS SIGNED) * CAST(p.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED)) * p.price
                         ), 0)
                         FROM dispatch_extras de
                         JOIN products p ON p.id = de.product_id
                         WHERE de.schedule_id = {$scheduleId} AND (de.qty_boxes < 0 OR de.qty_pieces < 0) AND {$companyCondition})
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
                           COALESCE((
                               SELECT MAX(COALESCE(oi.base_selling_price, p.price))
                               FROM dispatch_schedule_srs dss
                               JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                               JOIN order_items oi ON oi.order_id = o.id
                               WHERE dss.schedule_id = {$scheduleId} AND oi.product_id = p.id
                           ), p.price) AS base_price,
                           (
                               SELECT COALESCE(SUM(oi.quantity), 0)
                               FROM dispatch_schedule_srs dss
                               JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                               JOIN order_items oi ON oi.order_id = o.id
                               WHERE dss.schedule_id = {$scheduleId} AND oi.product_id = p.id
                           ) as ordered_qty,
                           (
                               COALESCE((
                                   SELECT SUM(di.quantity)
                                   FROM dispatches d
                                   JOIN dispatch_items di ON di.dispatch_id = d.id
                                   WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND di.product_id = p.id AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)
                               ), 0)
                               +
                               COALESCE((
                                   SELECT SUM(CAST(de.qty_boxes AS SIGNED) * CAST(p2.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED))
                                   FROM dispatch_extras de
                                   JOIN products p2 ON p2.id = de.product_id
                                   WHERE de.schedule_id = {$scheduleId} AND de.product_id = p.id AND (de.qty_boxes < 0 OR de.qty_pieces < 0)
                               ), 0)
                           ) as dispatched_qty,
                           (
                               COALESCE((
                                   SELECT SUM(di.quantity * COALESCE(di.base_selling_price, p2.price))
                                   FROM dispatches d
                                   JOIN dispatch_items di ON di.dispatch_id = d.id
                                   JOIN products p2 ON p2.id = di.product_id
                                   WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND di.product_id = p.id AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)
                               ), 0)
                               +
                               COALESCE((
                                   SELECT SUM((CAST(de.qty_boxes AS SIGNED) * CAST(p2.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED)) * p2.price)
                                   FROM dispatch_extras de
                                   JOIN products p2 ON p2.id = de.product_id
                                   WHERE de.schedule_id = {$scheduleId} AND de.product_id = p.id AND (de.qty_boxes < 0 OR de.qty_pieces < 0)
                               ), 0)
                           ) as dispatched_value,
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
                               SELECT COALESCE(SUM(oi.quantity * oi.base_selling_price), 0)
                               FROM orders o
                               JOIN order_items oi ON oi.order_id = o.id
                               JOIN products p ON p.id = oi.product_id
                               WHERE o.sr_id = u.id AND DATE(o.created_at) = '{$dispatchDate}' AND {$companyCondition}
                           ) as base_order_value,
                           (
                               SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
                               FROM orders o
                               JOIN order_items oi ON oi.order_id = o.id
                               JOIN products p ON p.id = oi.product_id
                               WHERE o.sr_id = u.id AND DATE(o.created_at) = '{$dispatchDate}' AND {$companyCondition}
                           ) as order_value,
                           (
                               SELECT COALESCE(SUM(di.delivered_quantity * di.base_selling_price), 0)
                               FROM dispatch_items di
                               JOIN products p ON p.id = di.product_id
                               JOIN dispatches d ON d.id = di.dispatch_id
                               LEFT JOIN orders o ON o.id = d.order_id
                               LEFT JOIN order_items oi ON oi.order_id = d.order_id AND oi.product_id = di.product_id
                               WHERE d.dispatch_date = '{$deliveryDate}' AND o.sr_id = u.id AND {$companyCondition}
                           ) as base_sale_value,
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
                    HAVING order_value > 0 OR sale_value > 0 OR base_order_value > 0
                    ORDER BY u.name ASC
                ")->fetchAll();

                foreach ($srs as &$sr) {
                    $srId = (int)$sr['id'];
                    $sr['products'] = $this->db->query("
                        SELECT p.name,
                               MAX(COALESCE(oi.base_selling_price, p.price)) as base_price,
                               SUM(oi.quantity) as ordered_qty,
                               SUM(oi.quantity * COALESCE(oi.base_selling_price, p.price)) as total_base_order_value,
                               SUM(oi.quantity * (oi.unit_price - COALESCE(oi.base_selling_price, p.price))) as total_oc
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
                    // Also include 'dispatched' orders to support re-dispatch after a dispatch-clear or re-organize
                    $orders = $this->db->prepare("
                        SELECT o.id, o.warehouse_id 
                        FROM orders o 
                        JOIN dispatch_schedule_srs dss ON dss.sr_id = o.sr_id
                        WHERE dss.schedule_id = ? AND DATE(o.created_at) = ? AND o.status IN ('pending', 'confirmed', 'dispatched')
                    ");
                    $orders->execute([$id, $date]);
                    $ordersList = $orders->fetchAll();
                    
                    foreach ($ordersList as $o) {
                        $checkEx = $this->db->prepare("SELECT id FROM dispatches WHERE order_id=?");
                        $checkEx->execute([$o['id']]);
                        if ($checkEx->fetch()) {
                            continue; // Skip if already dispatched
                        }

                        $this->db->prepare("INSERT INTO dispatches (order_id, dsr_id, warehouse_id, dispatch_date, status) VALUES (?, ?, ?, ?, 'pending')")
                                 ->execute([$o['id'], $dsrId, $o['warehouse_id'], $deliv_date]);
                        $dispatchId = $this->db->lastInsertId();
                        
                        $items = $this->db->prepare("SELECT * FROM order_items WHERE order_id=?");
                        $items->execute([$o['id']]);
                        foreach($items->fetchAll() as $item) {
                            $this->db->prepare("INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, product_name, box_type, pieces_per_box, unit_price, base_selling_price, buying_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                                     ->execute([$dispatchId, $item['product_id'], $item['lot_id'], $item['quantity'], $item['product_name'], $item['box_type'], $item['pieces_per_box'], $item['unit_price'], $item['base_selling_price'] ?? $item['unit_price'], $item['buying_price'] ?? $item['unit_price'], $item['total_price']]);
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
                            
                            $this->db->prepare("INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, product_name, box_type, pieces_per_box, unit_price, base_selling_price, buying_price, total_price) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)")
                                     ->execute([$extraDispatchId, $ex['product_id'], $qty, $pd['name'], $pd['box_type'], $pd['pieces_per_box'], $pd['price'], $pd['price'], $pd['buying_price'] ?? $pd['price'], $qty * $pd['price']]);
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


    public function apiDispatchUndoDispatch(string $id): void
        {
            header('Content-Type: application/json; charset=utf-8');
            $id = (int)$id;
            
            $sch = $this->db->query("SELECT dsr_id, dispatch_date, delivery_date, status FROM dispatch_schedules WHERE id = " . $id)->fetch();
            if (!$sch) {
                echo json_encode(['success' => false, 'message' => 'Schedule not found']);
                exit;
            }
            
            if ($sch['status'] !== 'dispatched') {
                echo json_encode(['success' => false, 'message' => 'Cannot undo a schedule that is not dispatched']);
                exit;
            }

            $dsrId = $sch['dsr_id'];
            $date = $sch['dispatch_date'];
            $deliv_date = $sch['delivery_date'] ?: $date;

            $this->db->beginTransaction();
            try {
                // 1. Get all dispatch_items under 'in_transit' dispatches for this DSR on delivery date
                $q = $this->db->prepare("
                    SELECT di.product_id, di.lot_id, d.warehouse_id, SUM(di.quantity) as total_qty
                    FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    WHERE d.dsr_id=? AND d.dispatch_date=? AND d.status='in_transit'
                    GROUP BY di.product_id, di.lot_id, d.warehouse_id
                ");
                $q->execute([$dsrId, $deliv_date]);
                $itemsToUndo = $q->fetchAll();

                // 2. Get dispatch_extras adjustments for this schedule (negative = manager reduced van load)
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
                    if ($adj < 0) { // Only negative (reduction)
                        $extrasAdjMap[(int)$exRow['product_id']] = ($extrasAdjMap[(int)$exRow['product_id']] ?? 0) + $adj;
                    }
                }

                // 3. Apply negative adjustments to get actual organized qty
                $appliedProducts = [];
                foreach ($itemsToUndo as &$item) {
                    $pid = (int)$item['product_id'];
                    if (!isset($appliedProducts[$pid]) && isset($extrasAdjMap[$pid])) {
                        $item['total_qty'] = max(0, (int)$item['total_qty'] + $extrasAdjMap[$pid]);
                        $appliedProducts[$pid] = true;
                    }
                }
                unset($item);

                // 4. Reverse van_stock and warehouse inventory
                foreach ($itemsToUndo as $item) {
                    if ((int)$item['total_qty'] <= 0) continue;

                    // 4.a Deduct quantity and initial_qty from van_stock
                    $lotCondition = $item['lot_id'] === null ? "IS NULL" : "= ?";
                    $params = [$item['total_qty'], $item['total_qty'], $dsrId, $item['product_id']];
                    if ($item['lot_id'] !== null) $params[] = $item['lot_id'];

                    $this->db->prepare("
                        UPDATE van_stock 
                        SET quantity = GREATEST(0, quantity - ?), 
                            initial_qty = GREATEST(0, initial_qty - ?) 
                        WHERE dsr_id = ? AND product_id = ? AND lot_id $lotCondition
                    ")->execute($params);

                    // Clean up van_stock rows that became 0
                    $cleanParams = [$dsrId, $item['product_id']];
                    if ($item['lot_id'] !== null) $cleanParams[] = $item['lot_id'];
                    $this->db->prepare("
                        DELETE FROM van_stock 
                        WHERE dsr_id = ? AND product_id = ? AND lot_id $lotCondition AND quantity = 0 AND initial_qty = 0
                    ")->execute($cleanParams);

                    // 4.b Restore to warehouse inventory
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
                        $newStockPcs = $totalStockPcs + (int)$item['total_qty']; // Restore

                        $newBoxes = floor($newStockPcs / $ppb);
                        $newPcs = $newStockPcs % $ppb;

                        $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                 ->execute([$newBoxes, $newPcs, $invRow['id']]);
                    }
                }

                // 5. Mark dispatches as pending
                $this->db->prepare("UPDATE dispatches SET status='pending', updated_at=NOW() WHERE dsr_id=? AND dispatch_date=? AND status='in_transit'")
                         ->execute([$dsrId, $deliv_date]);

                // 6. Mark schedule status back to organized
                $this->db->prepare("UPDATE dispatch_schedules SET status = 'organized' WHERE id = ?")->execute([$id]);

                $this->db->commit();
                \Helpers::logManagerActivity(\Auth::id(), 'dispatch_status_undo', "Undid dispatch for schedule $id", $id);
                echo json_encode(['success' => true, 'message' => 'Dispatch has been successfully reverted to Organized.']);
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }


    public function apiDispatchUpdateProductQty(string $scheduleId): void
        {
            header('Content-Type: application/json; charset=utf-8');
            $input = json_decode(file_get_contents('php://input'), true);
            $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            $newDispatchedQty = isset($input['new_dispatched_qty']) ? max(0, (int)$input['new_dispatched_qty']) : 0;

            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product.']);
                exit;
            }

            $sch = $this->db->query("SELECT id, dsr_id, dispatch_date, delivery_date, status FROM dispatch_schedules WHERE id = " . (int)$scheduleId)->fetch();
            if (!$sch) {
                echo json_encode(['success' => false, 'message' => 'Schedule not found.']);
                exit;
            }

            if ($sch['status'] === 'returned') {
                echo json_encode(['success' => false, 'message' => 'রিটার্ন সম্পন্ন হওয়া শিডিউলের ডিসপ্যাচ কোয়ান্টিটি পরিবর্তন করা যাবে না।']);
                exit;
            }

            $scheduleIdInt = (int)$sch['id'];
            $dsrId = (int)$sch['dsr_id'];
            $dispatchDate = $sch['dispatch_date'];
            $deliveryDate = $sch['delivery_date'] ?: $dispatchDate;
            $status = $sch['status'];

            $product = $this->db->query("SELECT id, name, pieces_per_box, box_type, price, buying_price FROM products WHERE id = {$productId}")->fetch();
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                exit;
            }

            $ppb = max(1, (int)$product['pieces_per_box']);
            $productPrice = (float)$product['price'];
            $productBuyingPrice = (float)($product['buying_price'] ?? $productPrice);
            $boxType = $product['box_type'] ?: 'Box';

            // 1. Calculate ordered quantity for this product in this schedule
            $orderedQty = (int)$this->db->query("
                SELECT COALESCE(SUM(oi.quantity), 0)
                FROM dispatch_schedule_srs dss
                JOIN orders o ON o.sr_id = dss.sr_id AND DATE(o.created_at) = '{$dispatchDate}'
                JOIN order_items oi ON oi.order_id = o.id
                WHERE dss.schedule_id = {$scheduleIdInt} AND oi.product_id = {$productId}
            ")->fetchColumn();

            // 2. Calculate already delivered/sale quantity for this product
            $alreadyDeliveredQty = (int)$this->db->query("
                SELECT COALESCE(SUM(di.delivered_quantity), 0)
                FROM dispatches d
                JOIN dispatch_items di ON di.dispatch_id = d.id
                WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND di.product_id = {$productId}
            ")->fetchColumn();

            // Check safety rule: cannot dispatch less than already delivered/sold
            if ($newDispatchedQty < $alreadyDeliveredQty) {
                echo json_encode([
                    'success' => false, 
                    'message' => "ইতোমধ্যে {$alreadyDeliveredQty} পিস বিক্রি/ডেলিভারি হয়ে গেছে। ডিসপ্যাচ কোয়ান্টিটি এর চেয়ে কম ({$newDispatchedQty}) করা যাবে না।"
                ]);
                exit;
            }

            // 3. Calculate current dispatched quantity
            $currentRegularDispatched = (int)$this->db->query("
                SELECT COALESCE(SUM(di.quantity), 0)
                FROM dispatches d
                JOIN dispatch_items di ON di.dispatch_id = d.id
                WHERE d.dsr_id = {$dsrId} AND d.dispatch_date = '{$deliveryDate}' AND di.product_id = {$productId} AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)
            ")->fetchColumn();

            $currentNegativeExtras = (int)$this->db->query("
                SELECT COALESCE(SUM(CAST(de.qty_boxes AS SIGNED) * CAST(p2.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED)), 0)
                FROM dispatch_extras de
                JOIN products p2 ON p2.id = de.product_id
                WHERE de.schedule_id = {$scheduleIdInt} AND de.product_id = {$productId} AND (de.qty_boxes < 0 OR de.qty_pieces < 0)
            ")->fetchColumn();

            if ($currentRegularDispatched == 0 && $status === 'assigned') {
                $currentExtras = (int)$this->db->query("
                    SELECT COALESCE(SUM(CAST(de.qty_boxes AS SIGNED) * CAST(p2.pieces_per_box AS SIGNED) + CAST(de.qty_pieces AS SIGNED)), 0)
                    FROM dispatch_extras de
                    JOIN products p2 ON p2.id = de.product_id
                    WHERE de.schedule_id = {$scheduleIdInt} AND de.product_id = {$productId}
                ")->fetchColumn();
                $currentDispatchedQty = max(0, $orderedQty + $currentExtras);
            } else {
                $currentDispatchedQty = max(0, $currentRegularDispatched + $currentNegativeExtras);
            }

            $diffQty = $newDispatchedQty - $currentDispatchedQty;
            if ($diffQty == 0) {
                echo json_encode(['success' => true, 'message' => 'কোনো পরিবর্তন করা হয়নি।']);
                exit;
            }

            // Determine warehouse ID
            $wIdRow = $this->db->prepare("SELECT warehouse_id FROM dispatches WHERE dsr_id=? AND dispatch_date=? AND warehouse_id IS NOT NULL LIMIT 1");
            $wIdRow->execute([$dsrId, $deliveryDate]);
            $wId = $wIdRow->fetchColumn() ?: (\App\Core\Auth::warehouseId() ?: 1);

            $this->db->beginTransaction();
            try {
                // If already dispatched, adjust physical inventory and van stock
                if ($status === 'dispatched') {
                    if ($diffQty > 0) {
                        // Check warehouse inventory
                        $invQuery = $this->db->prepare("
                            SELECT i.id, i.qty_boxes, i.qty_pieces, p.pieces_per_box 
                            FROM inventory i 
                            JOIN products p ON p.id = i.product_id 
                            WHERE i.product_id=? AND i.warehouse_id=? AND i.lot_id IS NULL
                        ");
                        $invQuery->execute([$productId, $wId]);
                        $invRow = $invQuery->fetch();

                        $invPpb = $invRow ? max(1, (int)$invRow['pieces_per_box']) : $ppb;
                        $totalStockPcs = $invRow ? (((int)$invRow['qty_boxes'] * $invPpb) + (int)$invRow['qty_pieces']) : 0;

                        if ($totalStockPcs < $diffQty) {
                            $this->db->rollBack();
                            echo json_encode([
                                'success' => false, 
                                'message' => "ওয়্যারহাউজে পর্যাপ্ত স্টক নেই। অতিরিক্ত প্রয়োজন: {$diffQty} পিস, ওয়্যারহাউজে আছে: {$totalStockPcs} পিস।"
                            ]);
                            exit;
                        }

                        // Deduct from warehouse
                        $newStockPcs = $totalStockPcs - $diffQty;
                        $newBoxes = floor($newStockPcs / $invPpb);
                        $newPcs = $newStockPcs % $invPpb;
                        if ($invRow) {
                            $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                     ->execute([$newBoxes, $newPcs, $invRow['id']]);
                        }

                        // Add to van_stock
                        $vsCheck = $this->db->prepare("SELECT id FROM van_stock WHERE dsr_id = ? AND product_id = ? AND lot_id IS NULL LIMIT 1");
                        $vsCheck->execute([$dsrId, $productId]);
                        $vsRow = $vsCheck->fetch();
                        if ($vsRow) {
                            $this->db->prepare("UPDATE van_stock SET quantity = quantity + ?, initial_qty = initial_qty + ?, loaded_at = ? WHERE id = ?")
                                     ->execute([$diffQty, $diffQty, $deliveryDate, $vsRow['id']]);
                        } else {
                            $this->db->prepare("INSERT INTO van_stock (dsr_id, product_id, lot_id, quantity, initial_qty, loaded_at) VALUES (?, ?, NULL, ?, ?, ?)")
                                     ->execute([$dsrId, $productId, $diffQty, $diffQty, $deliveryDate]);
                        }
                    } elseif ($diffQty < 0) {
                        $reduceQty = abs($diffQty);

                        // Deduct from van_stock
                        $this->db->prepare("
                            UPDATE van_stock 
                            SET quantity = GREATEST(0, quantity - ?), 
                                initial_qty = GREATEST(0, initial_qty - ?) 
                            WHERE dsr_id = ? AND product_id = ? AND lot_id IS NULL
                        ")->execute([$reduceQty, $reduceQty, $dsrId, $productId]);

                        // Restore to warehouse inventory
                        $invQuery = $this->db->prepare("
                            SELECT i.id, i.qty_boxes, i.qty_pieces, p.pieces_per_box 
                            FROM inventory i 
                            JOIN products p ON p.id = i.product_id 
                            WHERE i.product_id=? AND i.warehouse_id=? AND i.lot_id IS NULL
                        ");
                        $invQuery->execute([$productId, $wId]);
                        $invRow = $invQuery->fetch();

                        $invPpb = $invRow ? max(1, (int)$invRow['pieces_per_box']) : $ppb;
                        $totalStockPcs = $invRow ? (((int)$invRow['qty_boxes'] * $invPpb) + (int)$invRow['qty_pieces']) : 0;
                        $newStockPcs = $totalStockPcs + $reduceQty;

                        $newBoxes = floor($newStockPcs / $invPpb);
                        $newPcs = $newStockPcs % $invPpb;

                        if ($invRow) {
                            $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                     ->execute([$newBoxes, $newPcs, $invRow['id']]);
                        } else {
                            $this->db->prepare("INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces) VALUES (?, ?, NULL, ?, ?)")
                                     ->execute([$wId, $productId, $newBoxes, $newPcs]);
                        }
                    }
                }

                // Update dispatch_extras and extra dispatch_items
                $extraDiff = $newDispatchedQty - $orderedQty;

                // Clear old dispatch_extras for this schedule & product
                $this->db->prepare("DELETE FROM dispatch_extras WHERE schedule_id = ? AND product_id = ?")
                         ->execute([$scheduleIdInt, $productId]);

                // Clear old extra dispatch_item for this product where order_id IS NULL
                $extraDispCheck = $this->db->prepare("
                    SELECT di.id, di.dispatch_id 
                    FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id
                    WHERE d.dsr_id = ? AND d.dispatch_date = ? AND d.order_id IS NULL AND di.product_id = ?
                ");
                $extraDispCheck->execute([$dsrId, $deliveryDate, $productId]);
                $oldExtraItem = $extraDispCheck->fetch();
                if ($oldExtraItem) {
                    $this->db->prepare("DELETE FROM dispatch_items WHERE id = ?")->execute([$oldExtraItem['id']]);
                }

                if ($extraDiff > 0) {
                    // Positive extra
                    $extraBoxes = floor($extraDiff / $ppb);
                    $extraPcs = $extraDiff % $ppb;
                    $this->db->prepare("INSERT INTO dispatch_extras (schedule_id, product_id, qty_boxes, qty_pieces) VALUES (?, ?, ?, ?)")
                             ->execute([$scheduleIdInt, $productId, $extraBoxes, $extraPcs]);

                    if ($status === 'organized' || $status === 'dispatched') {
                        // Ensure extra dispatch record exists
                        $extraDispatchId = $this->db->query("
                            SELECT id FROM dispatches 
                            WHERE dsr_id = {$dsrId} AND dispatch_date = '{$deliveryDate}' AND order_id IS NULL 
                            LIMIT 1
                        ")->fetchColumn();

                        $dispStatus = ($status === 'dispatched') ? 'in_transit' : 'pending';

                        if (!$extraDispatchId) {
                            $this->db->prepare("INSERT INTO dispatches (order_id, dsr_id, warehouse_id, dispatch_date, status) VALUES (NULL, ?, ?, ?, ?)")
                                     ->execute([$dsrId, $wId, $deliveryDate, $dispStatus]);
                            $extraDispatchId = $this->db->lastInsertId();
                        }

                        // Insert extra dispatch item
                        $this->db->prepare("
                            INSERT INTO dispatch_items (dispatch_id, product_id, lot_id, quantity, product_name, box_type, pieces_per_box, unit_price, base_selling_price, buying_price, total_price) 
                            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)
                        ")->execute([
                            $extraDispatchId, $productId, $extraDiff, $product['name'], $boxType, $ppb,
                            $productPrice, $productPrice, $productBuyingPrice, $extraDiff * $productPrice
                        ]);
                    }
                } elseif ($extraDiff < 0) {
                    // Negative adjustment
                    $absDiff = abs($extraDiff);
                    $negBoxes = -floor($absDiff / $ppb);
                    $negPcs = -($absDiff % $ppb);
                    $this->db->prepare("INSERT INTO dispatch_extras (schedule_id, product_id, qty_boxes, qty_pieces) VALUES (?, ?, ?, ?)")
                             ->execute([$scheduleIdInt, $productId, $negBoxes, $negPcs]);
                }

                $this->db->commit();
                \Helpers::logManagerActivity(\Auth::id(), 'dispatch_qty_update', "Updated product {$productId} dispatch qty to {$newDispatchedQty} (diff: {$diffQty}) for schedule {$scheduleIdInt}", $scheduleIdInt);

                echo json_encode([
                    'success' => true, 
                    'message' => 'ডিসপ্যাচ কোয়ান্টিটি সফলভাবে আপডেট করা হয়েছে।',
                    'new_dispatched_qty' => $newDispatchedQty,
                    'diff_qty' => $diffQty
                ]);
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
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

                        $this->db->prepare("INSERT INTO return_items (return_id, product_id, quantity, reason, product_name, box_type, pieces_per_box, unit_price, base_selling_price, buying_price) VALUES (?, ?, ?, 'good', ?, ?, ?, ?, ?, ?)")
                                 ->execute([$returnId, $pid, $qty, $pd['name'] ?? null, $pd['box_type'] ?? null, $pd['pieces_per_box'] ?? 1, $pd['price'] ?? 0, $pd['price'] ?? 0, $pd['buying_price'] ?? $pd['price'] ?? 0]);
                                 
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


    public function apiDispatchUndoReturn(string $scheduleId): void
        {
            header('Content-Type: application/json; charset=utf-8');
            $scheduleId = (int)$scheduleId;

            $sch = $this->db->query("SELECT dsr_id, dispatch_date, delivery_date, status FROM dispatch_schedules WHERE id = " . $scheduleId)->fetch();
            if (!$sch) {
                echo json_encode(['success' => false, 'message' => 'Schedule not found']);
                exit;
            }

            if ($sch['status'] !== 'returned') {
                echo json_encode(['success' => false, 'message' => 'Schedule is not in returned status']);
                exit;
            }

            $dsrId = $sch['dsr_id'];
            $deliv_date = $sch['delivery_date'] ?: $sch['dispatch_date'];

            $wIdRow = $this->db->prepare("SELECT warehouse_id FROM dispatches WHERE dsr_id=? AND dispatch_date=? LIMIT 1");
            $wIdRow->execute([$dsrId, $deliv_date]);
            $wId = $wIdRow->fetchColumn() ?: (\App\Core\Auth::warehouseId() ?: 1);

            $this->db->beginTransaction();
            try {
                // Find return record for this DSR on delivery date
                $retQuery = $this->db->prepare("SELECT id FROM returns WHERE dsr_id = ? AND return_date = ? ORDER BY id DESC LIMIT 1");
                $retQuery->execute([$dsrId, $deliv_date]);
                $returnId = $retQuery->fetchColumn();

                if ($returnId) {
                    // Fetch returned items
                    $itemsStmt = $this->db->prepare("SELECT product_id, quantity FROM return_items WHERE return_id = ?");
                    $itemsStmt->execute([$returnId]);
                    $returnItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($returnItems as $ri) {
                        $pid = (int)$ri['product_id'];
                        $qty = (int)$ri['quantity'];
                        if ($qty <= 0) continue;

                        // 1. Deduct from warehouse inventory (reverse inventory restore)
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
                            $currTotalPcs = ((int)$invRow['qty_boxes'] * $ppb) + (int)$invRow['qty_pieces'];
                            $newStockPcs = max(0, $currTotalPcs - $qty);

                            $newBoxes = floor($newStockPcs / $ppb);
                            $newPcs = $newStockPcs % $ppb;

                            $this->db->prepare("UPDATE inventory SET qty_boxes = ?, qty_pieces = ? WHERE id = ?")
                                     ->execute([$newBoxes, $newPcs, $invRow['id']]);
                        }

                        // 2. Restore returned quantities to van_stock
                        $vsCheck = $this->db->prepare("SELECT id FROM van_stock WHERE dsr_id = ? AND product_id = ? LIMIT 1");
                        $vsCheck->execute([$dsrId, $pid]);
                        $vsId = $vsCheck->fetchColumn();

                        if ($vsId) {
                            $this->db->prepare("UPDATE van_stock SET quantity = quantity + ? WHERE id = ?")
                                     ->execute([$qty, $vsId]);
                        }
                    }

                    // Delete return items and return record
                    $this->db->prepare("DELETE FROM return_items WHERE return_id = ?")->execute([$returnId]);
                    $this->db->prepare("DELETE FROM returns WHERE id = ?")->execute([$returnId]);
                }

                // 3. Update dispatch_schedules status back to 'dispatched'
                $this->db->prepare("UPDATE dispatch_schedules SET status = 'dispatched' WHERE id = ?")->execute([$scheduleId]);

                // 4. Update dispatches status back to 'in_transit'
                $this->db->prepare("UPDATE dispatches SET status = 'in_transit', updated_at = NOW() WHERE dsr_id = ? AND dispatch_date = ? AND status = 'returned'")
                         ->execute([$dsrId, $deliv_date]);

                $this->db->commit();
                echo json_encode(['success' => true, 'message' => 'রিটার্ন সফলভাবে রিভার্ট (Undo) করা হয়েছে।']);
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }


}
