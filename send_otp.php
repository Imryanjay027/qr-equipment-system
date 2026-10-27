<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection gamit ang Coolify Environment Variables
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : getenv('DB_PASS');
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// PHPMailer Requires (Direktang nakareperensya sa phpmailer folder nang walang /src/)
require_once 'phpmailer/Exception.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Magpatuloy sa paggawa at pagpapadala ng OTP dito...
