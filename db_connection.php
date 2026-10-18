<?php
// db_connection.php

$host     = getenv('DB_HOST')     ?: 'wfbyfhr4404k2y9u2spiv101';
$user     = getenv('DB_USER')     ?: 'mariadb';
$password = getenv('DB_PASSWORD') ?: 'ryan072797';
$database = getenv('DB_NAME')     ?: 'qrcode';

// Pagkonekta gamit ang mysqli gamit ang environment variables
$conn = mysqli_connect($host, $user, $password, $database);

// Pagsusuri kung may error sa koneksyon
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
