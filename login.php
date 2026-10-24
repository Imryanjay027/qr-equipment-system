<?php
session_start();

// Functional PHPMailer / Native SMTP function using Brevo / SMTP
function sendOtpEmail($toEmail, $otpCode) {
    $smtpHost = 'ssl://smtp.gmail.com';
    $smtpPort = 465;
    $username = 'ryanjayleoligao64@gmail.com';
    $password = 'rtse abkj wqbz oxcg'; // Iyong Gmail App Password

    $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }

    // Helper function para magbasa ng SMTP response
    $getResponse = function($socket) {
        $response = "";
        while ($str = fgets($socket, 512)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    };

    // Basahin ang initial greeting ng server
    $getResponse($socket);

    // EHLO Command
    fputs($socket, "EHLO localhost\r\n");
    $getResponse($socket);

    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $getResponse($socket);

    fputs($socket, base64_encode($username) . "\r\n");
    $getResponse($socket);

    fputs($socket, base64_encode($password) . "\r\n");
    $getResponse($socket);

    // MAIL FROM & RCPT TO
    fputs($socket, "MAIL FROM: <$username>\r\n");
    $getResponse($socket);

    fputs($socket, "RCPT TO: <$toEmail>\r\n");
    $getResponse($socket);

    // DATA Command
    fputs($socket, "DATA\r\n");
    $getResponse($socket);

    // Headers & Body
    $headers  = "From: Equipment Monitoring System <$username>\r\n";
    $headers .= "To: <$toEmail>\r\n";
    $headers .= "Subject: Your Login OTP Code\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message  = "<h2>Equipment Monitoring System</h2>";
    $message .= "<p>Ang iyong OTP Verification Code para makapag-login ay:</p>";
    $message .= "<h1 style='color: #2b6cb0; font-size: 32px;'>$otpCode</h1>";
    $message .= "<p>Valid ang code na ito sa loob ng 5 minuto. Huwag ipamahagi kanino man.</p>";

    $emailContent = $headers . "\r\n" . $message . "\r\n.\r\n";
    fputs($socket, $emailContent);
    $getResponse($socket);

    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}

// Logic para sa Form Handling (SQLite Database Integration)
$db = new SQLite3('/app/database.sqlite'); 

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $usernameInput = trim($_POST['username'] ?? '');
        $passwordInput = trim($_POST['password'] ?? '');

        // I-fetch ang user mula sa database
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', $usernameInput, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && password_verify($passwordInput, $result['password'])) {
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['pending_user_id'] = $result['id'];
            $_SESSION['pending_email'] = $result['email'];
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expires'] = time() + 300; // 5 minutes

            // Ipadala ang OTP via Gmail SMTP Port 465
            if (sendOtpEmail($result['email'], $otp)) {
                $_SESSION['step'] = 'verify_otp';
                $successMsg = "Napadala na ang OTP code sa iyong email (" . htmlspecialchars($result['email']) . ").";
            } else {
                $errorMsg = "Bigo sa pagpapadala ng OTP code. Paki-subukan muli.";
            }
        } else {
            $errorMsg = "Maling Username o Password!";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $enteredOtp = trim($_POST['otp'] ?? '');

        if (time() > ($_SESSION['otp_expires'] ?? 0)) {
            $errorMsg = "Expired na ang OTP code. Mag-login muli.";
            unset($_SESSION['step']);
        } elseif ($enteredOtp == ($_SESSION['otp_code'] ?? '')) {
            // Success Verification
            $_SESSION['user_id'] = $_SESSION['pending_user_id'];
            unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['step']);
            
            header('Location: index.php');
            exit;
        } else {
            $errorMsg = "Maling OTP Code! Paki-subukan muli.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="login-card">
    <h2 style="text-align: center;">System Login</h2>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['step']) && $_SESSION['step'] === 'verify_otp'): ?>
        <!-- STEP 2: OTP Verification Form -->
        <form action="login.php" method="POST">
            <input type="hidden" name="action" value="verify_otp">
            <div class="form-group">
                <label for="otp">Enter 6-Digit OTP Code:</label>
                <input type="text" name="otp" id="otp" required maxlength="6" placeholder="123456" autofocus>
            </div>
            <button type="submit">Verify OTP</button>
        </form>
    <?php else: ?>
        <!-- STEP 1: Credentials Form -->
        <form action="login.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
