<?php
session_start();
include 'db_connection.php';

// 1. PHPMailer Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 2. I-require ang PHPMailer sa tamang pagkakasunod-sunod
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/PHPMailer.php';

$msg = "";
$error = "";

// Process Send OTP
if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@gmail.com'; // Palitan ng iyong Gmail
            $mail->Password   = 'your_app_password';   // Palitan ng iyong Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'QR Equipment System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Login OTP Code';
            $mail->Body    = "Ang iyong OTP code para sa pag-login ay: <b>$otp</b>";

            $mail->send();
            $msg = "Naipadala na ang OTP sa iyong email!";
        } catch (Exception $e) {
            $error = "Hindi maipadala ang email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Mangyaring ilagay ang iyong email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #0d6efd;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .title {
            color: #0d6efd;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #212529;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;
        }

        .btn-send {
            width: 100%;
            background-color: #0d6efd;
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-send:hover {
            background-color: #0b5ed7;
        }

        .alert {
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .footer-text {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h1 class="title">QR Equipment System</h1>
    <p class="subtitle">Equipment Borrowing, Return & Monitoring</p>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required>
        </div>

        <button type="submit" name="send_otp" class="btn-send">
            Send OTP Code
        </button>
    </form>

    <div class="footer-text">
        &copy; Mary the Queen College of Quezon City
    </div>
</div>

</body>
</html>
