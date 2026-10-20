<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

$user_id = $_GET['uid'] ?? ($_POST['uid'] ?? ($_COOKIE['temp_user_id'] ?? ($_SESSION['temp_user_id'] ?? '')));

if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

$error = "";
$redirect_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Malinis na pag-extract ng input
    $user_otp = trim((string)($_POST['otp'] ?? ''));

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $db_otp = trim((string)$user['otp_code']);
        $current_time = date('Y-m-d H:i:s');

        // Loose at Strict Type Verification Logic
        if (empty($db_otp) || $db_otp !== $user_otp) {
            $error = "Mali ang OTP code na inilagay mo. (Sinubukan: $user_otp)";
        } else if ($current_time > $user['otp_expiry']) {
            $error = "Expired na ang OTP. Mangyaring mag-login ulit.";
        } else {
            // MATCH SUCCESSFUL!
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user']      = $user['id'];
            $_SESSION['role']      = strtolower(trim($user['role']));
            $_SESSION['full_name'] = $user['full_name'];

            // Clear OTP
            $clear = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $clear->bind_param("i", $user['id']);
            $clear->execute();

            setcookie("temp_user_id", "", time() - 3600, "/");

            $user_role = strtolower(trim($user['role']));
            if ($user_role === 'custodian') {
                $redirect_url = "custodian_dashboard.php?uid=" . $user['id'];
            } else {
                $redirect_url = "borrower.php?uid=" . $user['id'];
            }
        }
    } else {
        $error = "User not found in database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MQCQC Equipment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h4 class="fw-bold text-success text-center mb-3">OTP Verification</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($redirect_url)): ?>
                        <div class="alert alert-success py-3 text-center">
                            <p class="mb-2 fw-semibold">OTP Verified Successfully!</p>
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <span class="small text-muted ms-1">Redirecting to dashboard...</span>
                        </div>
                        <script>
                            setTimeout(function() { window.location.href = "<?= $redirect_url; ?>"; }, 1000);
                        </script>
                    <?php else: ?>
                        <form action="verify_otp.php" method="POST">
                            <input type="hidden" name="uid" value="<?= htmlspecialchars($user_id); ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Enter 6-Digit OTP</label>
                                <input type="text" class="form-control text-center fs-4 fw-bold" name="otp" maxlength="6" required placeholder="000000" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold">Verify & Login</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-secondary small text-decoration-none">← Bumalik sa Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
