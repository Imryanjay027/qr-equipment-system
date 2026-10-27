<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection - Gamitin ang 'qrcode' bilang fallback kung walang DB_NAME sa environment
$host = getenv('DB_HOST') ?: 'wfbyfhr4404k2y9u2spiv101';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'qrcode';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'ryan072797';

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

// Kunin ang email mula sa POST o SESSION
$email = $_POST['email'] ?? $_SESSION['email'] ?? 'ryancabasag64@gmail.com';

if (!empty($email)) {
    // I-save ang temp_email sa session para mabasa ng verify_otp.php
    $_SESSION['temp_email'] = $email;

    $otp = rand(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // I-save ang OTP at Expiration sa database ng qrcode.users
    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
    $stmt->execute([$otp, $expires_at, $email]);

    $mail = new PHPMailer(true);
    try {
        // Brevo SMTP Relay Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bed417001@smtp-brevo.com';
        $mail->Password   = 'xsmtpsib-654e3a69f3ede9ebad7b087075af141adcb0d07d749bb609b5bb13d12ae33b40-bnm2a9542oVHPC6K';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender: Verified email sa Brevo
        $mail->setFrom('ryanjayleoligao64@gmail.com', 'MQCQC Equipment Monitoring');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'MQCQC System - Your OTP Code';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #0056b3;">MQCQC Equipment Monitoring System</h2>
                <p>Magandang araw!</p>
                <p>Ang iyong One-Time Password (OTP) para sa pag-login ay:</p>
                <h1 style="background-color: #f4f4f4; padding: 10px 20px; display: inline-block; letter-spacing: 4px; color: #333;">' . $otp . '</h1>
                <p>Mag-e-expire ang kodigong ito sa loob ng 5 minuto.</p>
            </div>
        ';

        $mail->send();
        
        // I-redirect sa verify_otp.php para ma-input ang OTP
        header("Location: verify_otp.php");
        exit();

    } catch (Exception $e) {
        echo "Hindi naipadala ang email. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Walang nahanap na email address para pagpadalan ng OTP.";
}
?>
