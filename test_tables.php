<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
$db->exec("ALTER TABLE dispatch_schedules ADD COLUMN delivery_date DATE DEFAULT NULL AFTER dispatch_date");
$stmt = $db->query("DESCRIBE dispatch_schedules");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
