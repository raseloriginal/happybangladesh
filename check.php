<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$q = $db->query('SELECT dsr_id, product_id, quantity, initial_qty FROM van_stock WHERE quantity < initial_qty ORDER BY id DESC LIMIT 20');
print_r($q->fetchAll(PDO::FETCH_ASSOC));
