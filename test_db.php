<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->query("SELECT * FROM van_stock LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}
?>
