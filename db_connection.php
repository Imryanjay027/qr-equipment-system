<?php
// db_connection.php - Fixed Cloud Host & Auto Table Creator
$host = getenv('DB_HOST') ?: 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'wfbyfhr4404k2y9u2spiv101';
$dbname = getenv('DB_NAME') ?: 'qrcode';

// PDO Connection (para sa ibang scripts tulad ng OTP)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8mb4", 'root', '', $options);
    } catch (\PDOException $ex) {}
}

// MySQLi Connection (para sa mga dashboards)
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // Fallback sa localhost kung sakaling kailanganin
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Awtomatikong likhain ang lahat ng kinakailangang tables para walang mag-error
$conn->query("CREATE TABLE IF NOT EXISTS tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) DEFAULT 'Student',
    username VARCHAR(100),
    student_id VARCHAR(50),
    fullname VARCHAR(150),
    course_year VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255) NULL,
    otp_code VARCHAR(10) NULL,
    otp_expiry DATETIME NULL,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS borrowers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NULL,
    fullname VARCHAR(100) NULL,
    course_year VARCHAR(100) NULL,
    item_to_borrow VARCHAR(150) NULL,
    return_date DATE NULL,
    return_time TIME NULL,
    selfie_image TEXT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS tbl_borrowers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NULL,
    fullname VARCHAR(100) NULL,
    course_year VARCHAR(100) NULL,
    item_to_borrow VARCHAR(150) NULL,
    return_date DATE NULL,
    return_time TIME NULL,
    selfie_image TEXT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS borrowed_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NULL,
    item_to_borrow VARCHAR(150) NULL,
    equipment_name VARCHAR(150) NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>
