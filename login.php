<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// DATABASE CONNECTION (COOLIFY ENVIRONMENT)
// ==========================================
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

// Siguraduhing may users table at default test accounts
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    student_id VARCHAR(50) DEFAULT NULL,
    course_year VARCHAR(100) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'borrower',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$check_user = $conn->query("SELECT COUNT(*) as count FROM users");
if ($check_user) {
    $row = $check_user->fetch_assoc();
    if ($row['count'] == 0) {
        $conn->query("INSERT INTO users (full_name, email, student_id, course_year, role) VALUES 
        ('System Custodian', 'custodian@mqcqc.edu.ph', 'CUST001', 'Staff', 'custodian'),
        ('Test Borrower', 'ryanjayleoligao64@gmail.com', '2026-00001', 'BSIT 3rd Year', 'borrower')");
    }
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_id = trim($_POST['email_or_id'] ?? '');

    if (!empty($email_or_id)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        $stmt->bind_param("ss", $email_or_id, $email_or_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Gumawa ng 6-digit OTP
            $otp = rand(100000, 999999);
            
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes validity

            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_role'] = strtolower(trim($user['role']));
            $_SESSION['temp_full_name'] = $user['full_name'];
            $_SESSION['temp_student_id'] = $user['student_id'] ?? '';
            $_SESSION['temp_course_year'] = $user['course_year'] ?? '';
            
            $target_email = $user['email'];

            $brevo_api_key = "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"; 
            
            $email_data = [
                "sender" => [
                    "name" => "MQCQC Equipment System",
                    "email" => "ryanjayleoligao64@gmail.com"
                ],
                "to" => [
                    [
                        "email" => $target_email
                    ]
                ],
                "subject" => "Your Login OTP Code",
                "htmlContent" => "<p>Hello {$user['full_name']},</p><p>Your 6-digit OTP code for system login is: <strong>{$otp}</strong></p><p>This code will expire in 5 minutes.</p>"
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

            if ($http_code >= 200 && $http_code < 300) {
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Brevo Error (HTTP $http_code): " . ($curl_error ? $curl_error : $response);
            }
        } else {
            $error = "Email or Student ID not found in the system database.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
