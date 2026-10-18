<?php
session_start();
include 'db_connection.php';

// 1. I-declare ang PHPMailer Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 2. I-require ang PHPMailer files sa TAMANG PAGKAKASUNOD-SUNOD
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/PHPMailer.php';

// Code para sa Send OTP Form Submission
if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];

    // Mag-generate ng 6-digit OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['email'] = $email;

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // Palitan ng email mo
        $mail->Password   = 'your_app_password';   // Palitan ng App Password mo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'QR Equipment System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP Code';
        $mail->Body    = "Hello, <br><br>Your OTP code for login is: <b>$otp</b>";

        $mail->send();
        $msg = "OTP has been sent to your email!";
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - QR Equipment System</title>
</head>
<body>
    <h2>Login with OTP</h2>
    
    <?php if (isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <label>Email Address:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit" name="send_otp">Send OTP</button>
    </form>
</body>
</html>
