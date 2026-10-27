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

// PHPMailer Requires
require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'] ?? $_SESSION['email'] ?? 'ryanjayleoligao64@gmail.com';

if (!empty($email)) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    $mail = new PHPMailer(true);
    try {
        // Server settings para sa Brevo SMTP Relay
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bed417001@smtp-brevo.com'; // Eksaktong Login mula sa Brevo[cite: 10]
        $mail->Password   = 'xsmtpsib-654e3a69f3ede9ebad7b087075af141adcb0d07d749bb609b5bb13d12ae33b40-bnm2a9542oVHPC6K'; // Ang iyong SMTP Key[cite: 10]
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('ryanjayleoligao64@gmail.com', 'MQCQC Equipment Monitoring');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = 'Your One-Time Password (OTP) is: <b>' . $otp . '</b>';

        $mail->send();
        echo "Tagumpay! Naipadala na ang OTP sa iyong email ($email).";
    } catch (Exception $e) {
        echo "Hindi naipadala ang email. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Walang nahanap na email address para pagpadalan ng OTP.";
}
?>
