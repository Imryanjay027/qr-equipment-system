<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'qrcode';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Gumawa ng table kung wala pa na kumpleto ang kolum kasama ang password
$conn->query("CREATE TABLE IF NOT EXISTS tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) DEFAULT 'Student',
    username VARCHAR(100),
    student_id VARCHAR(50),
    fullname VARCHAR(150),
    course_year VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255) NULL,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 2. Ligtas na idagdag ang bawat kolum kung sakaling lumang table ang nauna
$columns = [
    "ALTER TABLE tbl_users ADD COLUMN role VARCHAR(50) DEFAULT 'Student'",
    "ALTER TABLE tbl_users ADD COLUMN username VARCHAR(100)",
    "ALTER TABLE tbl_users ADD COLUMN student_id VARCHAR(50)",
    "ALTER TABLE tbl_users ADD COLUMN fullname VARCHAR(150)",
    "ALTER TABLE tbl_users ADD COLUMN course_year VARCHAR(100)",
    "ALTER TABLE tbl_users ADD COLUMN email VARCHAR(150)",
    "ALTER TABLE tbl_users ADD COLUMN password VARCHAR(255) NULL",
    "ALTER TABLE tbl_users ADD COLUMN photo VARCHAR(255)"
];

foreach ($columns as $sql) {
    try {
        $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        // Hahayaan lang kung umiiral na ang kolum para hindi mag-error
    }
}
?>
