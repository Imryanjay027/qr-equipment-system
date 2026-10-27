<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : getenv('DB_PASS');

// Sinisigurong sasaloin nito ang DB_DATABASE o DB_NAME man ang nakalagay
$dbname = getenv('DB_DATABASE') !== false ? getenv('DB_DATABASE') : getenv('DB_NAME');
$port = 3306;

$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
