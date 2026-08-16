<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'happybangladesh_dms';

$dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query('SELECT name, buying_price, price, dealer_percentage FROM products LIMIT 5');
    print_r($stmt->fetchAll());
} catch (\PDOException $e) {
    echo $e->getMessage();
}
