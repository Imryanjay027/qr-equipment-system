<?php
// db_connection.php

$host     = 'localhost'; // Subukan muna natin i-hardcode sa localhost o 127.0.0.1
$user     = getenv('DB_USER') ?: 'mariadb';
$password = getenv('DB_PASSWORD') ?: 'ryan072797';
$database = getenv('DB_NAME') ?: 'qrcode';
$port     = getenv('DB_PORT') ?: 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
