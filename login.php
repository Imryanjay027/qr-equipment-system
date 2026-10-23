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
    // Gamitin ang mysqli initialization para maiwasan ang greeting packet / gone away issue
    $conn = mysqli_init();
    
    // I-set ang timeout para hindi agad maputol ang koneksyon
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    // Kumonekta gamit ang ip, user, password, db, port
    $conn->real_connect($host, $user, $pass, $db, $port);
    
    // Enable UTF-8 encoding
    $conn->set_charset("utf8mb4");
    
    // echo "Database connected successfully!";
    
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
