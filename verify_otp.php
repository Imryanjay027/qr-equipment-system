<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// DATABASE CONNECTION (COOLIFY ENVIRONMENT)
// ==========================================
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

// Kunin ang User ID mula sa URL, Cookie, o Session
$user_id = $_GET['uid'] ?? ($_POST['uid'] ?? ($_COOKIE['temp_user_id'] ?? ($_SESSION['temp_user_id'] ?? '')));

if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

$error = "";
$redirect_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $current_time = date('Y-m-d H:i:s');

        if (empty($user['otp_code']) || $user['otp_code'] !== $user_otp) {
            $error = "Mali ang OTP code na inilagay mo.";
        } else if ($current_time > $user['otp_expiry']) {
            $error = "Expired na ang OTP. Mangyaring mag-login ulit.";
        } else {
            // SUCCESSFUL OTP VERIFICATION!
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user']      = $user['id'];
            $_SESSION['role']      = strtolower(trim($user['role']));
            $_SESSION['full_name'] = $user['full_name'];

            // Linisin ang OTP sa Database
            $clear = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $clear->bind_param("i", $user['id']);
            $clear->execute();

            setcookie("temp_user_id", "", time() - 3600, "/");

            // TUKUYIN KUNG SAANG DASHBOARD PIPUNTA (CUSTODIAN VS BORROWER)
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
    <title>Verify OTP - MQCQC Equipment Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .otp-card { background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; margin-top: 50px; }
        .btn-custom { background-color: #198754; color: white; }
        .btn-custom:hover { background-color: #157347; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="otp-card">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-success">OTP Verification</h4>
                        <p class="text-muted small">Inilagay namin ang 6-digit code sa iyong email. Pakilagay sa ibaba:</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($redirect_url)): ?>
                        <div class="alert alert-success py-3 text-center">
                            <p class="mb-2 fw-semibold">OTP Verified Successfully!</p>
                            <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                            <span class="small text-muted ms-1">Redirecting to your dashboard...</span>
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = "<?= $redirect_url; ?>";
                            }, 1000);
                        </script>
                    <?php else: ?>
                        <form action="verify_otp.php" method="POST">
                            <input type="hidden" name="uid" value="<?= htmlspecialchars($user_id); ?>">
                            <div class="mb-3">
                                <label for="otp" class="form-label fw-semibold">Enter 6-Digit OTP</label>
                                <input type="text" class="form-control text-center fs-4 fw-bold" id="otp" name="otp" maxlength="6" required placeholder="000000">
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-custom fw-bold py-2">Verify & Login</button>
                            </div>
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
