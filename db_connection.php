<?php
$host = '127.0.0.1';          // Pinalitan mula 'localhost' patungong IP para sa Docker/Coolify
$user = 'root';               // Ang iyong database username
$pass = 'ILAGAY_ANG_PASSWORD'; // Palitan ng tamang password ng database mo
$dbname = 'PANGALAN_NG_DB';   // Palitan ng tamang pangalan ng database mo

$conn = new mysqli($host, $user, $pass, $dbname);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset para ligtas sa special characters
$conn->set_charset("utf8mb4");
?>
