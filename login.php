<?php
session_start();
$db_file = '/tmp/qrcode_database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE,
        otp TEXT
    )");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 90px; height: 90px; object-fit: contain; margin-bottom: 5px; border-radius: 50%; }
        .system-title { color: #28a745; font-size: 15px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.jpg" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="system-title">QR-BASED EQUIPMENT MONITORING SYSTEM</div>
        
        <form action="send_otp.php" method="POST">
            <div class="form-group">
                <label for="email">Enter your email</label>
                <input type="email" id="email" name="email" placeholder="example@email.com" required>
            </div>
            <button type="submit" class="btn-login">Send OTP</button>
        </form>
    </div>
</body>
</html>
