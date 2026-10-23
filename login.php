<?php
session_start();

// Database configuration settings
$host = '172.17.0.1'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    
    // I-set ang timeout
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
    
    // Kumonekta nang walang compression flag para maiwasan ang greeting packet error
    $conn->real_connect($host, $user, $pass, $db, $port);
    
    // Enable UTF-8 encoding
    $conn->set_charset("utf8mb4");
    
    echo "Database connected successfully!";
    
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
