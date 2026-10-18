<?php
// Subukan ang iba't ibang posibleng host sa Coolify environment
$hosts = [
    $_ENV['DB_HOST'] ?? '',
    'mariadb', // Karaniwang internal service name sa Docker/Coolify
    'database',
    '127.0.0.1',
    'localhost'
];

$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$dbname = $_ENV['DB_DATABASE'] ?? 'qrcode';
$port = (int)($_ENV['DB_PORT'] ?? 3306);

$conn = null;
$connect_error = "";

// Subukan isa-isa ang mga host hanggang sa kumonekta
foreach ($hosts as $host) {
    if (empty($host)) continue;
    
    // Pigilan ang mysqli na maglabas ng direktang warning/error habang sinusubukan
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($host, $user, $pass, $dbname, $port);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if (!$conn->connect_error) {
        break; // Tagumpay ang koneksyon!
    } else {
        $connect_error = $conn->connect_error;
    }
}

// Kung nabigo lahat ng pagsubok
if (!$conn || $conn->connect_error) {
    die("Connection failed on all hosts. Last error: " . $connect_error);
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
