<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = Database::getInstance();
$res = $db->query("SHOW CREATE TABLE returns")->fetch();
print_r($res);
