<?php
// login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Coolify Database Connection
$host = "wfbyfhr4404k2y9u2spiv101";
$username = "root";
$password = "ryan072797";
$database = "qrcode";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (!empty($username_input) && !empty($password_input)) {
        $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username_input, $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Suriin ang password (sumusuporta sa plain text o password_hash)
            if ($password_input === $user['password'] || password_verify($password_input, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                $role_lower = strtolower(trim($user['role']));
                if ($role_lower === 'custodian' || $role_lower === 'admin') {
                    header("Location: index.php");
                    exit();
                } else {
                    header("Location: borrower_dashboard.php");
                    exit();
                }
            } else {
                $error_msg = "Maling password. Mangyaring ulitin.";
            }
        } else {
            $error_msg = "Walang nahanap na account para sa username na iyon.";
        }
        $stmt->close();
    } else {
        $error_msg = "Mangyaring punan ang lahat ng field.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mary The Queen College of Quezon City</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; color: #1a365d; margin-bottom: 25px; font-size: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 13px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-login { background-color: #1a365d; color: white; padding: 12px; border: none; border-radius: 4px; width: 100%; font-size: 15px; cursor: pointer; font-weight: bold; }
        .btn-login:hover { background-color: #2b4c7e; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-size: 13px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>System Login</h2>
        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username o Email:</label>
                <input type="text" id="username" name="username" required placeholder="Ilagay ang username">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Ilagay ang password">
            </div>
            <button type="submit" class="btn-login">Mag-log In</button>
        </form>
    </div>
</body>
</html>
