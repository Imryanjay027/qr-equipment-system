<?php
// db_connection.php

$host     = 'wfbyfhr4404k2y9u2spiv101';
$user     = 'root';
$password = 'ito_ang_root_password_mo_sa_coolify'; // Palitan ng Root Password mula sa Coolify config
$database = 'qrcode';
$port     = 3306;

// Pagkonekta gamit ang root user
$conn = mysqli_connect($host, $user, $password, $database, $port);

// Pagsusuri kung may error sa koneksyon
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
