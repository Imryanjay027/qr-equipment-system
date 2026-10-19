<?php
session_start();
include 'db_connection.php';

// 1. PHPMailer Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 2. I-require ang PHPMailer files sa TAMANG PAGKAKASUNOD-SUNOD
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/PHPMailer.php';

$msg = "";
$error = "";

// Handle Send OTP
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
            $mail->Username   = 'your_email@gmail.com'; // Palitan ng totoong email address
            $mail->Password   = 'your_app_password';   // Palitan ng Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'QR Equipment Monitoring System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Login OTP Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <h2 style='color: #0d6efd;'>QR Equipment System</h2>
                    <p>Ang iyong OTP Code para sa pag-login ay:</p>
                    <h1 style='color: #333; letter-spacing: 5px;'>$otp</h1>
                    <p>Huwag itong ipamigay kanino man.</p>
                </div>
            ";

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

    <!-- Bootstrap 5 CDN para sa magandang UI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            padding: 30px;
        }
        .system-title {
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .btn-primary {
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h4 class="system-title">QR Equipment System</h4>
        <p class="text-muted small">Equipment Borrowing, Return & Monitoring</p>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label font-weight-bold">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
        </div>

        <button type="submit" name="send_otp" class="btn btn-primary w-100">
            Send OTP Code
        </button>
    </form>

    <div class="text-center mt-4 border-top pt-3">
        <small class="text-muted">&copy; Mary the Queen College of Quezon City</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
