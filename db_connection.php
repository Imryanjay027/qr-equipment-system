<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
// Sinisigurong sasaloin nito ang DB_PASSWORD man o DB_PASS ang naka-set sa environment variables
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = 3306;

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
