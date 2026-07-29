<?php
// Temporary debug file - DELETE AFTER DIAGNOSIS
echo '<pre style="font-family:monospace;font-size:13px;padding:20px;">';
echo '<strong>REQUEST_URI:</strong> ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo '<strong>SCRIPT_NAME:</strong> ' . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo '<strong>PHP_SELF:</strong> '    . htmlspecialchars($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo '<strong>HTTP_HOST:</strong> '   . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo '<strong>HTTPS:</strong> '               . htmlspecialchars($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo '<strong>SERVER_PORT:</strong> '         . htmlspecialchars($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n";
echo '<strong>REQUEST_SCHEME:</strong> '      . htmlspecialchars($_SERVER['REQUEST_SCHEME'] ?? 'N/A') . "\n";
echo '<strong>HTTP_X_FORWARDED_PROTO:</strong> ' . htmlspecialchars($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
echo '<strong>HTTP_X_FORWARDED_SSL:</strong> '   . htmlspecialchars($_SERVER['HTTP_X_FORWARDED_SSL'] ?? 'N/A') . "\n";
echo '<strong>$_GET[url]:</strong> '          . htmlspecialchars($_GET['url'] ?? 'NOT SET') . "\n";
echo "\n<strong>--- Full $_SERVER ---</strong>\n";
foreach ($_SERVER as $k => $v) {
    echo htmlspecialchars($k) . ': ' . htmlspecialchars((string)$v) . "\n";
}
echo '</pre>';
