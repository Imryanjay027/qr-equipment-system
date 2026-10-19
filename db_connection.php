<?php
// Kunin ang credentials mula sa Coolify environment variables o gamitin ang localhost
$host = getenv('DB_HOST') ?: 'localhost'; 
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'qr_equipment_system';
$port = getenv('DB_PORT') ?: 3306;

// Gumawa ng koneksyon gamit ang MySQLi
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Suriin ang koneksyon
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
