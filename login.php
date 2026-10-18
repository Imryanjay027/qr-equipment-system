<?php
// login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Coolify Database Connection
$host = "wfbyfhr4404k2y9u2spiv101";
$username = "root";
$password = "cW86JONRjM797hW23852myqva2R4yZKKCcJ9o41Hd8ViLXYCAy2MQchbytcIY0ke";
$database = "qrcode";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_val = trim($_POST['login_input'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!empty($input_val) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT id, fullname, password, username, role FROM users WHERE id = ? OR username = ? OR username LIKE ?");
        $like_val = "%" . $input_val . "%";
        $stmt->bind_param("sss", $input_val, $input_val, $like_val);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if ($pass === $row['password'] || password_verify($pass, $row['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['role'] = $row['role'] ?? 'custodian';
                
                $role_lower = strtolower(trim($_SESSION['role']));
                if ($role_lower === 'custodian' || $role_lower === 'admin') {
                    header("Location: index.php");
                } else {
                    header("Location: borrower_dashboard.php");
                }
                exit();
            } else {
                $error_msg = "Maling password.";
            }
        } else {
            $error_msg = "Walang nahanap na record para sa: " . htmlspecialchars($input_val);
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
    <title>Borrower Login - Mary The Queen College</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; color: #0056b3; margin-bottom: 25px; font-size: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .btn-login { background-color: #0056b3; color: white; padding: 12px; border: none; border-radius: 4px; width: 100%; font-size: 16px; cursor: pointer; font-weight: bold; }
        .btn-login:hover { background-color: #004085; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-size: 13px; border: 1px solid #f5c6cb; word-break: break-all; }
        .back-home { text-align: center; margin-top: 20px; }
        .back-home a { color: #007bff; text-decoration: none; font-size: 13px; }
        .back-home a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>System Portal Login</h2>
        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="login_input">Student ID / Username o Email:</label>
                <input type="text" id="login_input" name="login_input" required placeholder="Ilagay ang ID, Username o Email">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="I-type ang password">
            </div>
            <button type="submit" class="btn-login">Mag-log In</button>
        </form>
    </div>
</body>
</html>
