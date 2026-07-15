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

        $q = $this->db->prepare("SELECT COALESCE(SUM(quantity),0) FROM van_stock WHERE dsr_id=?"); $q->execute([$dsrId]);
        $stats['van_stock_items'] = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status='in_transit'"); $q->execute([$dsrId]);
        $stats['active_deliveries'] = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE dsr_id=? AND date=CURDATE()"); $q->execute([$dsrId]);
        $stats['today_expenses'] = $q->fetchColumn();

        $q = $this->db->prepare("SELECT COUNT(*) FROM dispatches WHERE dsr_id=? AND status='delivered' AND DATE(updated_at)=CURDATE()"); $q->execute([$dsrId]);
        $stats['delivered_today'] = $q->fetchColumn();

        $vanStock = $this->db->prepare("
            SELECT vs.*, p.name AS product_name, p.sku
            FROM van_stock vs JOIN products p ON p.id=vs.product_id
            WHERE vs.dsr_id=? ORDER BY p.name LIMIT 5
        ");
        $vanStock->execute([$dsrId]);
        $vanStock = $vanStock->fetchAll();

        $this->render('dashboard', compact('stats', 'vanStock'));
    }

    public function scanner(): void
    {
        $this->render('scanner');
    }

    public function scan(): void
    {
        $code = trim($this->post('code', ''));
        if (empty($code)) {
            $this->json(['success' => false, 'message' => 'No code provided.']);
            return;
        }

        // Look up product by SKU or lot number
        $product = $this->db->prepare("SELECT p.*, c.name AS company_name FROM products p LEFT JOIN companies c ON c.id=p.company_id WHERE p.sku=? LIMIT 1");
        $product->execute([$code]);
        $product = $product->fetch();

        if ($product) {
            $this->json(['success' => true, 'type' => 'product', 'data' => $product]);
            return;
        }

        $lot = $this->db->prepare("SELECT l.*, p.name AS product_name FROM lots l JOIN products p ON p.id=l.product_id WHERE l.lot_number=? LIMIT 1");
        $lot->execute([$code]);
        $lot = $lot->fetch();

        if ($lot) {
            $this->json(['success' => true, 'type' => 'lot', 'data' => $lot]);
            return;
        }

        $this->json(['success' => false, 'message' => "No product or lot found for code: {$code}"]);
    }

    public function vanStock(): void
    {
        $dsrId = Auth::id();
        $items = $this->db->prepare("
            SELECT vs.*, p.name AS product_name, p.sku, l.lot_number, l.expiry_date
            FROM van_stock vs
            JOIN products p ON p.id = vs.product_id
            LEFT JOIN lots l ON l.id = vs.lot_id
            WHERE vs.dsr_id = ?
            ORDER BY p.name
        ");
        $items->execute([$dsrId]);
        $items = $items->fetchAll();
        $this->render('van_stock', compact('items'));
    }

    public function expenses(): void
    {
        $dsrId = Auth::id();
        $items = $this->db->prepare("SELECT * FROM expenses WHERE dsr_id=? ORDER BY date DESC, created_at DESC");
        $items->execute([$dsrId]);
        $items = $items->fetchAll();
        $this->render('expenses', compact('items'));
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
        $items = $this->db->prepare("
            SELECT d.*, o.total_amount, o.notes AS order_notes
            FROM dispatches d
            LEFT JOIN orders o ON o.id=d.order_id
            WHERE d.dsr_id=?
            ORDER BY d.created_at DESC
        ");
        $items->execute([$dsrId]);
        $items = $items->fetchAll();
        $this->render('delivery', compact('items'));
    }

    public function deliveryUpdate(string $id): void
    {
        $this->verifyCsrf();
        $status = $this->post('status', 'delivered');
        $this->db->prepare("UPDATE dispatches SET status=?, updated_at=NOW() WHERE id=? AND dsr_id=?")
                 ->execute([$status, $id, Auth::id()]);
        $this->flash('success', 'Delivery status updated.'); $this->redirect('dsr/delivery');
    }
}
