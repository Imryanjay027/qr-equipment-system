<?php
// 1. Awtomatikong i-redirect ang user sa login.php
header("Location: login.php");
exit();

/* 
  -------------------------------------------------------------
  DATABASE CONNECTION CONFIGURATION
  -------------------------------------------------------------
  Kung may code ka rito sa ibaba para sa database connection,
  panatilihin itong nakakonekta gamit ang 'localhost' sa halip
  na '172.17.0.1' para maiwasan ang "Connection refused" error.
*/

$host = 'localhost'; // Inayos mula sa 172.17.0.1
$user = 'root';
$pass = 'YOUR_DATABASE_PASSWORD'; // Palitan ng tamang password
$db   = 'qrcode';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
