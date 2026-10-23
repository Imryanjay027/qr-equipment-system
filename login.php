<?php
session_start();

// Database configuration settings
$host = '172.17.0.1'; 
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Enable UTF-8 encoding
$conn->set_charset("utf8mb4");

// Maglagay dito ng karagdagang login logic kung kinakailangan
?>
