<?php
session_start();

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
                <p>Mangyaring i-input ang code na ito sa login page. Huwag itong ibahagi kanino man.</p>
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

// ==========================================
// FORM HANDLING
// ==========================================
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'request_otp') {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expiration'] = time() + (5 * 60); // Valid for 5 minutes

            if (sendOTP($email, $otp)) {
                $message = "Matagumpay na naisend ang OTP sa iyong email!";
                $status = "success";
            } else {
                $message = "Nagkaroon ng problema sa pagpapadala ng OTP. Subukan ulit.";
                $status = "error";
            }
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
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { margin-bottom: 20px; color: #2e7d32; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background-color: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #1b5e20; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>MQCQC System Login</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="action" value="request_otp">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your registered email">
        </div>
        <button type="submit" class="btn">Send OTP</button>
    </form>
</div>

</body>
</html>
