<?php
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101'; // Ang host name mula sa Coolify
$port = 3306;
$user = 'root';
$pass = 'ryan072797'; // Ang password mo
$dbname = 'qrcode';   // Ang database name mula sa Coolify

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
