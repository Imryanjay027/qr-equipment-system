<?php
session_start();

// Baguhin ang host pabalik sa internal service name o subukan ang Docker bridge IP kasama ang bagong non-root user
$host = '172.17.0.1'; 
$user = 'qradmin'; // Gumamit ng hiwalay na user sa halip na root
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
    
    if (!$conn->real_connect($host, $user, $pass, $db, $port)) {
        throw new Exception("Connect Error: " . mysqli_connect_error());
    }
    
    $conn->set_charset("utf8mb4");
    
    echo "Database connected successfully!";
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
