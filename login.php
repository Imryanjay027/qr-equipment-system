<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    die("Error: db_connection.php file not found.");
}

// 1. Tinitiyak na may app_users table at may 'role' column para sa custodian access
$conn->query("CREATE TABLE IF NOT EXISTS app_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(50) DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 2. Awtomatikong i-register ang email mo bilang Custodian kung wala pa sa database
$my_email = 'ryanjayleoligao64@gmail.com';
$check_user = $conn->prepare("SELECT id FROM app_users WHERE email = ?");
$check_user->bind_param("s", $my_email);
$check_user->execute();
$check_result = $check_user->get_result();

if ($check_result->num_rows === 0) {
    $insert_user = $conn->prepare("INSERT INTO app_users (email, role) VALUES (?, 'custodian')");
    $insert_user->bind_param("s", $my_email);
    $insert_user->execute();
    $insert_user->close();
}
$check_user->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT * FROM app_users WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['login_email'] = $email;
                $_SESSION['user_role'] = $user['role'];

                // Tagumpay! Mag-redirect sa verify_otp.php
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "This email address is not registered in the system.";
            }
            $stmt->close();
        } else {
            $error = "Database query error: " . $conn->error;
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
    <title>QR-Based Equipment Monitoring System - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 15px;
        }
        .logo-container img {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .login-card h3 {
            font-size: 16px;
            color: #212529;
            margin-bottom: 5px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #212529;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #2e7d32;
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #1b5e20;
        }
        .error-msg {
            color: #721c24;
            margin-bottom: 20px;
            font-size: 13px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .footer-text {
            margin-top: 25px;
            font-size: 13px;
            color: #6c757d;
        }
        .footer-text a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }
        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="logo.jpg" alt="School Logo">
        </div>

        <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
        <div class="subtitle">Account Login (OTP Authentication)</div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your Email" required>
            </div>
            <button type="submit" class="btn-submit">Send OTP</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

</body>
</html>
