<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    die("Error: db_connection.php file not found.");
}

// Awtomatikong siguraduhing nandiyan ang table para walang tablespace error
$conn->query("CREATE TABLE IF NOT EXISTS app_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

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
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['login_email'] = $email;

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
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .login-card h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: 700;
        }
        .subtitle {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #1b5e20;
            outline: none;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #1b5e20;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-submit:hover {
            background-color: #2e7d32;
        }
        .error-msg {
            color: #c0392b;
            margin-bottom: 20px;
            font-size: 13px;
            background: #fadbd8;
            padding: 10px;
            border-radius: 6px;
            border-left: 4px solid #c0392b;
            text-align: left;
        }
        .footer-text {
            margin-top: 25px;
            font-size: 13px;
            color: #7f8c8d;
        }
        .footer-text a {
            color: #1b5e20;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Kung may logo ka, pwede mong i-uncomment ang sunod na linya at ilagay ang path ng logo -->
        <!-- <div class="logo-container"><img src="path_to_logo.png" alt="Logo"></div> -->

        <h3>QR-BASED EQUIPMENT MONITORING SYSTEM</h3>
        <div class="subtitle">Account Login (OTP Authentication)</div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required>
            </div>
            <button type="submit" class="btn-submit">Send OTP</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

</body>
</html>
