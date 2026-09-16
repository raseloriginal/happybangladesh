<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$q = $db->query('SELECT * FROM van_stock');
print_r($q->fetchAll(PDO::FETCH_ASSOC));
