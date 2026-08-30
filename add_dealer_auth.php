<?php
require_once __DIR__ . '/app/Config/config.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add username and password to dealers
    $sql = "ALTER TABLE `dealers` 
            ADD COLUMN `username` VARCHAR(100) NULL AFTER `name`,
            ADD COLUMN `password` VARCHAR(255) NULL AFTER `username`,
            ADD UNIQUE INDEX `uq_dealers_username` (`username`);";
            
    $db->exec($sql);
    echo "Successfully added username and password to dealers table.\n";
    
    // For existing dealers, generate a default username and password to prevent NULL unique constraint issues if any,
    // though NULLs are allowed in UNIQUE index in InnoDB. Let's set a default for existing ones.
    $stmt = $db->query("SELECT id, phone FROM dealers");
    $dealers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($dealers as $d) {
        $username = "dealer_" . $d['id'];
        $password = password_hash("123456", PASSWORD_DEFAULT);
        $db->exec("UPDATE dealers SET username = '$username', password = '$password' WHERE id = " . $d['id']);
    }
    echo "Updated existing dealers with default credentials (password: 123456).\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
