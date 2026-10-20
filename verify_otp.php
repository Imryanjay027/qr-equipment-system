<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// SESSION & URL ID SECURITY CHECK
// ==========================================m
$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');

if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

$_SESSION['user_id'] = $user_id;

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

// Kunin ang impormasyon ng naka-log in na borrower
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

if (!$current_user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - MQCQC Equipment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .sidebar { background-color: #198754; min-height: 100vh; color: white; }
        .sidebar a { color: rgba(255,255,255,0.85); text-decoration: none; padding: 10px 15px; display: block; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #157347; color: white; }
        .card-custom { border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
                <h4 class="text-white fw-bold text-center py-3 mb-4 border-bottom">MQCQC Borrower</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="borrower.php?uid=<?= $current_user['id']; ?>" class="nav-link active"><i class="fas fa-home me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-warning"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 text-success fw-bold">Welcome, <?= htmlspecialchars($current_user['full_name']); ?>!</h1>
                    <span class="badge bg-success fs-6 px-3 py-2 text-uppercase"><?= htmlspecialchars($current_user['role']); ?></span>
                </div>

                <!-- User Details Card -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card card-custom p-4 bg-white">
                            <h5 class="fw-bold text-secondary mb-3">Account Information</h5>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($current_user['email']); ?></p>
                            <p class="mb-1"><strong>Student ID:</strong> <?= htmlspecialchars($current_user['student_id'] ?? 'N/A'); ?></p>
                            <p class="mb-0"><strong>Course / Year:</strong> <?= htmlspecialchars($current_user['course_year'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Equipment Borrowing Section Placeholder -->
                <div class="card card-custom p-4 bg-white">
                    <h4 class="fw-bold text-success mb-3">Equipment Borrowing & Monitoring</h4>
                    <p class="text-muted">Maaari mo nang gamitin ang system para sa paghiram at pag-monitor ng mga kagamitan sa paaralan.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
