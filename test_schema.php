<?php
require 'public/index.php'; // or whatever loads Database
$db = Database::getInstance();
$stmt = $db->query("DESCRIBE dispatch_schedules");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
// Empty
