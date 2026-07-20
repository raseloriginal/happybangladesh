<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
$tables = ['van_stock', 'dispatch_schedules', 'dispatch_schedule_srs', 'dispatch_items', 'dispatches', 'orders', 'order_items', 'returns', 'return_items'];
foreach($tables as $table) {
    try {
        $stmt = $db->query("SHOW CREATE TABLE $table");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $row['Create Table'] . "\n\n";
    } catch(Exception $e) {
        echo "Table $table not found\n";
    }
}
