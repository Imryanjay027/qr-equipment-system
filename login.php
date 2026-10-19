<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ligtas na pagsusuri: Kung nakalog-in na ang student, direktang dalhin sa borrower.php
// (Hindi ito mag-loloop dahil hiwalay ang destinasyon nito)
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && strtolower(trim($_SESSION['role'])) === 'student') {
    header("Location: borrower.php");
    exit();
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

                    <!-- Login Form patungong OTP verification -->
                    <form action="verify_otp.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address or Student ID</label>
                            <input type="text" class="form-control" id="email" name="email_or_id" required placeholder="Enter your email or ID">
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-custom fw-bold py-2">Send OTP / Login</button>
                        </div>
                    </form>

                    <!-- Nakaturo na nang tama sa registration.php -->
                    <div class="text-center mt-3">
                        <p class="small text-muted">Don't have an account yet? <a href="registration.php" class="text-success fw-bold text-decoration-none">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
