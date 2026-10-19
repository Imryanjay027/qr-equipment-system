<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp'] ?? '');
    
    if (isset($_SESSION['otp'], $_SESSION['otp_expiry']) && time() <= $_SESSION['otp_expiry']) {
        if ($entered_otp == $_SESSION['otp']) {
            
            $user_id = $_SESSION['temp_user_id'] ?? '';
            
            if (!empty($user_id)) {
                $sql = "SELECT * FROM tbl_users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    // Itakda ang permanenteng session flag para hindi ma-log out tuwing magre-refresh
                    $_SESSION['logged_in'] = true;
                    
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'] ?? $row['Email'];
                    $_SESSION['fullname'] = $row['fullname'] ?? $row['name'] ?? '';
                    $_SESSION['student_id'] = $row['student_id'] ?? $row['username'] ?? '';
                    $_SESSION['course_year'] = $row['course_year'] ?? '';
                    $_SESSION['role'] = $row['role'] ?? 'Student';
                    $_SESSION['photo'] = $row['photo'] ?? '';
                    
                    // Linisin ang temporary session variables
                    unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['temp_user_id'], $_SESSION['temp_user_role'], $_SESSION['temp_email']);
                    
                    // Tamang pag-redirect batay sa database role
                    $role = strtolower(trim($row['role'] ?? ''));
                    if ($role === 'custodian') {
                        header("Location: custodian_dashboard.php");
                        exit();
                    } else {
                        header("Location: borrower.php");
                        exit();
                    }
                }
            }

        } else {
            $error = "Invalid OTP code. Please try again.";
        }
    } else {
        $error = "OTP code has expired. Please log in again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Mary The Queen College of Quezon City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verify-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card verify-card p-4 bg-white">
                    <div class="text-center mb-4">
                        <img src="logo.jpg" alt="Logo" class="mb-2" style="max-height: 70px;">
                        <h4 class="fw-bold text-primary mb-1">Mary The Queen College of Quezon City</h4>
                        <p class="text-muted small">Enter the 6-digit OTP sent to your email</p>
                    </div>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger py-2 text-center small" role="alert">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold text-secondary small">OTP Code</label>
                            <input type="text" class="form-control text-center tracking-widest" id="otp" name="otp" placeholder="------" maxlength="6" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Verify OTP</button>
                    </form>

                    <div class="text-center mt-3">
                        <p class="small text-muted mb-0">Back to <a href="logout.php" class="text-decoration-none fw-bold">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
