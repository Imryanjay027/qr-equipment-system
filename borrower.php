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

// Kunin ang impormasyon ng naka-login na user mula sa database batay sa id o student_id
$user_data = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? OR student_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("is", $user_id, $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Kunin nang ligtas ang mga values mula sa database kasama ang iba't ibang posibleng pangalan ng column
$display_name = $user_data['full_name'] ?? ($user_data['name'] ?? 'System Custodian');
$display_sid = $user_data['student_id'] ?? ($user_data['id_number'] ?? 'CUST001');
$display_course = $user_data['course'] ?? ($user_data['program'] ?? ($user_data['department'] ?? 'IT'));
$display_year = $user_data['year_level'] ?? ($user_data['year'] ?? ($user_data['year_lvl'] ?? '3rd Year'));
$display_pic = $user_data['profile_pic'] ?? ($user_data['selfie'] ?? ($user_data['avatar'] ?? ''));
$user_role = $user_data['role'] ?? ($user_data['user_type'] ?? 'STUDENT');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - MQCQC</title>
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
        
        .profile-section { display: flex; align-items: center; gap: 20px; }
        .profile-avatar { width: 70px; height: 70px; background-color: #6c757d; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; object-fit: cover; border: 2px solid #217835; }

        .mobile-header { display: none; background-color: var(--primary-color); color: white; padding: 12px 20px; align-items: center; justify-content: space-between; }
        
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 15px; }
            .mobile-header { display: flex; }
        }
    </style>
</head>
<body>

    <!-- Mobile Header with Hamburger Toggle -->
    <div class="mobile-header">
        <button class="btn text-white p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
            <i class="fas fa-bars fs-4"></i>
        </button>
        <span class="fw-bold" style="font-size: 13px;">MQCQC BORROWER PANEL</span>
        <span class="badge bg-warning text-dark" style="font-size: 10px;">STUDENT</span>
    </div>

    <!-- Offcanvas Mobile Sidebar -->
    <div class="offcanvas offcanvas-start bg-success text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom border-light border-opacity-10" style="background-color: #0c1e35;">
            <h5 class="offcanvas-title fs-6 fw-bold" id="mobileSidebarLabel">MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
            <div class="sidebar-menu">
                <a href="borrower.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="registration.php?uid=<?= $user_id; ?>"><i class="fas fa-plus-circle"></i> Borrow an Item</a>
                <a href="borrower_history.php?uid=<?= $user_id; ?>"><i class="fas fa-history"></i> Returned Items / History</a>
                <a href="login.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            <div class="text-center py-2 border-top border-light border-opacity-10" style="font-size: 11px; color: rgba(255,255,255,0.7);">
                <p class="mb-0">"Discipline today, excellence tomorrow."</p>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div class="sidebar d-none d-md-flex">
        <div>
            <div class="sidebar-brand">
                <img src="logo.jpg" alt="School Logo" onerror="this.src='uploads/default.png';">
                <h5>MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            </div>
            <div class="sidebar-menu">
                <a href="borrower.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="registration.php?uid=<?= $user_id; ?>"><i class="fas fa-plus-circle"></i> Borrow an Item</a>
                <a href="borrower_history.php?uid=<?= $user_id; ?>"><i class="fas fa-history"></i> Returned Items / History</a>
                <a href="login.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="sidebar-footer">
            <p class="mb-0">"Discipline today, excellence tomorrow."</p>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="top-navbar d-none d-md-flex">
            <div>
                <h4>BORROWER'S DASHBOARD</h4>
                <p>Equipment Borrowing, Return & Monitoring Portal</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success px-3 py-2" style="font-size: 11px;"><?= strtoupper(htmlspecialchars($user_role)); ?></span>
            </div>
        </div>

        <div class="main-card">
            <div class="profile-section mb-4">
                <?php if (!empty($display_pic)): ?>
                    <img src="<?= htmlspecialchars($display_pic); ?>" class="profile-avatar" alt="User Selfie">
                <?php else: ?>
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($display_name); ?></h4>
                    <p class="text-muted mb-0 small">Student ID: <strong><?= htmlspecialchars($display_sid); ?></strong></p>
                    <p class="text-muted mb-0 small">Course / Year: <strong><?= htmlspecialchars($display_course); ?> / <?= htmlspecialchars($display_year); ?></strong></p>
                    <span class="badge bg-secondary mt-1" style="font-size: 10px;">Registered Borrower</span>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-smile me-2 text-success"></i>Welcome to your Borrower Portal</h5>
            <div class="card p-3 border-0 bg-light shadow-sm">
                <p class="text-muted small mb-0">Use the sidebar menu or the hamburger menu above to navigate through the sections and borrow equipment.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
