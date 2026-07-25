<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
$db = Database::getInstance();
$id = 1;
$schedule = $db->query("SELECT dispatch_date, delivery_date, dsr_id FROM dispatch_schedules WHERE id = " . (int)$id)->fetch();
$delivery_date = $schedule['delivery_date'] ?: $schedule['dispatch_date'];
$srs = $db->query("
    SELECT u.id, u.name,
           (
               SELECT COALESCE(SUM(ri.quantity * p.price), 0)
               FROM returns r
               JOIN return_items ri ON ri.return_id = r.id
               JOIN products p ON p.id = ri.product_id
               WHERE r.dsr_id = {$schedule['dsr_id']} AND r.return_date = '{$delivery_date}'
           ) as return_items_value
    FROM dispatch_schedule_srs dss
    JOIN users u ON u.id = dss.sr_id
    WHERE dss.schedule_id = " . (int)$id . "
")->fetchAll(PDO::FETCH_ASSOC);

print_r($srs);
