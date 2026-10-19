<?php
$host = '172.17.0.1';
$port = 3306;
$user = 'root'; 
$pass = 'cW86JONRjM797hW23852myqva2R4yZKKCcJ9o41Hd8ViLXYCAy2MQchbytcIY0ke'; // Palitan ito ng buong Root Password na nakikita mo sa Coolify dashboard mo
$dbname = 'qrcode';

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
