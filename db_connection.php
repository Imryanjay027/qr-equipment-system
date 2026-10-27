<?php
$host = 'mariadb-database-scvzklpphi95efixy0m7hpyg';
$db   = 'default';
$user = 'mariadb';
$pass = 'ryanjay09954040736'; // Eksaktong password mula sa Coolify dashboard

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
