<?php
session_start();
$db_file = '/tmp/qrcode_database.sqlite';
$error = '';
$email = $_SESSION['login_email'] ?? '';

if (empty($email)) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
        $entered_otp = trim($_POST['otp']);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['otp'] === $entered_otp) {
            $_SESSION['authenticated'] = true;
            $_SESSION['role'] = $user['role'] ?? 'borrower';
            $_SESSION['email'] = $email;
            
            // Conditional redirection batay sa nakatalagang role sa database
            if (trim($user['role']) === 'custodian') {
                header("Location: custodian_dashboard.php");
            } else {
                header("Location: borrower.php");
            }
            exit();
        } else {
            $error = "Invalid OTP code. Please try again.";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - QR Equipment System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 90px; height: 90px; object-fit: contain; margin-bottom: 5px; border-radius: 50%; }
        .system-title { color: #28a745; font-size: 15px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
        .login-container h2 { margin-bottom: 5px; font-size: 20px; color: #333; }
        p { color: #666; font-size: 13px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; text-align: center; font-size: 20px; letter-spacing: 3px; }
        .btn-login { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #0056b3; }
        .error { color: red; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.jpg" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="system-title">QR-BASED EQUIPMENT MONITORING SYSTEM</div>
        
        <h2>Enter Verification Code</h2>
        <p>We've sent a 6-digit OTP to your Gmail inbox: <strong><?php echo htmlspecialchars($email); ?></strong></p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group">
                <label for="otp">6-Digit OTP</label>
                <input type="text" id="otp" name="otp" maxlength="6" placeholder="------" required autofocus>
            </div>
            <button type="submit" class="btn-login">Verify & Login</button>
        </form>
    </div>
</body>
</html>
