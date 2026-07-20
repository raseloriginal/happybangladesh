<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
$stmt = $db->query("SHOW CREATE TABLE products");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
