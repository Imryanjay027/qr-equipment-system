<?php
// I-on ang error reporting para makita agad kung may problema
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Siguraduhing tama ang path ng db_connection.php mo
if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    die("Error: Hindi makita ang db_connection.php file.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // PALITAN ANG 'users' NG TANGGAP NA PANGALAN NG TABLE SA DATABASE MO (hal. accounts, tbl_users, etc.)
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['login_email'] = $email;

                // Mag-redirect papuntang verify_otp.php
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Ang email na ito ay hindi rehistrado.";
            }
            $stmt->close();
        } else {
            $error = "Database query error: " . $conn->error;
        }
    } else {
        $error = "Mangyaring ilagay ang iyong email address.";
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
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #1b5e20;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #2e7d32;
        }
        .error-msg {
            color: #d32f2f;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
        <p style="font-size: 14px; color: #666;">Account Login (OTP Authentication)</p>

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

        <p style="margin-top: 20px; font-size: 13px; color: #666;">
            Don't have an account? <a href="register.php" style="color: #1b5e20; text-decoration: none;">Register here</a>
        </p>
    </div>

</body>
</html>
