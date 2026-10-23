<?php
session_start();

// Mga posibleng host kasama ang Docker internal gateway IP at container bridge
$hosts = ['wfbyfhr4k2y9u2spiv101', '172.17.0.1', 'host.docker.internal', 'localhost', '127.0.0.1']; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$connected = false;

foreach ($hosts as $host) {
    try {
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        
        if (@$conn->real_connect($host, $user, $pass, $db, $port)) {
            $conn->set_charset("utf8mb4");
            $connected = true;
            break; 
        }
    } catch (Exception $e) {
        continue;
    }
}

if (!$connected) {
    die("Database connection failed: Hindi makakonekta sa database server. Siguraduhing nasa iisang Docker network o tama ang IP ng database.");
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
