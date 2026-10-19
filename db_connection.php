<?php
// Subukan nating i-explicit ang port 3306
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = ''; // Subukan muna ang walang password kung ito ay local container
$dbname = 'qrequipment_db'; // Palitan ng aktwal na pangalan ng database mo kung iba

// Gamitin ang port sa mysqli constructor
$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    // Kung nag-fail ang 127.0.0.1, subukan ang socket o ibang paraan
    die("Database Connection Failed: " . $conn->connect_error . " <br>Tiyakin na naka-start ang MySQL/MariaDB service at tama ang pangalan ng database.");
}

$conn->set_charset("utf8mb4");
?>
