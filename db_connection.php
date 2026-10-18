<?php
// Kunin ang mga detalye mula sa Coolify environment variables
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'qrcode';
$port = (int)(getenv('DB_PORT') ?: 3306);

// Awtomatikong hanapin ang Docker gateway IP kung saan naka-attach ang host database
$gateway_ip = @exec("ip route | awk '/default/ {print $3}'") ?: '172.17.0.1';

// Subukan ang gateway IP, pagkatapos ay i-fallback sa localhost kung sakali
$hosts = [$gateway_ip, '172.17.0.1', '172.18.0.1', 'localhost'];

$conn = null;
$last_error = "";

mysqli_report(MYSQLI_REPORT_OFF);

foreach ($hosts as $host) {
    try {
        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
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
    die("Connection failed on gateway IP ($gateway_ip). Error: " . $last_error);
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
