<?php
// Babasahin nito ang nilagay mo sa Environment Variables sa Coolify
$host = getenv('DB_HOST') ?: '172.17.0.1';
$port = 3306;
$user = 'root';
$pass = 'ryan072797';
$dbname = 'qrcode';

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
