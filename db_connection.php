<?php
// Kunin ang configuration mula sa Coolify Environment Variables
$host     = getenv('DB_HOST') ?: '172.17.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: ''; // Ilagay ang password kung meron
$dbname   = getenv('DB_NAME') ?: 'qrcode';
$port     = getenv('DB_PORT') ?: '3306';

// Gamitin ang MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
