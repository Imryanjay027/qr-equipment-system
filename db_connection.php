<?php
$host = '172.17.0.1';
$port = 3306;
$user = 'root'; // Subukan ang 'root' o ang username mula sa Coolify DB settings
$pass = 'ryan072797';
$dbname = 'qrcode';

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
