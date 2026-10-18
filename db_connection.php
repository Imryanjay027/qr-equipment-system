<?php
// Gamitin ang Coolify environment variable para sa host kung available, 
// o ang pangalan ng database container mula sa iyong project
$host = getenv('DB_HOST') ?: 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: ''; // Ilagay ang password kung kinakailangan
$dbname = getenv('DB_NAME') ?: 'qrcode';
$port = (int)(getenv('DB_PORT') ?: 3306);

// Kung sakaling hindi pa naka-link ang network, gamitin muna ang localhost habang inaayos ang network sa Coolify
if (empty($host)) {
    $host = 'localhost';
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $mysqli->real_connect($host, $user, $pass, $dbname, $port);
    $conn = $mysqli;
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage() . " (Host: $host)");
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
