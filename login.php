<?php
session_start();

// Database configuration settings
$host = getenv('DB_HOST') ?: 'mariadb-database-wfbyfhr4404k2';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'ryan072797';
$db   = getenv('DB_NAME') ?: 'qrcode';
$port = (int)(getenv('DB_PORT') ?: 3306);

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Enable UTF-8 encoding
$conn->set_charset("utf8mb4");

// Optional: Maglagay ng simpleng feedback kapag direktang binuksan para ma-test
// Maaari mo itong alisin kapag gumagana na ang buong login logic mo
?>
