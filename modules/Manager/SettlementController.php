<?php

class SettlementController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

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
                    SELECT COALESCE(SUM(COALESCE(di.delivered_quantity, 0) * (COALESCE(di.unit_price, p.price) - di.base_selling_price)), 0)
                    FROM dispatches d
                    JOIN dispatch_items di ON d.id = di.dispatch_id
                    JOIN products p ON p.id = di.product_id
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


}
