<?php
// db_connection.php

// Subukan nating tukuyin kung nasa live server o local ka
$host = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? 'localhost' : 'localhost'; // Palitan ng host ng live server mo kung iba
$username = "root"; // Palitan ng live database username mo kung kinakailangan
$password = ""; // Ilagay ang live database password mo kung meron
$database = "qrcode"; // Ang pangalan ng database mo

// Pagkonekta gamit ang mysqli
$conn = new mysqli($host, $username, $password, $database);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
