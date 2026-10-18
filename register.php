<?php
session_start();

// Database connection parameters
$host = 'mariadb-database-wfbyfhr4404k2y9u2spiv101'; // O kung ano ang host variable sa inyong environment
$db   = 'qrcode';
$user = 'root';
$pass = 'wfbyfhr4404k2y9u2spiv101'; // Siguraduhing tugma sa inyong database password
$charset = 'utf8mb4';

// Isama ang db connection file kung mayroon ka, o gamitin ang PDO instance sa ibaba
if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} elseif (file_exists('config.php')) {
    require_once 'config.php';
}

// Fallback: Pagbuo ng $pdo connection kung wala pa
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
        // Subukan gamitin ang env variables kung available
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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // PANSAMANTALANG LIMIT CHECK (Binago sa 5 muna para sa client test)
    if ($role === 'Custodian') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_users WHERE role = 'Custodian'");
        $stmt->execute();
        $custodianCount = $stmt->fetchColumn();

        if ($custodianCount >= 5) { // Ibalik sa 2 pagkatapos ng testing
            $error = "Maximum limit of 5 Custodian accounts reached.";
        }
    }

    if (empty($error)) {
        // I-check kung may umiiral nang email o username
        $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $error = "Email or Username already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("INSERT INTO tbl_users (username, fullname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($insert->execute([$username, $fullname, $email, $hashedPassword, $role])) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration - QR-Based Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo { max-width: 80px; margin-bottom: 10px; }
        h3 { margin-bottom: 5px; color: #1b4332; font-size: 1.1rem; }
        p.sub { font-size: 0.9rem; color: #666; margin-bottom: 20px; }
        .alert { padding: 10px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 15px; text-align: left; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-group { text-align: left; margin-bottom: 15px; }
        label { font-size: 0.85rem; font-weight: bold; color: #333; display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 0.9rem; }
        button { width: 100%; padding: 11px; background-color: #2d6a4f; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.95rem; }
        button:hover { background-color: #1b4332; }
        .login-link { font-size: 0.85rem; margin-top: 15px; display: block; color: #333; text-decoration: none; }
        .login-link a { color: #2d6a4f; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
        <p class="sub">Account Registration</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Account Role</label>
                <select name="role" required>
                    <option value="Student">Student</option>
                    <option value="Custodian">Custodian</option>
                </select>
            </div>

            <div class="form-group">
                <label>ID Number / Username</label>
                <input type="text" name="username" placeholder="Enter ID Number" required>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter your Full name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
