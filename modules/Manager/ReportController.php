<?php

class ReportController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

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
            $pQuery = $this->db->prepare("SELECT name, box_type, pieces_per_box, price, buying_price FROM products WHERE id=?");
            $pQuery->execute([$pId]);
            $pd = $pQuery->fetch(PDO::FETCH_ASSOC);

            $this->db->prepare("INSERT INTO readysales (warehouse_id,product_id,lot_id,quantity,price,base_selling_price,buying_price,product_name,box_type,pieces_per_box) VALUES (?,?,?,?,?,?,?,?,?,?)")
                     ->execute([$this->post('warehouse_id'), $pId, $this->post('lot_id') ?: null, $this->post('quantity',0), $this->post('price',0), $pd['price'] ?? 0, $pd['buying_price'] ?? 0, $pd['name'] ?? null, $pd['box_type'] ?? null, $pd['pieces_per_box'] ?? 1]);
            $this->flash('success', 'Ready sale record added.'); $this->redirect('manager/readysale');
        }


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
                       SUM(oi.quantity * oi.base_selling_price) as total_base_value,
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
                       SUM(oi.quantity * oi.base_selling_price) as total_base_value,
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
                       SUM(oi.quantity * oi.base_selling_price) as total_base_value,
                       SUM(oi.total_price) as total_sr_value,
                       SUM(oi.quantity * (oi.unit_price - oi.base_selling_price)) as total_oc
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
                       SUM(oi.quantity * oi.base_selling_price) as total_base_value,
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
                               CAST(COALESCE((SELECT SUM(CAST(qty_boxes AS SIGNED) * CAST(p.pieces_per_box AS SIGNED) + CAST(qty_pieces AS SIGNED)) FROM lots WHERE product_id = p.id), 0) AS SIGNED)
                               -
                               CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled' AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)), 0) AS SIGNED)
                               +
                               CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0) AS SIGNED)
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


}
