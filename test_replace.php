<?php
$file = 'modules/Manager/ManagerController.php';
$content = file_get_contents($file);

$search = <<<EOT
                    (SELECT COALESCE(SUM(ri.quantity * p.price), 0)
                     FROM returns r
                     JOIN return_items ri ON ri.return_id = r.id
                     JOIN products p ON p.id = ri.product_id
                     JOIN dispatches d ON d.id = r.dispatch_id
                     JOIN orders o ON o.id = d.order_id
                     WHERE o.sr_id = u.id AND d.dispatch_date = '{\$delivery_date}') as return_items_value,
EOT;

$replace = <<<EOT
                    (SELECT COALESCE(SUM(ri.quantity * p.price), 0)
                     FROM returns r
                     JOIN return_items ri ON ri.return_id = r.id
                     JOIN products p ON p.id = ri.product_id
                     WHERE r.dsr_id = {\$schedule['dsr_id']} AND r.return_date = '{\$delivery_date}'
                     AND u.id = {\$schedule['dsr_id']}) as return_items_value,
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Replaced successfully\n";
