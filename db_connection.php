<?php
// db_connection.php

$host     = 'wfbyfhr4404k2y9u2spiv101'; // Ang Coolify service ID ng MariaDB
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
