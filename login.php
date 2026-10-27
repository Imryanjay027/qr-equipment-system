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

                if ($user['role'] === 'admin') {
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
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 480px;
            text-align: center;
        }

        .login-container img {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .login-container h1 {
            font-size: 16px;
            color: #008000;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .login-container .subtitle {
            font-size: 13px;
            color: #555;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #007bff;
        }

        .login-btn {
            width: 100%;
            background-color: #0066ff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background-color: #0056b3;
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

        .register-footer {
            font-size: 13px;
            color: #555;
            margin-top: 25px;
        }

        .register-footer a {
            color: #0066ff;
            text-decoration: none;
            font-weight: bold;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Nakaturo na sa logo.jpg -->
        <img src="logo.jpg" alt="School Logo" onerror="this.src='https://via.placeholder.com/75'">
        
        <h1>QR-BASED EQUIPMENT MONITORING SYSTEM</h1>
        <p class="subtitle">Enter your email and password to login</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="example@gmail.com">
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

</body>
</html>
