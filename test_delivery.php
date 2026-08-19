<?php
$hasDeliveries = true;
$retailers = [];
$selectedDate = "2026-08-16";
$isCompleted = false;
$isReturned = false;
class Helpers { public static function csrfToken() { return "token"; } }
function url($path) { return "http://localhost/" . $path; }
ob_start();
include "c:/xampp/htdocs/happybangladesh/modules/DSR/views/delivery.php";
$output = ob_get_clean();
file_put_contents("c:/xampp/htdocs/happybangladesh/rendered_delivery.html", $output);
echo "Written to rendered_delivery.html\n";
