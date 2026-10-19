<?php
// verify_otp.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Siguraduhing may na-request na OTP at email
if (!isset($_SESSION['login_email']) || !isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit();
}

// Gamitin ang db_connection.php para sa koneksyon
if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    $host = "wfbyfhr4404k2y9u2spiv101";
    $username = "root";
    $password = "cW86JONRjM797hW23852myqva2R4yZKKCcJ9o41Hd8ViLXYCAy2MQchbytcIY0ke";
    $database = "qrcode";
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
}

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (!empty($otp_code)) {
        // Suriin kung tugma ang in-input na OTP sa session OTP
        if (intval($otp_code) === intval($_SESSION['otp'])) {
            
            // I-set ang mga sessions na kailangan ng custodian dashboard
            $_SESSION['custodian_logged_in'] = true;
            $_SESSION['logged_in'] = true;
            
            // Linisin ang temporary OTP session para hindi na magamit ulit
            unset($_SESSION['otp']);

            // Direktang i-redirect sa custodian_dashboard.php nang walang delay
            header("Location: custodian_dashboard.php");
            exit();
        } else {
            $error_msg = "Mali ang OTP code. Pakitingnan muli ang iyong Gmail.";
        }
    } else {
        $error_msg = "Mangyaring ilagay ang OTP code.";
    }
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Based Equipment Monitoring System - Verify OTP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .login-card { background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 450px; text-align: center; }
        .logo-container img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 15px; }
        .system-title { font-size: 16px; font-weight: 700; color: #1b4332; margin-bottom: 5px; line-height: 1.4; }
        .system-subtitle { font-size: 13px; color: #555; margin-bottom: 25px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #222; font-size: 13px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; outline: none; transition: border-color 0.2s; text-align: center; letter-spacing: 2px; }
        .form-group input:focus { border-color: #2d6a4f; }
        .btn-login { background-color: #2d6a4f; color: white; padding: 12px; border: none; border-radius: 4px; width: 100%; font-size: 15px; cursor: pointer; font-weight: 600; transition: background-color 0.2s; }
        .btn-login:hover { background-color: #1b4332; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; border: 1px solid #f5c6cb; text-align: left; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="logo.jpg" alt="Mary The Queen College Logo" onerror="this.src='uploads/default.png'">
        </div>
        
        <div class="system-title">"QR-BASED EQUIPMENT MONITORING SYSTEM"</div>
        <div class="system-subtitle">Enter Verification Code (OTP)</div>

        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="otp_code">OTP Code</label>
                <input type="text" id="otp_code" name="otp_code" required placeholder="Enter 6-digit OTP" autocomplete="off" maxlength="6">
            </div>
            <button type="submit" class="btn-login">Verify OTP</button>
        </form>
    </div>
</body>
</html>
