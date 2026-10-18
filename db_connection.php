<?php
// Kunin ang mga detalye mula sa Environment Variables na sinet natin sa Coolify
$host = getenv('DB_HOST') ?: 'wfbyfhr4404k2y9u2spiv101';
$user = getenv('DB_USER') ?: 'mariadb';
$pass = getenv('DB_PASSWORD') ?: ''; // Kukuha sa DB_PASSWORD na ginawa natin
$dbname = getenv('DB_NAME') ?: 'qrcode';

// Pagkonekta gamit ang MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// I-set ang character set sa UTF-8
$conn->set_charset("utf8");
?>
