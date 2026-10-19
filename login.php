<?php
session_start();
include 'db_connection.php';

$error_msg = "";

if (isset($_POST['send_otp'])) {
    $identifier = trim($_POST['identifier']);

    if (!empty($identifier)) {
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $email = $user['email'];

                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['email'] = $email;

                // Subukang i-send ang email
                $to = $email;
                $subject = "Your Login OTP Code";
                $message = "Ang iyong OTP code para sa login ay: " . $otp;
                $headers = "From: noreply@equipment-system.local\r\n";

                // Kahit mag-fail o mag-success ang mail(), ituloy natin sa verify_otp para ma-test mo agad
                @mail($to, $subject, $message, $headers);
                
                header("Location: verify_otp.php");
                exit();
            } else {
                // ITO ANG DAHILAN KAYA NAGRERE-LOAD: Walang nakitang ganitong email sa tbl_users
                $error_msg = "Error: Ang Email na '$identifier' ay walang nahanap na record sa tbl_users!";
            }
            $stmt->close();
        } else {
            $error_msg = "Database query error: " . $conn->error;
        }
    } else {
        $error_msg = "Mangyaring ilagay ang iyong Email address.";
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .logo {
            width: 90px;
            height: auto;
            margin-bottom: 20px;
        }

        .system-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .system-subtitle {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 25px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 13.5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13.5px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #2d6a4f;
        }

        .btn-submit {
            width: 100%;
            background-color: #2d6a4f;
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #21513c;
        }

        .register-link {
            margin-top: 25px;
            font-size: 13.5px;
            color: #555555;
        }

        .register-link a {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Logo -->
    <img src="logo.jpg" alt="MQCQC Logo" class="logo" onerror="this.src='logo.png';">

    <h2 class="system-title">"QR-BASED EQUIPMENT MONITORING SYSTEM"</h2>
    <p class="system-subtitle">Account Login (OTP Authentication)</p>

    <!-- Error Message Alert Box - Dito makikita kung bakit nagre-reload -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert-danger">
            <?= htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="identifier">Email Address</label>
            <input type="email" id="identifier" name="identifier" class="form-control" placeholder="Enter your Email" required>
        </div>

        <button type="submit" name="send_otp" class="btn-submit">
            Send OTP
        </button>
    </form>

    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>
