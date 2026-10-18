<?php
$host     = getenv('DB_HOST') ?: 'wfbyfhr4404k2y9u2spiv101';
$user     = 'root';
$password = getenv('DB_PASSWORD') ?: 'ryan072797';
$port     = getenv('DB_PORT') ?: 3306;

// Kumonekta muna nang walang specific database para malikha ito
$conn = mysqli_connect($host, $user, $password, "", $port);

// Likha ang database kung wala pa
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS qrcode");

// Pumili sa database na nilikha
mysqli_select_db($conn, "qrcode");
?>
