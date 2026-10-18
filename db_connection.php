<?php
// Gamitin ang IP address ng VPS server mo o ang localhost IP sa pamamagitan ng host.docker.internal kung suportado, 
// o kaya ang pampublikong IP ng iyong VPS (halimbawa: '127.0.0.1' kung nasa parehong server, pero dahil hiwalay ang container, gamitin ang VPS IP o mariadb IP)
$host = '127.0.0.1'; // O ang IP ng iyong VPS kung saan naka-port map ang MariaDB (hal. port 3306)
$user = 'root';
$pass = ''; // Ilagay ang Normal User Password mo sa Coolify kung meron
$dbname = 'qrcode';
$port = 3306; // Siguraduhing tugma sa port mapping sa Coolify

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $mysqli->real_connect($host, $user, $pass, $dbname, $port);
    $conn = $mysqli;
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Automatic creation ng tbl_users kung hindi pa ito umiiral
$createTableSql = "CREATE TABLE IF NOT EXISTS tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL DEFAULT 'Student',
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    course_year VARCHAR(100) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    selfie_image TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($createTableSql);
?>
