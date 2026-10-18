<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Ibinook-mark pabalik sa tbl_users para mag-match sa database table name
        $sql = "SELECT * FROM tbl_users WHERE email = ? OR Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Gumawa ng 6-digit OTP
            $otp = rand(100000, 999999);
            
            // I-save sa session ang OTP at temporary user data
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes expiration
            $_SESSION['temp_user_id'] = $row['id'];
            $_SESSION['temp_user_role'] = $row['role'] ?? 'Student';
            $_SESSION['temp_email'] = $email;

            // PHPMailer Loading gamit ang Absolute Path (__DIR__)
            require_once __DIR__ . '/phpmailer/PHPMailer.php';
            require_once __DIR__ . '/phpmailer/SMTP.php';
            require_once __DIR__ . '/phpmailer/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Server settings para sa Gmail SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ryanjayleoligao64@gmail.com';
                $mail->Password   = 'pjldvtgclahzzggj'; // App Password nang walang space
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('ryanjayleoligao64@gmail.com', 'MQCQC System');
                $mail->addAddress($email);

                // I-embed ang logo.jpg para lumabas bilang school logo sa email body
                if (file_exists('logo.jpg')) {
                    $mail->addEmbeddedImage('logo.jpg', 'school_logo');
                }

                // Content (HTML Template na may updated na buong title)
                $mail->isHTML(true);
                $mail->Subject = 'Your Login OTP Code - MQCQC System';
                $mail->Body    = '
                <div style="background-color: #f4f6f9; padding: 20px; font-family: Arial, sans-serif;">
                    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e0e0e0; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                        <img src="cid:school_logo" alt="MQCQC Logo" style="width: 90px; height: auto; margin-bottom: 15px;">
                        <h2 style="color: #1a365d; margin: 0; font-size: 18px;">Mary The Queen College of Quezon City</h2>
                        <p style="color: #666666; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 8px; margin-bottom: 20px; line-height: 1.4;">QR - BASED SCHOOL EQUIPMENT BORROWING, RETURN, AND CONDITION MONITORING SYSTEM</p>
                        <hr style="border: none; border-top: 1px solid #eeeeee; margin: 20px 0;">
                        <p style="text-align: left; color: #333333; font-size: 14px; margin-bottom: 5px;">Hello,</p>
                        <p style="text-align: left; color: #333333; font-size: 14px; margin-top: 0;">Here is your 6-digit OTP code for logging in:</p>
                        <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin: 20px 0; display: inline-block; width: 80%; border: 1px solid #ddd;">
                            <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #1a365d;">' . $otp . '</span>
                        </div>
                        <p style="color: #888888; font-size: 12px; margin-top: 30px;">It will expire in 5 minutes.</p>
                    </div>
                </div>';

                if ($mail->send()) {
                    header("Location: verify_otp.php");
                    exit();
                }
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Email address not found in our database.";
        }
    } else {
        $error = "Please enter your registered email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mary The Queen College of Quezon City</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 15px;
        }
        .login-card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); 
            max-width: 480px;
            width: 100%;
        }
        .btn-primary { 
            background-color: #0d6efd; 
            border: none; 
        }
        .btn-primary:hover { 
            background-color: #0b5ed7; 
        }
    </style>
</head>
<body>

    <div class="card login-card p-4 bg-white">
        <div class="text-center mb-4">
            <img src="logo.jpg" alt="Logo" class="mb-2" style="max-height: 70px;" onerror="this.style.display='none';">
            <h5 class="fw-bold text-primary mb-1" style="font-size: 13px; line-height: 1.4;">"QR - BASED SCHOOL EQUIPMENT BORROWING, RETURN, AND CONDITION MONITORING SYSTEM"</h5>
            <p class="text-muted small mb-0">Enter your registered email to log in</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger py-2 text-center small" role="alert">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold text-secondary small">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Send OTP</button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register</a></p>
        </div>
    </div>

</body>
</html>
