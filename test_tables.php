<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) echo $t . "\n";
