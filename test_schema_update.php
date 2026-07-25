<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = Database::getInstance();

$tables = ['warehouses', 'companies', 'dealers', 'users', 'roles', 'orders'];
foreach ($tables as $t) {
    try {
        $q = $db->query("SHOW COLUMNS FROM `$t` LIKE 'status'");
        if (!$q->fetch()) {
            echo "Table $t missing status. Adding it...\n";
            $db->exec("ALTER TABLE `$t` ADD `status` TINYINT(1) DEFAULT 1");
        } else {
            echo "Table $t has status.\n";
        }
    } catch(Exception $e) {
        echo "Error checking $t: " . $e->getMessage() . "\n";
    }
}

$q = $db->query("SHOW COLUMNS FROM `users` LIKE 'target_amount'");
if (!$q->fetch()) {
    echo "Adding target_amount to users...\n";
    $db->exec("ALTER TABLE `users` ADD `target_amount` DECIMAL(10,2) DEFAULT 0.00");
} else {
    echo "target_amount already in users.\n";
}
echo "Done.\n";
