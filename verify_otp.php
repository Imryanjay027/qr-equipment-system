<?php
// verify_otp.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Kung walang temporary session ng email, ibalik sa login page
if (!isset($_SESSION['temp_email']) || !isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (!empty($otp_code)) {
        if (intval($otp_code) === intval($_SESSION['otp'])) {
            $role = strtolower(trim($_SESSION['temp_user_role'] ?? 'student'));
            
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            $_SESSION['email'] = $_SESSION['temp_email'];
            $_SESSION['role'] = $role;

            // Linisin ang temporary session values
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiration']);
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_role']);

            // Dynamic Redirection batay sa Role sa Database
            if ($role === 'custodian' || $role === 'property custodian') {
                $_SESSION['custodian_logged_in'] = true;
                header("Location: custodian_dashboard.php");
                exit();
            } else {
                $_SESSION['student_logged_in'] = true;
                header("Location: borrower.php");
                exit();
            }

        } else {
            $error_msg = "Mali ang OTP code. Pakitingnan muli ang iyong email.";
        }
    } else {
        $error_msg = "Mangyaring ilagay ang OTP code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MQCQC Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .login-card { background: white; padding: 40px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 420px; text-align: center; }
        .login-card img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; }
        .login-card h3 { margin: 10px 0 5px 0; color: #212529; font-size: 18px; font-weight: bold; }
        .login-card p.subtitle { color: #6c757d; font-size: 13px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #212529; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; text-align: center; letter-spacing: 3px; font-size: 18px; }
        .btn { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background-color 0.2s; }
        .btn:hover { background-color: #1b5e20; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="login-card">
    <img src="logo.jpg" alt="MQCQC Logo" onerror="this.src='uploads/default.png'">
    <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
    <p class="subtitle">Enter One-Time Password (OTP)</p>

    <!-- Pansamantalang Debug para makita kung anong role ang binabasa mula sa database -->
    <div style="background: #e2f0cb; padding: 8px; margin-bottom: 15px; border-radius: 5px; font-size: 12px; color: #2e7d32; font-weight: bold;">
        DEBUG ROLE: [<?php echo htmlspecialchars($_SESSION['temp_user_role'] ?? 'wala'); ?>]
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form method="POST" action="verify_otp.php">
        <div class="form-group">
            <label for="otp_code">OTP Verification Code</label>
            <input type="text" id="otp_code" name="otp_code" required placeholder="6-Digit OTP" maxlength="6" autocomplete="off">
        </div>
        <button type="submit" class="btn">Verify & Proceed</button>
    </form>
</div>

</body>
</html>
