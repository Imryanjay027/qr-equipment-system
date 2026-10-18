<?php
session_start();

// Database connection parameters
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$db   = 'qrcode';
$user = 'root';
$pass = 'wfbyfhr4404k2y9u2spiv101';
$charset = 'utf8mb4';

if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} elseif (file_exists('config.php')) {
    require_once 'config.php';
}

if (!isset($pdo) || $pdo === null) {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        $env_host = getenv('DB_HOST') ?: 'localhost';
        $env_db   = getenv('DB_NAME') ?: 'qrcode';
        $env_user = getenv('DB_USER') ?: 'root';
        $env_pass = getenv('DB_PASS') ?: '';
        
        try {
            $pdo = new PDO("mysql:host=$env_host;dbname=$env_db;charset=$charset", $env_user, $env_pass, $options);
        } catch (\PDOException $ex) {
            die("Database Connection Failed: " . $ex->getMessage());
        }
    }
}

$error = '';
$email = $_SESSION['pending_otp_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp_code'] ?? '');

    if (empty($user_otp)) {
        $error = "Please enter the 6-digit OTP code.";
    } else {
        $user = null;

        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }

        $session_otp = $_SESSION['otp_code'] ?? null;
        $db_otp = $user['otp_code'] ?? null;

        // I-verify kung tumutugma sa Database OTP o Session OTP
        if (($db_otp && $db_otp == $user_otp) || ($session_otp && $session_otp == $user_otp)) {
            // Success! Set persistent session flags
            $_SESSION['user_id'] = $user['id'] ?? $_SESSION['user_id'] ?? 1;
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['fullname'] = $user['fullname'] ?? '';
            $_SESSION['role'] = $user['role'] ?? 'custodian'; // Default role

            // Linisin ang OTP pagkatapos magamit
            unset($_SESSION['otp_code']);
            if ($user) {
                $pdo->prepare("UPDATE tbl_users SET otp_code = NULL WHERE id = ?")->execute([$user['id']]);
            }

            // Direct Redirect sa Custodian Dashboard (o nababagay na pahina)
            if (file_exists('custodian_dashboard.php')) {
                header("Location: custodian_dashboard.php");
            } elseif (file_exists('dashboard.php')) {
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid OTP code. Please check your email and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - QR Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 80px; height: auto; margin-bottom: 10px; }
        h3 { color: #0d6efd; margin: 5px 0 10px 0; font-size: 1.2rem; }
        p.sub { font-size: 0.85rem; color: #666; margin-bottom: 20px; }
        .alert { padding: 12px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 20px; text-align: left; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { font-size: 0.85rem; font-weight: bold; color: #333; display: block; margin-bottom: 6px; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #0d6efd; border-radius: 4px; box-sizing: border-box; font-size: 1.2rem; letter-spacing: 4px; text-align: center; outline: none; }
        button[type="submit"] { width: 100%; padding: 12px; background-color: #0d6efd; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.95rem; }
        button[type="submit"]:hover { background-color: #0b5ed7; }
        .back-link { font-size: 0.85rem; margin-top: 20px; display: block; color: #666; text-decoration: none; }
        .back-link a { color: #0d6efd; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <img src="logo.jpg" alt="School Logo" class="logo">
        <h3>Mary The Queen College of Quezon City</h3>
        <p class="sub">Enter the 6-digit OTP sent to your email</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group">
                <label>OTP Code</label>
                <input type="text" name="otp_code" placeholder="------" maxlength="6" required autofocus>
            </div>
            <button type="submit">Verify OTP</button>
        </form>

        <p class="back-link">Back to <a href="login.php">Login</a></p>
    </div>
</body>
</html>
