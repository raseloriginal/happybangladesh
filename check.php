<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';

$db = Database::getInstance();
$q = $db->query("SELECT id, name, phone FROM retailers WHERE name LIKE '%উজ্জ্বল%' OR name LIKE '%তসলিম%'");
print_r($q->fetchAll());

$q2 = $db->query("SELECT id, name, phone FROM dealers WHERE name LIKE '%উজ্জ্বল%' OR name LIKE '%তসলিম%'");
print_r($q2->fetchAll());
