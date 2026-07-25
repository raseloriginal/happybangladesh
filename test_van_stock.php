<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = Database::getInstance();
try {
    $stock = $db->query("
        SELECT vs.product_id, p.name as product_name, SUM(vs.quantity) as qty
        FROM van_stock vs
        JOIN products p ON p.id = vs.product_id
        WHERE vs.dsr_id = 1 AND vs.quantity > 0
        GROUP BY vs.product_id
    ")->fetchAll();
    echo "Success: \n";
    print_r($stock);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
