<?php
// Subukan ang Docker default bridge gateway IP o localhost IP para maabot ang host/database port
$hosts = [
    '172.17.0.1', // Karaniwang Docker bridge gateway IP sa Linux/Coolify servers
    '172.18.0.1',
    'localhost'
];

$user = 'root';
$pass = ''; // Ilagay ang Normal User Password mo sa Coolify kung meron
$dbname = 'qrcode';
$port = 3306; // O ang port number na nakalagay sa Ports Mappings sa Coolify

$conn = null;
$last_error = "";

mysqli_report(MYSQLI_REPORT_OFF);

foreach ($hosts as $host) {
    try {
        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        if (@$mysqli->real_connect($host, $user, $pass, $dbname, $port)) {
            $conn = $mysqli;
            break; // Tagumpay! Nakakonekta na.
        }
    } catch (\Throwable $e) {
        $last_error = $e->getMessage();
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$conn) {
    die("Connection failed on all gateway IPs. Last error: " . ($conn->connect_error ?? $last_error));
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
