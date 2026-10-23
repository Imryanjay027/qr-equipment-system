<?php
session_start();

// Zero-config SQLite database na tumutugma sa ginamit natin sa login.php
$db_file = '/tmp/qrcode_database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kunin ang email mula sa session
    $email = $_SESSION['login_email'] ?? '';
    
    // Kung pinindot ang verify button
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $entered_otp = trim($_POST['otp']);
        
        $stmt = $pdo->prepare("SELECT otp FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['otp'] === $entered_otp) {
            // Successful ang login
            $_SESSION['authenticated'] = true;
            header("Location: dashboard.php"); // Palitan kung saan man ang landing page pagka-login
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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
        .logo { width: 100px; height: 100px; object-fit: contain; margin-bottom: 15px; border-radius: 50%; }
        .login-container h2 { margin-bottom: 10px; font-size: 22px; color: #333; }
        p { color: #666; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; text-align: center; font-size: 18px; letter-spacing: 2px; }
        .btn-login { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #0056b3; }
        .error { color: red; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.jpg" alt="Logo" class="logo" onerror="this.style.display='none'">
        <h2>Enter Verification Code</h2>
        <p>We sent an OTP to <strong><?php echo htmlspecialchars($email); ?></strong></p>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group">
                <label for="otp">6-Digit OTP</label>
                <input type="text" id="otp" name="otp" maxlength="6" placeholder="123456" required>
            </div>
            <button type="submit" class="btn-login">Verify OTP</button>
        </form>
    </div>
</body>
</html>
