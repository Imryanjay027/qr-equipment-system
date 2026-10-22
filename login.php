<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$error = '';

// Proseso kapag pinindot ang Login button
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($login_input) && !empty($password)) {
        // Hanapin ang user gamit ang email, username, o student_id
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ? OR student_id = ? LIMIT 1");
        $stmt->bind_param("sss", $login_input, $login_input, $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // I-verify ang password (sinusuportahan ang hashed password o plain text)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                
                // I-save ang session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role']; // Halimbawa: 'custodian' o 'borrower'
                $_SESSION['full_name'] = $user['full_name'] ?? ($user['name'] ?? 'User');

                // TAMANG PAG-REDIRECT BATAY SA ROLE:
                if ($user['role'] === 'custodian') {
                    header("Location: custodian_dashboard.php"); // Direktang papunta sa Custodian Dashboard
                    exit();
                } else {
                    header("Location: borrower.php?uid=" . $user['id']); // Dashboard para sa Borrower
                    exit();
                }

            } else {
                $error = "Mali ang iyong password.";
            }
        } else {
            $error = "Walang nakitang account sa pangalan o email na ito.";
        }
        $stmt->close();
    } else {
        $error = "Mangyaring punan ang lahat ng patlang.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Equipment Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { max-width: 420px; width: 100%; margin: auto; background: white; border-radius: 12px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); padding: 30px; }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success"><i class="fas fa-qrcode me-2"></i>Login Portal</h3>
            <p class="text-muted small">Equipment Monitoring & Borrowing System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="login_input" class="form-label small fw-bold">Email, Username, o Student ID</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control" id="login_input" name="login_input" placeholder="Ilagay ang iyong credential" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold py-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Mag-log in
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
