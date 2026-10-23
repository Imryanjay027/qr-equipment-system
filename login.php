<?php
session_start();

// Kunin ang host mula sa Coolify environment variables o gamitin ang fallback
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: '172.17.0.1';
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: 'ryan072797';
$db   = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$connected = false;

// Subukan ang iba't ibang posibleng koneksyon kabilang ang localhost at gateway
$hosts_to_try = [$host, '172.17.0.1', 'localhost', '127.0.0.1'];

foreach ($hosts_to_try as $h) {
    try {
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        if (@$conn->real_connect($h, $user, $pass, $db, $port)) {
            $conn->set_charset("utf8mb4");
            $connected = true;
            break;
        }
    } catch (Exception $e) {
        continue;
    }
}

if (!$connected) {
    die("Database connection failed: Hindi makakonekta sa database. Siguraduhing naka-link ang App at Database sa Coolify.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Equipment System - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login - QR Equipment System</h2>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username / ID</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>
