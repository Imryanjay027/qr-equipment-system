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

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? 1);

// Kunin ang impormasyon ng custodian mula sa database
$custodian_data = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? OR role = 'Custodian' OR role = 'Staff' LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $custodian_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$custodian_data) {
    $custodian_data = [
        'full_name' => 'System Custodian',
        'student_id' => 'CUST001',
        'profile_pic' => ''
    ];
}

// Bilang ng active requests para sa dashboard overview
$total_active = 0;
$count_query = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE LOWER(status) NOT LIKE '%good%' AND LOWER(status) NOT LIKE '%replaced%' AND LOWER(status) NOT LIKE '%cleared%'");
if ($count_query) {
    $row_c = $count_query->fetch_assoc();
    $total_active = $row_c['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Panel - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-color: #217835; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); background-color: #0c1e35; }
        .sidebar-brand img { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #ffc107; }
        .sidebar-brand h5 { font-size: 12px; font-weight: 700; line-height: 1.4; margin: 0; }
        
        .sidebar-menu { padding: 15px 10px; flex-grow: 1; }
        .sidebar-menu a { display: flex; align-items: center; color: rgba(255,255,255,0.8); padding: 12px 15px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; margin-bottom: 5px; transition: all 0.2s; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(255,255,255,0.15); color: white; }
        .sidebar-menu a i { width: 25px; font-size: 15px; }

        .sidebar-footer { padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 11px; color: rgba(255,255,255,0.7); text-align: center; }

        .main-content { margin-left: var(--sidebar-width); padding: 25px; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .top-navbar h4 { font-size: 15px; font-weight: 700; color: #0c1e35; margin: 0; }
        .top-navbar p { font-size: 11px; color: #777; margin: 0; }

        .main-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); padding: 25px; margin-bottom: 25px; }
        
        .profile-section { display: flex; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; gap: 20px; }
        .profile-avatar { width: 70px; height: 70px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; object-fit: cover; border: 2px solid #217835; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="sidebar-brand">
                <img src="logo.jpg" alt="School Logo" onerror="this.src='uploads/default.png';">
                <h5>MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            </div>
            <div class="sidebar-menu">
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="borrowing_requests.php?uid=<?= $user_id; ?>"><i class="fas fa-list-alt"></i> Borrowing Requests</a>
                <a href="index.php?uid=<?= $user_id; ?>"><i class="fas fa-qrcode"></i> QR Scanner Portal</a>
                <a href="login.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="sidebar-footer">
            <p class="mb-0">"Discipline today, excellence tomorrow."</p>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4>PROPERTY CUSTODIAN PANEL</h4>
                <p>Equipment Borrowing, Return & Monitoring System</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success px-3 py-2" style="font-size: 11px;">CUSTODIAN</span>
            </div>
        </div>

        <div class="main-card">
            <!-- Custodian Profile Information Section -->
            <div class="profile-section">
                <?php if (!empty($custodian_data['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($custodian_data['profile_pic']); ?>" class="profile-avatar" alt="Custodian Picture">
                <?php else: ?>
                    <div class="profile-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($custodian_data['full_name']); ?></h5>
                    <p class="text-muted mb-0 small">Custodian ID / No: <strong><?= htmlspecialchars($custodian_data['student_id'] ?? 'CUST001'); ?></strong></p>
                    <span class="badge bg-secondary mt-1" style="font-size: 10px;">System Administrator / Property Custodian</span>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie me-2 text-success"></i>Dashboard Overview</h5>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-3 border-0 bg-light shadow-sm">
                        <h6 class="text-muted small fw-bold mb-1">Total Active Requests</h6>
                        <h3 class="fw-bold text-success mb-0"><?= $total_active; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
