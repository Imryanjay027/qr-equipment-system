<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'qrcode';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-patcher para siguraduhing kumpleto ang columns at table sa tbl_users
$conn->query("CREATE TABLE IF NOT EXISTS tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) DEFAULT 'Student',
    username VARCHAR(100),
    student_id VARCHAR(50),
    fullname VARCHAR(150),
    course_year VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ligtas na pagdaragdag ng mga kolum gamit ang try-catch para maiwasan ang duplicate error
$columns_to_add = [
    "ALTER TABLE tbl_users ADD COLUMN student_id VARCHAR(50)",
    "ALTER TABLE tbl_users ADD COLUMN course_year VARCHAR(100)",
    "ALTER TABLE tbl_users ADD COLUMN photo VARCHAR(255)",
    "ALTER TABLE tbl_users ADD COLUMN role VARCHAR(50) DEFAULT 'Student'"
];

foreach ($columns_to_add as $sql) {
    try {
        $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        // Huwag pansinin kung mayroon nang kolum
    }
}
?>
