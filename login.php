<?php
// login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Coolify Database Connection
$host = "wfbyfhr4404k2y9u2spiv101";
$username = "root";
$password = "cW86JONRjM797hW23852myqva2R4yZKKCcJ9o41Hd8ViLXYCAy2MQchbytcIY0ke";
$database = "qrcode";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_val = trim($_POST['email_or_id'] ?? '');

    if (!empty($input_val)) {
        // Halimbawa ng pag-verify o pag-handle ng OTP login
        $stmt = $conn->prepare("SELECT id, fullname, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $input_val, $input_val);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Ilagay dito ang sunod na hakbang para sa OTP kung kinakailangan
            $_SESSION['temp_user_id'] = $user['id'];
            // header("Location: verify_otp.php");
            // exit();
        } else {
            $error_msg = "Walang nahanap na account para sa Email o ID na ito.";
        }
        $stmt->close();
    } else {
        $error_msg = "Mangyaring ilagay ang iyong Email o ID Number.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Based Equipment Monitoring System - Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .login-card { background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 450px; text-align: center; }
        .logo-container img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 15px; }
        .system-title { font-size: 16px; font-weight: 700; color: #1b4332; margin-bottom: 5px; line-height: 1.4; }
        .system-subtitle { font-size: 13px; color: #555; margin-bottom: 25px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #222; font-size: 13px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-group input:focus { border-color: #2d6a4f; }
        .btn-login { background-color: #2d6a4f; color: white; padding: 12px; border: none; border-radius: 4px; width: 100%; font-size: 15px; cursor: pointer; font-weight: 600; transition: background-color 0.2s; }
        .btn-login:hover { background-color: #1b4332; }
        .register-link { margin-top: 20px; font-size: 13px; color: #555; }
        .register-link a { color: #2d6a4f; text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; border: 1px solid #f5c6cb; text-align: left; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <!-- Palitan ang src kung iba ang pangalan o lokasyon ng logo image file mo -->
            <img src="logo.png" alt="Mary The Queen College Logo" onerror="this.src='uploads/default.png'">
        </div>
        
        <div class="system-title">"QR-BASED EQUIPMENT MONITORING SYSTEM"</div>
        <div class="system-subtitle">Account Login (OTP Authentication)</div>

        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email_or_id">Email Address or ID Number</label>
                <input type="text" id="email_or_id" name="email_or_id" required placeholder="Enter your Email or ID Number">
            </div>
            <button type="submit" class="btn-login">Send OTP</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
