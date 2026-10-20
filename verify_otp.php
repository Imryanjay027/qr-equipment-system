<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kung walang OTP session, ibalik sa login
if (!isset($_SESSION['otp_code']) || !isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp'] ?? '');

    if (time() > $_SESSION['otp_expiry']) {
        $error = "Expired na ang OTP. Mangyaring mag-login ulit.";
        unset($_SESSION['otp_code'], $_SESSION['otp_expiry'], $_SESSION['temp_user_id']);
    } 
    else if ($user_otp == $_SESSION['otp_code']) {
        
        // I-set ang permanenteng login sessions
        $_SESSION['user_id']      = $_SESSION['temp_user_id'];
        $_SESSION['user']         = $_SESSION['temp_user_id'];
        $_SESSION['logged_in']    = true;
        $_SESSION['is_logged_in'] = true;
        
        $_SESSION['role']         = strtolower(trim($_SESSION['temp_role']));
        $_SESSION['full_name']    = $_SESSION['temp_full_name'];
        $_SESSION['student_id']   = $_SESSION['temp_student_id'];

        // Linisin ang temporary session variables
        unset($_SESSION['otp_code']);
        unset($_SESSION['otp_expiry']);
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_role']);
        unset($_SESSION['temp_full_name']);
        unset($_SESSION['temp_student_id']);

        // Redirect batay sa role
        $role = strtolower(trim($_SESSION['role']));

        if ($role === 'custodian') {
            header("Location: custodian_dashboard.php");
            exit();
        } else {
            header("Location: borrower.php");
            exit();
        }

    } else {
        $error = "Mali ang OTP code na inilagay mo. Pakisubukan ulit.";
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
                <div class="otp-card">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-success">OTP Verification</h4>
                        <p class="text-muted small">Inilagay namin ang 6-digit code sa iyong email. Pakilagay sa ibaba:</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="verify_otp.php" method="POST">
                        <div class="mb-3">
                            <label for="otp" class="form-label fw-semibold">Enter 6-Digit OTP</label>
                            <input type="text" class="form-control text-center fs-4 fw-bold" id="otp" name="otp" maxlength="6" required placeholder="000000">
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-custom fw-bold py-2">Verify & Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-secondary small text-decoration-none">← Bumalik sa Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
