<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
$tables = ['products', 'orders', 'order_items', 'retailers', 'users'];
foreach($tables as $t) {
    echo "\nTable: $t\n";
    $stmt = $db->query("DESCRIBE $t");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'].' ('.$row['Type'].')'."\n";
    }
}
