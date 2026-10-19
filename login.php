<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kung naka-login na, diretso sa borrower dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && strtolower(trim($_SESSION['role'])) === 'student') {
    header("Location: borrower.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_id = trim($_POST['email_or_id'] ?? '');

    if (!empty($email_or_id)) {
        // 1. Mag-generate ng 6-digit OTP
        $otp = rand(100000, 999999);
        
        // 2. I-save sa Session (5 minutes expiration)
        $_SESSION['otp_code'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; 
        $_SESSION['temp_email'] = $email_or_id;

        // Pansamantalang user data para sa session pagkatapos mag-verify
        $_SESSION['temp_user_id'] = 1;
        $_SESSION['temp_role'] = 'student';
        $_SESSION['temp_full_name'] = 'Ryan Jay Leoligao';
        $_SESSION['temp_student_id'] = '903324454';
        $_SESSION['temp_course_year'] = 'BSIT-3B';

        // 3. Pagpapadala ng OTP gamit ang Brevo API (cURL)
        $brevo_api_key = "xkeysib-ILAGAY_ANG_IYONG_BREVO_API_KEY_DITO"; // Palitan ito ng iyong Brevo API Key
        
        $email_data = [
            "sender" => [
                "name" => "MQCQC Equipment System",
                "email" => "no-reply@yourdomain.com" // Palitan ng iyong verified sender email sa Brevo
            ],
            "to" => [
                [
                    "email" => $email_or_id
                ]
            ],
            "subject" => "Your Login OTP Code",
            "htmlContent" => "<p>Hello,</p><p>Your 6-digit OTP code for system login is: <strong>{$otp}</strong></p><p>This code will expire in 5 minutes.</p>"
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $brevo_api_key,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Suriin kung pumasok ang pagpapadala ng email
        if ($http_code >= 200 && $http_code < 300) {
            header("Location: verify_otp.php");
            exit();
        } else {
            // I-display ang eksaktong tugon galing sa Brevo para makita natin ang dahilan
            $error = "Brevo Error (HTTP $http_code): " . ($curl_error ? $curl_error : $response);
        }
    } else {
        $error = "Please enter your email or Student ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MQCQC Equipment Monitoring System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .login-card { background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; margin-top: 50px; }
        .btn-custom { background-color: #198754; color: white; }
        .btn-custom:hover { background-color: #157347; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-success">System Login</h4>
                        <p class="text-muted small">Enter your details to receive an OTP via email (Brevo)</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address or Student ID</label>
                            <input type="text" class="form-control" id="email" name="email_or_id" required placeholder="Enter your email or ID">
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-custom fw-bold py-2">Send OTP / Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="small text-muted">Don't have an account yet? <a href="registration.php" class="text-success fw-bold text-decoration-none">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
