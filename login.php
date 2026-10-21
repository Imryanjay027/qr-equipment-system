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
                // Diretsong pumunta sa verify page nang walang kailangang i-pasa na uid sa URL
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
    <title>Login - MQCQC Equipment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h4 class="fw-bold text-success text-center mb-3">System Login</h4>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($redirect_url)): ?>
                        <div class="alert alert-success py-3 text-center">
                            <p class="mb-2 fw-semibold">OTP sent to your email!</p>
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <span class="small text-muted ms-1">Redirecting to OTP verification...</span>
                        </div>
                        <script>
                            setTimeout(function() { window.location.href = "<?= $redirect_url; ?>"; }, 1000);
                        </script>
                    <?php else: ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email or Student ID</label>
                                <input type="text" class="form-control" name="email_or_id" required placeholder="Enter email or ID" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold">Send OTP / Login</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
