<?php
require 'app/Config/config.php';
$layoutFile = APP_PATH . '/Views/layouts/dsr_app.php';
echo "APP_PATH: " . APP_PATH . "\n";
echo "layoutFile: " . $layoutFile . "\n";
echo "file_exists: " . (file_exists($layoutFile) ? "YES" : "NO") . "\n";
