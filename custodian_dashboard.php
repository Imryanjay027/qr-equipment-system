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

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Subukang kunin ang User ID sa Session O sa URL Parameter (?uid=)
$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');

if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

// Kunin ang mga tunay na columns sa tbl_users para iwas error sa columns
$table_columns = [];
$res = $conn->query("SHOW COLUMNS FROM tbl_users");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $table_columns[] = $row['Field'];
    }
}

$name_col = in_array('full_name', $table_columns) ? 'full_name' : (in_array('fullname', $table_columns) ? 'fullname' : 'full_name');
$role_col = in_array('role', $table_columns) ? 'role' : (in_array('user_type', $table_columns) ? 'user_type' : 'role');

// Kunin ang mga detalye ng user mula sa tbl_users
$stmt = $conn->prepare("SELECT *, {$name_col} as full_name, {$role_col} as role FROM tbl_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

// PINAIGTING NA PAG-CHECK NG ROLE
$role_lower = strtolower(trim($current_user['role'] ?? ''));
if (!$current_user || (strpos($role_lower, 'custodian') === false && strpos($role_lower, 'admin') === false)) {
    header("Location: login.php");
    exit();
}

// I-refresh ang session para secure
$_SESSION['user_id'] = $current_user['id'];
$_SESSION['role']    = $current_user['role'];
$_SESSION['full_name'] = $current_user['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard - MQCQC Equipment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-tools me-2 text-warning"></i>MQCQC Custodian Panel
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small">
                    <i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($current_user['full_name']); ?> 
                    <span class="badge bg-warning text-dark ms-1 text-uppercase"><?= htmlspecialchars($current_user['role']); ?></span>
                </span>
                <a href="login.php" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm p-4 bg-white rounded-4 border-start border-warning border-4">
                    <h3 class="fw-bold text-dark mb-1">Custodian Dashboard, <?= htmlspecialchars($current_user['full_name']); ?>!</h3>
                    <p class="text-muted mb-0">Pamahalaan ang mga kagamitan, aprubahan ang mga hiram, at i-monitor ang kondisyon ng equipment.</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Action Cards -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-warning fs-1">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h5 class="fw-bold">Manage Equipment</h5>
                        <p class="text-muted small">Magdagdag, mag-update, o mag-monitor ng mga school equipment.</p>
                        <a href="#" class="btn btn-warning btn-sm w-100 fw-semibold text-dark">Pamahalaan ang Items</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-success fs-1">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h5 class="fw-bold">Borrow & Return Requests</h5>
                        <p class="text-muted small">Aprubahan o i-record ang pagbabalik ng mga kagamitan.</p>
                        <a href="#" class="btn btn-success btn-sm w-100 fw-semibold">Tignan ang Requests</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-primary fs-1">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h5 class="fw-bold">QR Scanner</h5>
                        <p class="text-muted small">I-scan ang QR code ng mga borrower para sa mabilis na transaksiyon.</p>
                        <a href="index.php?uid=<?= $current_user['id']; ?>" class="btn btn-primary btn-sm w-100 fw-semibold">Buksan ang Scanner</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
