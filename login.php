<?php
session_start();

// Database configuration settings
$host = '172.17.0.1'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

// I-enable ang strict exception reporting para malinaw ang error kung sakali
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Establish database connection
    $conn = new mysqli($host, $user, $pass, $db, $port);
    
    // Enable UTF-8 encoding
    $conn->set_charset("utf8mb4");
    
    // Maaari mong tanggalin o palitan ito ng iyong login UI logic kapag gumagana na
    // echo "Database connected successfully!";
    
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
