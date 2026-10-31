<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solid Database Connection na may tamang Coolify / Docker Fallback
$hosts = [
    getenv('MYSQL_HOST'),
    getenv('DB_HOST'),
    'mysql',          // Karaniwang host name sa Coolify/Docker networks
    '127.0.0.1',
    'localhost'
];

$port    = getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';
$db      = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'qr_equipment_db';
$user    = getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
$pass    = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$charset = 'utf8mb4';

$pdo = null;
$dbStatus = "";

foreach ($hosts as $h) {
    if (empty($h)) continue;
    try {
        $dsn = "mysql:host=$h;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        $dbStatus = "Connected via host: $h";
        break;
    } catch (\PDOException $e) {
        $dbStatus = "Failed on $h: " . $e->getMessage();
    }
}
