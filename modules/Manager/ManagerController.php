<?php

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


}
