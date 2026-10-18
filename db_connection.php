<?php
// Kunin ang database credentials mula sa Coolify Environment Variables, 
// o gumamit ng fallback values para sa local development (XAMPP).
$host   = getenv('DB_HOST')     ?: 'localhost';
$user   = getenv('DB_USER')     ?: 'root';
$pass   = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME')     ?: 'qrcode';

// Gumawa ng koneksyon sa MariaDB/MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
