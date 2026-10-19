<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = 'ryan072797'; // Ang password ng iyong database
$dbname = 'qrcode';   // Ang database name mula sa Coolify

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
