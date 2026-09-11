<?php

class AdminController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check(ROLE_ADMIN);
        $this->viewPath = MOD_PATH . '/Admin/views';
        $this->db = Database::getInstance();
    }

    public function dashboard(): void
        {
            $stats = [
                'total_users'       => $this->db->query("SELECT COUNT(*) FROM users WHERE status=1")->fetchColumn(),
                'total_managers'    => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='manager' AND u.status=1")->fetchColumn(),
                'total_srs'         => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='sr' AND u.status=1")->fetchColumn(),
                'total_dsrs'        => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='dsr' AND u.status=1")->fetchColumn(),
                'total_products'    => $this->db->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn(),
                'total_companies'   => $this->db->query("SELECT COUNT(*) FROM companies WHERE status=1")->fetchColumn(),
                'total_dealers'     => $this->db->query("SELECT COUNT(*) FROM dealers WHERE status=1")->fetchColumn(),
                'pending_orders'    => $this->db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
                'pending_approvals' => $this->db->query("SELECT COUNT(*) FROM approvals WHERE status='pending'")->fetchColumn(),
                'total_warehouses'  => $this->db->query("SELECT COUNT(*) FROM warehouses WHERE status=1")->fetchColumn(),
                'today_expenses'    => $this->db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date=CURDATE()")->fetchColumn(),
                'today_attendance'  => $this->db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='present'")->fetchColumn(),
            ];

            $recentOrders = $this->db->query("
                SELECT o.*, u.name AS sr_name, d.name AS dealer_name
                FROM orders o
                LEFT JOIN users u ON u.id = o.sr_id
                LEFT JOIN dealers d ON d.id = o.dealer_id
                ORDER BY o.created_at DESC LIMIT 8
            ")->fetchAll();

            $recentLogs = $this->db->query("
                SELECT l.*, u.name AS user_name
                FROM activity_logs l
                LEFT JOIN users u ON u.id = l.user_id
                ORDER BY l.created_at DESC LIMIT 8
            ")->fetchAll();

            $pageTitle = 'Dashboard';
            $this->render('dashboard', compact('stats', 'recentOrders', 'recentLogs', 'pageTitle'));
        }


    public function aiAssistant(): void
        {
            $pageTitle = 'AI Assistant';
            $this->render('ai_assistant', compact('pageTitle'));
        }


    public function aiAssistantApi(): void
        {
            header('Content-Type: application/json');
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                $action = $input['action'] ?? '';
                
                $prompt = "";
                
                if ($action === 'analyze_sales') {
                    $sales = $this->db->query("
                        SELECT DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as orders_count
                        FROM orders 
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                        GROUP BY DATE(created_at)
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    $prompt = "You are a senior business analyst. Analyze these last 7 days of sales data and give exactly 3 short, actionable bullet points of business advice (start each with '- '): " . json_encode($sales);
                } elseif ($action === 'analyze_inventory') {
                    $inventory = $this->db->query("
                        SELECT p.name, p.sku, 
                               COALESCE(SUM(i.qty_boxes), 0) AS total_boxes, 
                               COALESCE(SUM(i.qty_pieces), 0) AS total_pieces
                        FROM products p
                        LEFT JOIN inventory i ON i.product_id = p.id
                        WHERE p.status = 1
                        GROUP BY p.id, p.name, p.sku
                        ORDER BY total_boxes ASC, total_pieces ASC
                        LIMIT 15
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    $prompt = "You are an inventory manager. Review this low-stock inventory data and suggest 3 short, actionable restock recommendations (start each with '- '): " . json_encode($inventory);
                } elseif ($action === 'dealer_performance') {
                    $dealers = $this->db->query("
                        SELECT d.name, d.phone, MAX(o.created_at) as last_order_date, COUNT(o.id) as total_orders
                        FROM dealers d
                        LEFT JOIN orders o ON o.dealer_id = d.id
                        WHERE d.status = 1
                        GROUP BY d.id, d.name, d.phone
                        LIMIT 15
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    $prompt = "You are a sales director. Review this dealer order history and suggest 3 short, actionable ways to re-engage inactive dealers (start each with '- '): " . json_encode($dealers);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid action requested.']);
                    return;
                }

                echo json_encode([
                    'success' => true,
                    'prompt'  => $prompt
                ]);
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'error'   => 'Database error: ' . $e->getMessage()
                ]);
            }
        }


    public function aiAssistantTranslate(): void
        {
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            $text = trim($input['text'] ?? '');
            $source = $input['source'] ?? 'en';
            $target = $input['target'] ?? 'bn';

            if ($text === '') {
                echo json_encode(['success' => true, 'translation' => '']);
                return;
            }

            $pair = ($source === 'en' && $target === 'bn') ? 'en|bn' : 'bn|en';
            $lines = explode("\n", $text);
            $translatedLines = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') {
                    $translatedLines[] = '';
                    continue;
                }

                $prefix = '';
                if (preg_match('/^([*\-•]|\d+\.)\s*(.*)$/', $trimmed, $matches)) {
                    $prefix = $matches[1] . ' ';
                    $trimmed = $matches[2];
                }

                $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($trimmed) . '&langpair=' . urlencode($pair);
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]);
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);
                $part = $result['responseData']['translatedText'] ?? $trimmed;
                $translatedLines[] = $prefix . $part;
            }

            $translation = implode("\n", $translatedLines);
            echo json_encode(['success' => true, 'translation' => $translation]);
        }


}
