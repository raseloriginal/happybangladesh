<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'app/Config/config.php';
require 'app/Core/Database.php';
try {
$db = \App\Core\Database::getInstance();
$q = $db->prepare("
            SELECT 
                vs.product_id, 
                SUM(vs.initial_qty) - COALESCE((
                    SELECT SUM(COALESCE(di.delivered_quantity, 0))
                    FROM dispatches d
                    JOIN dispatch_items di ON d.id = di.dispatch_id
                    WHERE d.dsr_id = vs.dsr_id 
                      AND d.dispatch_date = DATE(MAX(vs.loaded_at)) 
                      AND d.status IN ('delivered', 'partial')
                      AND di.product_id = vs.product_id
                ), 0) - COALESCE((
                    SELECT SUM(ri.quantity)
                    FROM returns r
                    JOIN return_items ri ON r.id = ri.return_id
                    WHERE r.dsr_id = vs.dsr_id
                      AND r.return_date = DATE(MAX(vs.loaded_at))
                      AND ri.product_id = vs.product_id
                ), 0) as remaining_qty
            FROM van_stock vs
            WHERE vs.dsr_id = 2 AND DATE(vs.loaded_at) = '2026-09-16'
            GROUP BY vs.product_id
            HAVING remaining_qty > 0
        ");
$q->execute();
print_r($q->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
