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

// Kukunin ang email ng user mula sa POST request (login/register form) o SESSION
$email = $_POST['email'] ?? $_SESSION['email'] ?? 'ryancabasag64@gmail.com';

if (!empty($email)) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    $mail = new PHPMailer(true);
    try {
        // Brevo SMTP Relay Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bed417001@smtp-brevo.com'; // Ang iyong Brevo Login
        $mail->Password   = 'xsmtpsib-654e3a69f3ede9ebad7b087075af141adcb0d07d749bb609b5bb13d12ae33b40-bnm2a9542oVHPC6K'; // Ang iyong Active SMTP Key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender: LAGING ang iyong Verified Email sa Brevo
        $mail->setFrom('ryanjayleoligao64@gmail.com', 'MQCQC Equipment Monitoring');

        // Recipient: Ang email address ng USER na kailangan ng OTP
        $mail->addAddress($email);

        // Content ng Email
        $mail->isHTML(true);
        $mail->Subject = 'MQCQC System - Your OTP Code';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #0056b3;">MQCQC Equipment Monitoring System</h2>
                <p>Magandang araw!</p>
                <p>Ang iyong One-Time Password (OTP) para sa pag-login ay:</p>
                <h1 style="background-color: #f4f4f4; padding: 10px 20px; display: inline-block; letter-spacing: 4px; color: #333;">' . $otp . '</h1>
                <p>Gamitin ang kodigo na ito upang makapasok sa system. Huwag ibahagi ang kodigong ito sa iba.</p>
            </div>
        ';

        $mail->send();
        echo "Tagumpay! Naipadala na ang OTP sa iyong email ($email).";
    } catch (Exception $e) {
        echo "Hindi naipadala ang email. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Walang nahanap na email address para pagpadalan ng OTP.";
}
?>
