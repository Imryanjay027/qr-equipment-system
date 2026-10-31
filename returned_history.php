<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kunin ang credentials mula sa Coolify Environment Variables o gamitin ang tamang host
$host = getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'mariadb'; // Palitan ang 'mariadb' ng pangalan ng database service mo sa Coolify kung iba
$port = (int)(getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'qr_equipment_db';

$conn = @new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    // Subukan ang 'localhost' kung sakaling nasa iisang container sila
    $conn = @new mysqli('127.0.0.1', $username, $password, $database, $port);
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
}
$conn->set_charset("utf8mb4");
?>
