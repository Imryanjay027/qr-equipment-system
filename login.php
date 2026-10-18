<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';
session_start();

// PHPMailer files (direkta na sa root directory)
require_once('PHPMailer.php');
require_once('SMTP.php');
require_once('Exception.php');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($email)) {
        $sql = "SELECT * FROM tbl_users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Store session data
                $_SESSION['user_id'] = $user['id'] ?? $user['user_id'] ?? 1;
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect based on role
                if ($user['role'] === 'Custodian') {
                    header("Location: custodian_dashboard.php");
                    exit();
                } else {
                    header("Location: borrower_dashboard.php");
                    exit();
                }
            } else {
                $error = "Email address not found. Please register first.";
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR-BASED EQUIPMENT MONITORING SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 500px;">
        <div class="card shadow border-0 p-4">
            <!-- Logo at Pamagat -->
            <div class="text-center mb-4">
                <img src="logo.jpg" alt="Logo" style="max-height: 80px;" class="mb-2">
                <h6 class="text-success fw-bold">"QR-BASED EQUIPMENT MONITORING SYSTEM"</h6>
                <p class="text-muted small mb-0">Account Login</p>
            </div>
            
            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold text-white">Login</button>
            </form>
            <div class="text-center mt-3">
                <p class="small text-muted">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
