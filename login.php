<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment Monitoring System</title>
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
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .login-container img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            object-fit: contain;
        }
        .login-container h2 {
            font-size: 18px;
            color: #008000;
            margin-bottom: 25px;
            font-weight: bold;
        }
        .login-container p {
            color: #555;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            background-color: #f8fafc;
        }
        .input-group input:focus {
            border-color: #0066cc;
            outline: none;
            background-color: #fff;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #0066ff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #0052cc;
        }
        .register-link {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }
        .register-link a {
            color: #0066ff;
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
        <!-- Nakaturo sa logo.jpg -->
        <img src="logo.jpg" alt="School Logo">
        
        <h2>QR-BASED EQUIPMENT MONITORING SYSTEM</h2>
        <p>Enter your email to receive an OTP</p>
        
        <!-- Naka-turo na diretso sa send_otp.php -->
        <form action="send_otp.php" method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="example@gmail.com">
            </div>
            <button type="submit" name="send_otp">Send OTP</button>
        </form>

        <div class="register-link">
            Don't have an account yet? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
