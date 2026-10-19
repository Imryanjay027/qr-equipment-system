<?php
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101'; // Ang Host/Name mula sa Coolify
$port = 3306;
$user = 'root';                                      // Normal User[cite: 4]
$pass = 'ILAGAY_ANG_PASSWORD_MULA_SA_SCREEN';         // Pindutin ang eye icon sa screen para makita ang password[cite: 4]
$dbname = 'qrcode';                                  // Initial Database[cite: 4]

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
