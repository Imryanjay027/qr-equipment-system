<?php
session_start();

$host = 'wfbyfhr4k2y9u2spiv101'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$max_retries = 3;
$retry_delay = 2; // seconds

for ($i = 0; $i < $max_retries; $i++) {
    try {
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        
        if ($conn->real_connect($host, $user, $pass, $db, $port)) {
            $conn->set_charset("utf8mb4");
            break; 
        }
    } catch (Exception $e) {
        if ($i == $max_retries - 1) {
            die("Database connection failed after retries: " . $e->getMessage());
        }
        sleep($retry_delay);
    }
}
?>
<!-- ILAGAY DITO ANG BUONG ORIHINAL MONG HTML, CSS, AT UI NG LOGIN PAGE -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Equipment System - Login</title>
</head>
<body>
    <!-- Ilagay ang iyong mga form at UI elements dito -->
</body>
</html>
