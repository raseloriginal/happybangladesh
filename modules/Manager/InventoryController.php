<?php

class InventoryController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

    public function inventory(): void
        {
            $items = $this->db->query("
                SELECT p.id AS product_id, p.name AS product_name, p.sku, 
                       'All Warehouses' AS warehouse_name, 
                       '-' AS lot_number,
                       0 AS qty_boxes,
                       (
                           CAST(COALESCE((SELECT SUM(CAST(qty_boxes AS SIGNED) * CAST(p.pieces_per_box AS SIGNED) + CAST(qty_pieces AS SIGNED)) FROM lots WHERE product_id = p.id), 0) AS SIGNED)
                           -
                           CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM dispatch_items di JOIN dispatches d ON d.id=di.dispatch_id WHERE di.product_id = p.id AND d.status != 'cancelled' AND (d.is_ready_sale = 0 OR d.is_ready_sale IS NULL)), 0) AS SIGNED)
                           +
                           CAST(COALESCE((SELECT SUM(CAST(quantity AS SIGNED)) FROM return_items ri JOIN returns r ON r.id=ri.return_id WHERE ri.product_id = p.id AND r.status != 'cancelled'), 0) AS SIGNED)
                       ) AS qty_pieces
                FROM products p
                WHERE p.status=1
                ORDER BY p.name
            ")->fetchAll();
            $this->render('inventory', compact('items'));
        }


}
