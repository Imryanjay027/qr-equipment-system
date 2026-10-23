<?php
session_start();

// Gagamitin natin ang localhost sakaling nasa iisang container o iisang network na sila, kasama ang IP fallback
$hosts = ['localhost', '127.0.0.1', '136.158.33.74', '172.17.0.1'];
$user = 'root';
$pass = 'ryan072797';
$db   = 'qrcode';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$error_logs = [];

foreach ($hosts as $host) {
    try {
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        if (@$conn->real_connect($host, $user, $pass, $db, $port)) {
            $conn->set_charset("utf8mb4");
            break;
        }
    } catch (Exception $e) {
        $error_logs[$host] = $e->getMessage();
        continue;
    }
}

if (!$conn || $conn->connect_errno) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-radius: 5px; font-family: Arial;'>";
    echo "<h3>Diagnosis ng Koneksyon sa Database:</h3>";
    echo "<p>Nabigo ang lahat ng pagtatangkang kumonekta sa MariaDB.</p>";
    echo "<ul>";
    foreach($error_logs as $h => $err) {
        echo "<li><strong>Host ($h):</strong> $err</li>";
    }
    echo "</ul>";
    echo "<p><b>Solusyon sa Coolify:</b> Pumunta sa iyong MariaDB service sa Coolify dashboard, pumunta sa <b>Ports</b>, at i-expose ang port 3306 (halimbawa: i-map ang <code>3306:3306</code>) para maabot ito ng ibang aplikasyon.</p>";
    echo "</div>";
    exit;
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
