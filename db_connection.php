<?php
// Gamitin ang IP ng iyong VPS / Server kung saan naka-host ang Coolify at ang database port nito
$host = 'IP_NG_VPS_MO_DITO'; // Halimbawa: '192.168.1.50' o ang domain ng server mo
$user = 'root';
$pass = ''; // Ilagay ang database password mo rito kung meron
$dbname = 'qrcode';
$port = 3307; // Ang public port ng database sa Coolify (kung binago) o 3306

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
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
