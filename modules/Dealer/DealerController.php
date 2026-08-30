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
    public function dashboard(): void
    {
        $this->checkAuth();
        
        $dealerId = $_SESSION['dealer_id'];
        $warehouseId = $_SESSION['dealer_warehouse_id'];

        // Get linked companies
        $companies = $this->getDealerCompanies($dealerId);
        $companyIds = array_column($companies, 'company_id');
        
        if (empty($companyIds)) {
            $companyIdsStr = '0'; 
        } else {
            $companyIdsStr = implode(',', $companyIds);
        }

        $stats = [
            'total_products'  => $this->db->query("SELECT COUNT(*) FROM products WHERE status=1 AND company_id IN ($companyIdsStr)")->fetchColumn(),
            'total_inventory' => $this->db->prepare("SELECT COALESCE(SUM(i.qty_boxes),0) FROM inventory i JOIN products p ON i.product_id = p.id WHERE i.warehouse_id=? AND p.company_id IN ($companyIdsStr)")->execute([$warehouseId]) ? $this->db->query("SELECT COALESCE(SUM(i.qty_boxes),0) FROM inventory i JOIN products p ON i.product_id = p.id WHERE i.warehouse_id=" . (int)$warehouseId . " AND p.company_id IN ($companyIdsStr)")->fetchColumn() : 0,
            'recent_orders' => $this->db->query("SELECT COUNT(*) FROM orders WHERE dealer_id = $dealerId AND status != 'cancelled'")->fetchColumn(),
        ];

        $this->render('dashboard', compact('stats'));
    }

    // ══════════════════════════════════════════════════════════
    //  Transactions
    // ══════════════════════════════════════════════════════════
    public function transactions(): void
    {
        $this->checkAuth();
        $dealerId = $_SESSION['dealer_id'];
        
        // Fetch orders and dispatches related to this dealer
        // To keep it simple, we just fetch orders placed by/for this dealer.
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as sr_name, r.name as retailer_name
            FROM orders o
            LEFT JOIN users u ON o.sr_id = u.id
            LEFT JOIN retailers r ON o.retailer_id = r.id
            WHERE o.dealer_id = ?
            ORDER BY o.created_at DESC LIMIT 50
        ");
        $stmt->execute([$dealerId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('transactions', compact('transactions'));
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
        
        $stmt = $this->db->prepare("SELECT happy_commission FROM dealers WHERE id = ?");
        $stmt->execute([$dealerId]);
        $dealer = $stmt->fetch(PDO::FETCH_ASSOC);
        $commissionRate = $dealer ? (float)$dealer['happy_commission'] : 0;
        
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(id) as total_orders, SUM(total_amount) as total_sales
            FROM orders
            WHERE dealer_id = ? AND status = 'delivered'
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) DESC
        ");
        $stmt->execute([$dealerId]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->render('profit_report', compact('reports', 'commissionRate'));
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
}
