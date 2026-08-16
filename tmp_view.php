<?php
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Config.php';

$db = Database::getInstance();
$sql = "CREATE OR REPLACE VIEW v_live_stock AS
SELECT 
    p.id AS product_id,
    w.id AS warehouse_id,
    COALESCE(
        (SELECT SUM(l.qty_boxes * p.pieces_per_box + l.qty_pieces) FROM lots l WHERE l.product_id = p.id), 0
    ) 
    - 
    COALESCE(
        (SELECT SUM(di.quantity) FROM dispatch_items di JOIN dispatches d ON d.id = di.dispatch_id WHERE di.product_id = p.id AND d.warehouse_id = w.id AND d.status != 'cancelled'), 0
    ) 
    + 
    COALESCE(
        (SELECT SUM(ri.quantity) FROM return_items ri JOIN returns r ON r.id = ri.return_id WHERE ri.product_id = p.id AND r.warehouse_id = w.id AND r.status != 'cancelled'), 0
    ) AS live_stock_pieces
FROM products p
CROSS JOIN warehouses w;";

try {
    $db->exec($sql);
    echo "View created successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
