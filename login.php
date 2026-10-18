<?php
session_start();

// Database connection parameters
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$db   = 'qrcode';
$user = 'root';
$pass = 'wfbyfhr4404k2y9u2spiv101';
$charset = 'utf8mb4';

if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} elseif (file_exists('config.php')) {
    require_once 'config.php';
}

if (!isset($pdo) || $pdo === null) {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        $env_host = getenv('DB_HOST') ?: 'localhost';
        $env_db   = getenv('DB_NAME') ?: 'qrcode';
        $env_user = getenv('DB_USER') ?: 'root';
        $env_pass = getenv('DB_PASS') ?: '';
        
        try {
            $pdo = new PDO("mysql:host=$env_host;dbname=$env_db;charset=$charset", $env_user, $env_pass, $options);
        } catch (\PDOException $ex) {
            die("Database Connection Failed: " . $ex->getMessage());
        }
    }
}

// I-include ang PHPMailer files
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');

    if (empty($login_input)) {
        $error = "Please enter your Email or ID Number.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ? OR username = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();

        if ($user) {
            $email = $user['email'];
            
            // Mag-generate ng 6-digit OTP at expiration (10 mins)
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Ligtas na pag-update sa database (Hindi mag-a-abort o magfa-fatal error kahit magka-issue sa column)
            try {
                $update_stmt = $pdo->prepare("UPDATE tbl_users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
                $update_stmt->execute([$otp, $expiry, $user['id']]);
            } catch (\Throwable $e) {
                // Babalik sa Session kung sakaling magka-issue sa database column
            }

            $_SESSION['pending_otp_email'] = $email;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['otp_code'] = $otp;

            // Pagpapadala ng email gamit ang PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ryanjayleoligao64@gmail.com';
                $mail->Password   = 'joajicxaxmfmrdcm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('ryanjayleoligao64@gmail.com', 'QR Equipment Monitoring System');
                $mail->addAddress($email, $user['fullname']);

                $mail->isHTML(true);
                $mail->Subject = 'Your Login OTP Verification Code';
                $mail->Body    = "Hello <b>{$user['fullname']}</b>,<br><br>Your OTP verification code is: <h1>{$otp}</h1><br>This code is valid for 10 minutes. Please do not share it with anyone.";

                $mail->send();

                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Account not found. Please check your Email or ID Number.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR-Based Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 90px; height: auto; margin-bottom: 10px; display: inline-block; }
        h3 { margin: 5px 0 0 0; color: #2d6a4f; font-size: 1.15rem; font-weight: bold; }
        p.sub { font-size: 0.85rem; color: #666; margin-top: 5px; margin-bottom: 20px; }
        .alert { padding: 12px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 20px; text-align: left; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { font-size: 0.85rem; font-weight: bold; color: #222; display: block; margin-bottom: 6px; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box; font-size: 0.9rem; outline: none; }
        input:focus { border-color: #2d6a4f; box-shadow: 0 0 0 2px rgba(45, 106, 79, 0.15); }
        button[type="submit"] { width: 100%; padding: 12px; background-color: #2d6a4f; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.95rem; }
        button[type="submit"]:hover { background-color: #1b4332; }
        .reg-link { font-size: 0.85rem; margin-top: 20px; display: block; color: #555; text-decoration: none; }
        .reg-link a { color: #2d6a4f; font-weight: bold; text-decoration: none; }
        .reg-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <img src="logo.jpg" alt="School Logo" class="logo">
        <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
        <p class="sub">Account Login (OTP Authentication)</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address or ID Number</label>
                <input type="text" name="login_input" placeholder="Enter your Email or ID Number" required>
            </div>
            <button type="submit">Send OTP</button>
        </form>

        <p class="reg-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
