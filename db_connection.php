<?php
// Subukan ang iba't ibang paraan ng pagkonekta (Internal name, IP, o localhost)
$hosts = [
    'mariadb-database-wfbyfhr4404k2y9u2spiv101',
    '127.0.0.1',
    'localhost',
    'host.docker.internal'
];

$user = 'root';
$pass = ''; // Ilagay ang password dito kung meron
$dbname = 'qrcode';
$port = 3306;

$conn = null;
$last_error = "";

mysqli_report(MYSQLI_REPORT_OFF);

foreach ($hosts as $host) {
    try {
        $conn = @new mysqli($host, $user, $pass, $dbname, $port);
        if (!$conn->connect_error) {
            break; // Tagumpay! Nakakonekta na.
        }
    } catch (\Throwable $e) {
        $last_error = $e->getMessage();
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$conn || $conn->connect_error) {
    die("Connection failed on all available hosts. Last error: " . ($conn->connect_error ?? $last_error));
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
