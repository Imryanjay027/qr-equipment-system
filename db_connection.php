<?php
// Direktang ikonekta gamit ang mga eksaktong detalye galing sa Coolify
$host = 'mariadb-database-scvzklpphi95efixy0m7hpyg';
$db   = 'default';
$user = 'mariadb';
$pass = 'pPjqOQG7NRXy20MSRfcuNVhtLTbiAR75w9CLeSceBwp06CQLGrUjgeLolgHCBzJH';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
