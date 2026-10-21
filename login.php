<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Siguraduhing may OTP columns
try { @$conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE users ADD COLUMN otp_expiry DATETIME DEFAULT NULL"); } catch (Exception $e) {}

$error = "";
$redirect_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_id = trim($_POST['email_or_id'] ?? '');

    if (!empty($email_or_id)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE TRIM(email) = TRIM(?) OR TRIM(student_id) = TRIM(?)");
        $stmt->bind_param("ss", $email_or_id, $email_or_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate 6-digit OTP
            $otp = sprintf("%06d", rand(0, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Update OTP sa database
            $update_stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $otp, $expiry, $user['id']);
            $update_stmt->execute();

            $target_email = trim($user['email']);
            $brevo_api_key = "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"; 
            
            $email_data = [
                "sender" => [
                    "name" => "MQCQC Equipment System",
                    "email" => "ryanjayleoligao64@gmail.com"
                ],
                "to" => [ ["email" => $target_email] ],
                "subject" => "Your Login OTP Code",
                "htmlContent" => "<p>Hello {$user['full_name']},</p><p>Your 6-digit OTP code is: <strong>{$otp}</strong></p><p>This code will expire in 15 minutes.</p>"
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
            curl_close($ch);

            if ($http_code >= 200 && $http_code < 300) {
                $redirect_url = "verify_otp.php";
            } else {
                $error = "Failed to send OTP via email.";
            }
        } else {
            $error = "Email or Student ID not found in database.";
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
    <title>Login - QR-Based Equipment Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 30px 25px;
            background: #ffffff;
        }
        .logo-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        .system-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111111;
            letter-spacing: 0.5px;
        }
        .system-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .btn-green {
            background-color: #217835;
            border-color: #217835;
            color: #ffffff;
            font-weight: 600;
            padding: 10px;
            border-radius: 8px;
        }
        .btn-green:hover {
            background-color: #1a602a;
            border-color: #1a602a;
            color: #ffffff;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #217835;
            box-shadow: 0 0 0 0.2rem rgba(33, 120, 53, 0.25);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-6 col-lg-4">
                <div class="card login-card text-center">
                    
                    <!-- School Logo with Fallback -->
                    <div class="mb-3">
                        <img src="https://upload.wikimedia.org/wikipedia/en/8/82/Mary_the_Queen_College_of_Quezon_City_logo.png" 
                             onerror="this.onerror=null; this.src='logo.jpg';" 
                             alt="MQCQC Logo" 
                             class="logo-img">
                    </div>

                    <!-- System Header -->
                    <h5 class="system-title mb-1">"QR-BASED EQUIPMENT MONITORING SYSTEM"</h5>
                    <p class="system-subtitle mb-4">Account Login (OTP Authentication)</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small text-start"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($redirect_url)): ?>
                        <div class="alert alert-success py-3 text-center">
                            <p class="mb-2 fw-semibold">OTP sent to your email!</p>
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <span class="small text-muted ms-1">Redirecting...</span>
                        </div>
                        <script>
                            setTimeout(function() { window.location.href = "<?= $redirect_url; ?>"; }, 1000);
                        </script>
                    <?php else: ?>
                        <form action="login.php" method="POST" class="text-start">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small mb-1">Email Address</label>
                                <input type="text" class="form-control" name="email_or_id" required placeholder="Enter your Email" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-green w-100 mt-2">Send OTP</button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">Don't have an account? </span>
                            <a href="register.php" class="fw-bold text-decoration-none" style="color: #217835;">Register here</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
