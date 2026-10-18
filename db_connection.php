<?php
// Gamitin ang hostname na nakalagay sa iyong Coolify MariaDB resource
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$user = 'root';
$pass = ''; // Siguraduhing blangko o naka-match sa Normal User Password sa Coolify
$dbname = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $mysqli->real_connect($host, $user, $pass, $dbname, $port);
    $conn = $mysqli;
} catch (mysqli_sql_exception $e) {
    // Kung sakaling mag-fail ang container name, subukan ang fallback sa environment o localhost
    try {
        $conn = new mysqli('localhost', $user, $pass, $dbname, $port);
    } catch (\Throwable $ex) {
        die("Connection failed completely. Error: " . $e->getMessage());
    }
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
