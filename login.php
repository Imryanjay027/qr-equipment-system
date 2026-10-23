<?php
session_start();

// Gamitin ang tamang internal service hostname mula sa Coolify configuration
$host = 'wfbyfhr4k2y9u2spiv101'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    
    // I-set ang timeout
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    // Kumonekta
    $conn->real_connect($host, $user, $pass, $db, $port);
    
    // Enable UTF-8 encoding
    $conn->set_charset("utf8mb4");
    
    echo "Database connected successfully!";
    
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
