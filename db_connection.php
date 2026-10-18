<?php
// db_connection.php

$host     = '127.0.0.1'; // Gamitin ang loopback IP para sa parehong network namespace
$user     = 'mariadb';
$password = 'ryan072797';
$database = 'qrcode';
$port     = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $password, $database, $port);
} catch (mysqli_sql_exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
