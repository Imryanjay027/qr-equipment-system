<?php
session_start();
require_once 'db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin' || $user['role'] === 'custodian') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: borrower.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - QR-Based Equipment Monitoring System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7f6; display: flex; height: 100vh; overflow: hidden; }
        
        .split-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* Left Panel na may School Background Image at Gradient Overlay */
        .brand-panel {
            flex: 1.1;
            background: linear-gradient(rgba(0, 40, 20, 0.85), rgba(0, 30, 60, 0.9)), url('school_bg.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 60px;
            position: relative;
        }

        .brand-top {
            text-align: center;
        }

        .brand-top img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.9);
            margin-bottom: 15px;
            object-fit: cover;
            background: white;
        }

        .brand-top h3 {
            font-size: 13px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #e0e0e0;
        }

        .brand-top h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .brand-top p {
            font-size: 12px;
            color: #d0d0d0;
            font-style: italic;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            background: rgba(0, 128, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 18px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }

        .feature-item .icon {
            background: #008000;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }

        /* Right Form Panel */
        .form-panel {
            flex: 1;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-container h2 {
            font-size: 24px;
            color: #111;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-container .subtitle {
            font-size: 13px;
            color: #666;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #333;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #008000;
            box-shadow: 0 0 0 3px rgba(0, 128, 0, 0.1);
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .login-btn {
            width: 100%;
            background-color: #008000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background-color: #006600;
        }

        .register-footer {
            text-align: center;
            font-size: 13px;
            color: #555;
            margin-top: 25px;
        }

        .register-footer a {
            color: #008000;
            text-decoration: none;
            font-weight: bold;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .split-container { flex-direction: column; overflow: auto; }
            body { overflow: auto; }
            .brand-panel { display: none; }
        }
    </style>
</head>
<body>

    <div class="split-container">
        <!-- Left Brand Panel with School Background -->
        <div class="brand-panel">
            <div class="brand-top">
                <img src="logo.jpg" alt="School Logo" onerror="this.src='https://via.placeholder.com/90'">
                <h3>MARY THE QUEEN COLLEGE OF QUEZON CITY</h3>
                <h1>QR-BASED EQUIPMENT MONITORING SYSTEM</h1>
                <p>Together for a Smarter, Safer and More Efficient Campus</p>
            </div>

            <div class="features-list">
                <div class="feature-item">
                    <div class="icon">📦</div>
                    <div><strong>Track Equipment</strong><br><span style="font-size:11px; color:#d0d0d0;">Seamless borrowing & return logs</span></div>
                </div>
                <div class="feature-item">
                    <div class="icon">🛡️</div>
                    <div><strong>Improve Accountability</strong><br><span style="font-size:11px; color:#d0d0d0;">Real-time condition monitoring</span></div>
                </div>
                <div class="feature-item">
                    <div class="icon">⚡</div>
                    <div><strong>Build a Smarter Campus</strong><br><span style="font-size:11px; color:#d0d0d0;">Automated digital verification</span></div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="form-panel">
            <div class="login-container">
                <h2>Welcome Back!</h2>
                <p class="subtitle">Login to your account to continue</p>

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <div class="register-footer">
                    Don't have an account yet? <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
