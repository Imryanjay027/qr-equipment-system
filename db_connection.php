<?php
// db_connection.php

$host     = getenv('DB_HOST') ?: 'wfbyfhr4404k2y9u2spiv101';
$user     = 'root'; // Pinalitan na natin patungong root
$password = getenv('DB_PASSWORD') ?: 'ryan072797';
$database = getenv('DB_NAME') ?: 'qrcode';
$port     = getenv('DB_PORT') ?: 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $password, $database, $port);
} catch (mysqli_sql_exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
