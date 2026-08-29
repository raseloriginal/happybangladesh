<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("UPDATE van_stock SET quantity = GREATEST(0, quantity - ?) WHERE dsr_id = ? AND product_id = ? AND (lot_id=? OR (? IS NULL AND lot_id IS NULL))");
    $stmt->execute([1, 1, 1, null, null]);
    echo "Success!";
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}
?>
