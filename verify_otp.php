<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$error = "";
$success_msg = "";
$redirect_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim((string)($_POST['otp'] ?? ''));

    if (empty($user_otp)) {
        $error = "Mangyaring ilagay ang 6-digit OTP code.";
    } else {
        $table_columns = [];
        $res = $conn->query("SHOW COLUMNS FROM tbl_users");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $table_columns[] = $row['Field'];
            }
        }

        $name_col = in_array('full_name', $table_columns) ? 'full_name' : (in_array('fullname', $table_columns) ? 'fullname' : '');
        $role_col = in_array('role', $table_columns) ? 'role' : (in_array('user_type', $table_columns) ? 'user_type' : '');

        if (!empty($name_col) && !empty($role_col)) {
            $sql = "SELECT id, {$role_col} as role, {$name_col} as full_name FROM tbl_users WHERE TRIM(otp_code) = TRIM(?) AND otp_expiry >= NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_otp);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // I-save nang maayos sa session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['role']      = strtolower(trim($user['role']));
                $_SESSION['full_name'] = $user['full_name'];

                $clear = $conn->prepare("UPDATE tbl_users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?");
                $clear->bind_param("i", $user['id']);
                $clear->execute();

                $user_role = strtolower(trim($user['role']));
                
                // Ituro nang tuwiran sa custodian_dashboard.php kung ito ay custodian o admin
                if (strpos($user_role, 'custodian') !== false || strpos($user_role, 'admin') !== false) {
                    $redirect_url = "custodian_dashboard.php?uid=" . $user['id'];
                } else {
                    $redirect_url = "borrower.php?uid=" . $user['id'];
                }

                $success_msg = "OTP Verified Successfully!";
            } else {
                $error = "Mali o expired na ang OTP code na inilagay mo.";
            }
        } else {
            $error = "Database configuration error: Required columns not found.";
        }
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

                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success py-3 text-center">
                            <p class="mb-2 fw-semibold"><?= htmlspecialchars($success_msg); ?></p>
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <span class="small text-muted ms-1">Redirecting to dashboard...</span>
                        </div>
                        <script>
                            setTimeout(function() { 
                                window.location.href = "<?= $redirect_url; ?>"; 
                            }, 800);
                        </script>
                    <?php else: ?>
                        <p class="text-muted text-center small">Ilagay ang 6-digit code na ipinadala sa iyong email:</p>
                        <form action="verify_otp.php" method="POST">
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
