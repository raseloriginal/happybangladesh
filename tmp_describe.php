<?php
$host = 'localhost';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query('SHOW DATABASES');
    print_r($stmt->fetchAll());
} catch (\PDOException $e) {
    echo $e->getMessage();
}
