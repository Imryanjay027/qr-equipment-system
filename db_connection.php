<?php
// Kunin ang configuration mula sa Coolify Environment Variables
$host     = getenv('DB_HOST') ?: '172.17.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: ''; // Ilagay ang password kung meron
$dbname   = getenv('DB_NAME') ?: 'qrcode';
$port     = getenv('DB_PORT') ?: '3306';

// Gamitin ang MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Suriin kung may error sa koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Likhain ang tbl_users table kung wala pa kasama ang mga tamang kolum
$sql_create = "CREATE TABLE IF NOT EXISTS tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'Student',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_create);

// Automaticong idagdag ang 'email' column kung wala pa sa lumang table
$check_email = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'email'");
if ($check_email->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_users ADD COLUMN email VARCHAR(100) NOT NULL");
}

// Awtomatikong idagdag ang 'role' column kung wala pa
$check_role = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'role'");
if ($check_role->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_users ADD COLUMN role VARCHAR(50) DEFAULT 'Student'");
}
?>
