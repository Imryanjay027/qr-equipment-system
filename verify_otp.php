<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');
    
    if (isset($_SESSION['otp_code']) && isset($_SESSION['otp_expiry'])) {
        if (time() > $_SESSION['otp_expiry']) {
            $error = "OTP code has expired. Please log in again.";
        } elseif (intval($entered_otp) === intval($_SESSION['otp_code'])) {
            // Success: I-set ang user session papuntang borrower dashboard
            $_SESSION['user_id'] = $_SESSION['temp_user_id'] ?? 1;
            $_SESSION['role'] = $_SESSION['temp_role'] ?? 'student';
            $_SESSION['full_name'] = $_SESSION['temp_full_name'] ?? 'Ryan Jay Leoligao';
            $_SESSION['student_id'] = $_SESSION['temp_student_id'] ?? '903324454';
            $_SESSION['course_year'] = $_SESSION['temp_course_year'] ?? 'BSIT-3B';
            
            // Linisin ang temporary session
            unset($_SESSION['otp_code'], $_SESSION['otp_expiry']);
            
            header("Location: borrower.php");
            exit();
        } else {
            $error = "Invalid OTP code. Please try again.";
        }
    } else {
        $error = "No OTP request found. Please log in again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MQCQC Equipment Monitoring System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .otp-card { background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; margin-top: 50px; }
        .btn-custom { background-color: #198754; color: white; }
        .btn-custom:hover { background-color: #157347; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="otp-card text-center">
                    <h4 class="fw-bold text-success mb-2">Enter OTP</h4>
                    <p class="text-muted small mb-4">Enter the 6-digit OTP sent to your email via Brevo</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="verify_otp.php" method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control text-center fs-4 tracking-wide" id="otp" name="otp" required placeholder="------" maxlength="6" autofocus>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-custom fw-bold py-2">Verify OTP</button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <p class="small text-muted">Back to <a href="login.php" class="text-success fw-bold text-decoration-none">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
