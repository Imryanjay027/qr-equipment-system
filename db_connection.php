<?php
// Palitan ang mga value na ito ayon sa iyong Coolify database credentials
$host = 'localhost';          // O ang database host/container name mula sa Coolify
$user = 'root';               // Ang tamang database username mo
$pass = 'ILAGAY_ANG_PASSWORD'; // Ilagay dito ang password ng database kung meron (kung wala, iwanang blangko '')
$dbname = 'PANGALAN_NG_DB';   // Ang pangalan ng iyong database sa phpMyAdmin / MariaDB

$conn = new mysqli($host, $user, $pass, $dbname);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset para maiwasan ang mga isyu sa special characters
$conn->set_charset("utf8mb4");
?>
