<?php
$host = '127.0.0.1'; // Gagamitin ang IP sa loob ng container network
$port = 3306;
$user = 'root';
$pass = 'ILAGAY_ANG_PASSWORD_MULA_SA_SCREEN'; // Ang password na nakita mo sa Coolify[cite: 4]
$dbname = 'qrcode'; // Ang initial database mula sa Coolify[cite: 4]

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
