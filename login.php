<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connection.php');

$message = ''; 
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Hanapin ang user sa tbl_users
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? OR Email = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $db_role = strtolower(trim($row['role'] ?? 'student'));

            // Itabi sa Session (WAG SISIRAIN ANG SESSION)
            $_SESSION['otp'] = $otp;
            $_SESSION['temp_email'] = $email;
            $_SESSION['temp_user_id'] = $row['id'];
            $_SESSION['temp_user_role'] = $db_role;
            $_SESSION['otp_expiration'] = time() + (5 * 60);

            // BREVO API INTEGRATION
            $apiKey = 'IYONG_BREVO_API_KEY_HERE'; // Ilagay ang iyong Brevo API Key dito
            $url = 'https://api.brevo.com/v3/smtp/email';

            $data = [
                'sender' => [
                    'name' => 'MQCQC Equipment System',
                    'email' => 'no-reply@mqcqc.edu.ph' // Palitan ayon sa Brevo sender email mo
                ],
                'to' => [
                    ['email' => $email]
                ],
                'subject' => 'Your Login OTP Code - MQCQC System',
                'htmlContent' => "<h3>Your One-Time Password (OTP) is: <b>{$otp}</b></h3><p>This code will expire in 5 minutes.</p>"
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Lumipat sa verify_otp.php
            header("Location: verify_otp.php");
            exit();

        } else {
            $message = "Email address not found in our database. Please register first.";
            $status = "error";
        }
        $stmt->close();
    } else {
        $message = "Please enter a valid email address.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login via OTP - MQCQC Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .login-card { background: white; padding: 40px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 420px; text-align: center; }
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
    <img src="logo.jpg" alt="MQCQC Logo" onerror="this.src='uploads/default.png'">
    <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
    <p class="subtitle">Login via One-Time Password (OTP)</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your registered email">
        </div>
        <button type="submit" class="btn">Send OTP Code</button>
    </form>

    <div class="register-link">Don't have an account? <a href="register.php">Register here</a></div>
</div>

</body>
</html>
