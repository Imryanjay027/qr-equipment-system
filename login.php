<?php
session_start();

// Database configuration settings gamit ang Docker Bridge IP
$host = '172.17.0.1'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

// I-enable ang strict exception reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Gamitin ang mysqli initialization para sa matatag na handshake
    $conn = mysqli_init();
    
    // I-set ang connection timeout sa 15 segundo
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
    
    // Ituloy ang koneksyon gamit ang real_connect
    if (!$conn->real_connect($host, $user, $pass, $db, $port)) {
        throw new Exception("Connect Error: " . mysqli_connect_error());
    }
    
    // I-set ang UTF-8 charset
    $conn->set_charset("utf8mb4");
    
    // Kung gumagana na, maaari mong i-uncomment ang susunod na linya para i-test
    // echo "Database connected successfully!";
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
