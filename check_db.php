<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');
// Add 'cancelled' to ENUM
$db->exec("ALTER TABLE dispatches MODIFY COLUMN status ENUM('pending','in_transit','delivered','partial','returned','cancelled') NOT NULL DEFAULT 'pending'");

// Fix the empty status rows to 'cancelled' (assuming they were cancelled)
$db->exec("UPDATE dispatches SET status = 'cancelled' WHERE status = ''");

echo "Fixed ENUM and updated empty statuses.";
