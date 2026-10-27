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
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// PHPMailer Namespaces at Files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        die("Error: Email is required.");
    }
    
    // 1. Suriin kung nag-e-exist ang email sa users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        echo "<script>alert('Email not registered yet! Please register first.'); window.location.href='register.php';</script>";
        exit();
    }

    // 2. Gumawa ng random 6-digit OTP at expiration (5 minutes)
    $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));
    
    // 3. I-update ang database para i-save ang OTP
    $update_stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
    $update_stmt->execute([$otp, $expires, $email]);

    // 4. Ipadala ang OTP gamit ang PHPMailer at Gmail SMTP
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ryanjayleoligao64@gmail.com';
        $mail->Password   = 'wfpwkymohpmghuqz'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('ryanjayleoligao64@gmail.com', 'QR Equipment Monitoring System');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body    = '<b>Hello!</b><br>Your OTP verification code for the Equipment Monitoring System is: <h3>' . $otp . '</h3><br>This code will expire in 5 minutes.';

        $mail->send();
        
        // I-save sa session para sa verification step
        $_SESSION['temp_email'] = $email;
        $_SESSION['login_email'] = $email;

        // 5. Mag-redirect na patungong verify_otp.php
        header("Location: verify_otp.php");
        exit();

    } catch (Exception $e) {
        die("Hindi maipadala ang email. Error: {$mail->ErrorInfo}");
    }
}
?>
