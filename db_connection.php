<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_DATABASE') !== false ? getenv('DB_DATABASE') : getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : getenv('DB_PASS');

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Matagumpay ang koneksyon
} catch (\PDOException $e) {
    // Ipakita ang eksaktong error kung sakaling magkaaberya
    die("Database Connection Error: " . $e->getMessage());
}
?>
