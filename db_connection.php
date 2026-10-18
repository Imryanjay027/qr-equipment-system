<?php
// db_connection.php

$host     = '172.17.0.1';
$user     = 'mariadb';
$password = 'ryan072797';
$database = 'qrcode';

// Pagkonekta gamit ang mysqli
$conn = mysqli_connect($host, $user, $password, $database);

// Pagsusuri kung may error sa koneksyon
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
