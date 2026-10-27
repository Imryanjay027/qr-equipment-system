<?php
$host = 'scvzklpphi95efixy0m7hpyg'; // Tamang internal hostname para sa MariaDB sa Coolify
$db   = 'default';
$user = 'root'; 
$pass = 'qOAF8F2klYnvxBSlj15uEx88gJxMM95J6QC6TJ1FYRgX9cHvqgA3QlmOhnETItCV';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
