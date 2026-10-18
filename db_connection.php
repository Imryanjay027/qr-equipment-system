<?php
// Gamitin ang localhost o ang loopback kasama ang port kung saan nakikinig ang MariaDB sa host
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Ilagay ang Normal User Password mo sa Coolify kung meron
$dbname = 'qrcode';
$port = 3306; // Palitan ito kung ibang port ang nakalagay sa Port Mappings sa Coolify (halimbawa 3307 o iba pa)

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
