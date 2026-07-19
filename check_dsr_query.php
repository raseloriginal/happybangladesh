<?php
$db = new PDO('mysql:host=localhost;dbname=happybangladesh_dms', 'root', '');

$dsrId = 12; // Let's check for Munna store or phone number
$stmt = $db->prepare("SELECT id FROM users WHERE phone = '01300000000'");
$stmt->execute();
$dsrId = $stmt->fetchColumn();

if (!$dsrId) die("User not found");

$selectedDate = date('Y-m-d');
$q = $db->prepare("
    SELECT d.id as dispatch_id, o.id as order_id, COALESCE(dl.id, r.id) as dealer_id,
           COALESCE(dl.name, r.name) as dealer_name, 
           r.name as retailer_name, dl.name as dealer_business_name,
           COALESCE(dl.address, r.address) as address, 
           COALESCE(dl.lat, r.lat) as lat, 
           COALESCE(dl.lng, r.lng) as lng,
           o.total_amount, d.status, d.paid_amount,
           c.name as company_name
    FROM dispatches d
    JOIN orders o ON o.id = d.order_id
    JOIN users u ON u.id = o.sr_id
    LEFT JOIN companies c ON c.id = u.company_id
    LEFT JOIN dealers dl ON dl.id = o.dealer_id
    LEFT JOIN retailers r ON r.id = o.retailer_id
    WHERE d.dsr_id = ?
      AND (d.status IN ('in_transit', 'partial') OR (d.status IN ('delivered', 'cancelled') AND (d.dispatch_date = ? OR DATE(d.updated_at) = ?)))
    ORDER BY dealer_name ASC
");
$q->execute([$dsrId, $selectedDate, $selectedDate]);
$flatRetailers = $q->fetchAll(PDO::FETCH_ASSOC);

print_r($flatRetailers);
