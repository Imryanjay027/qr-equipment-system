<?php
// db_connection.php

$host     = 'wfbyfhr4404k2y9u2spiv101';
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
