<?php
// login.php
session_start();
include 'db_connection.php'; // Siguraduhing tama ang pangalan ng connection file mo

// ==========================================
// BREVO API EMAIL FUNCTION (cURL)
// ==========================================
function sendOTP($toEmail, $otpCode) {
    $apiKey = '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';
    $url = 'https://api.brevo.com/v3/smtp/email';

    $data = array(
        'sender' => array(
            'name' => 'MQCQC Equipment System',
            'email' => 'ryanjayleoligao64@gmail.com'
        ),
        'to' => array(
            array(
                'email' => $toEmail
            )
        ),
        'subject' => 'Your Login OTP Code - MQCQC System',
        'htmlContent' => "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px; max-width: 500px;'>
                <h2 style='color: #2e7d32;'>MQCQC Equipment Monitoring System</h2>
                <p>Hello,</p>
                <p>Ang iyong One-Time Password (OTP) para sa pag-login ay:</p>
                <h1 style='color: #1b5e20; letter-spacing: 4px; background-color: #f4f4f4; padding: 10px; display: inline-block; border-radius: 4px;'>{$otpCode}</h1>
                <p>Mangyaring i-input ang code na ito sa OTP verification page. Huwag itong ibahagi kanino man.</p>
                <br>
                <small style='color: #777;'>Awtomatikong mensahe mula sa MQCQC System.</small>
            </div>
        "
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode == 201);
}

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'request_otp') {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            // I-check muna sa database kung rehistrado ang email at kunin ang role
            $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? OR Email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $otp = sprintf("%06d", mt_rand(1, 999999));
                
                // I-save ang mga detalye sa session para magamit sa verify_otp.php
                $_SESSION['otp'] = $otp;
                $_SESSION['login_email'] = $email;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_user_id'] = $row['id'];
                $_SESSION['temp_user_role'] = $row['role'] ?? 'student';
                $_SESSION['otp_expiration'] = time() + (5 * 60);

                if (sendOTP($email, $otp)) {
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $message = "Nagkaroon ng problema sa pagpapadala ng OTP. Subukan ulit.";
                    $status = "error";
                }
            } else {
                $message = "Ang email na ito ay hindi pa rehistrado sa sistema.";
                $status = "error";
            }
            $stmt->close();
        } else {
            $message = "Mangyaring maglagay ng tamang email address.";
            $status = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MQCQC Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 450px; text-align: center; }
        .login-card img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; }
        .login-card h3 { margin: 10px 0 5px 0; color: #212529; font-size: 18px; font-weight: bold; }
        .login-card p.subtitle { color: #6c757d; font-size: 13px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #212529; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background-color 0.2s; }
        .btn:hover { background-color: #1b5e20; }
        .register-link { margin-top: 20px; font-size: 13px; color: #6c757d; }
        .register-link a { color: #2e7d32; text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="login-card">
    <img src="logo.jpg" alt="MQCQC Logo">
    <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
    <p class="subtitle">Account Login (OTP Authentication)</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="action" value="request_otp">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your Email">
        </div>
        <button type="submit" class="btn">Send OTP</button>
    </form>

    <div class="register-link">Don't have an account? <a href="register.php">Register here</a></div>
</div>

</body>
</html>
