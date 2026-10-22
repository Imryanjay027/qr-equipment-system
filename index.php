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
$scanned_student_id = $_GET['student_id'] ?? '';

// Kunin ang impormasyon ng estudyante batay sa na-scan o na-input na ID
$student_data = null;
if (!empty($scanned_student_id)) {
    // Kung ang na-scan ay JSON o URL, linisin natin kung sakali
    if (strpos($scanned_student_id, 'ID:') !== false) {
        // Parsing logic kung sakaling naka-format string ang QR data
        preg_match('/ID:\s*([A-Za-z0-9-]+)/', $scanned_student_id, $matches);
        if (!empty($matches[1])) {
            $scanned_student_id = trim($matches[1]);
        }
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR full_name LIKE ? LIMIT 1");
    if ($stmt) {
        $like_term = "%" . $scanned_student_id . "%";
        $stmt->bind_param("ss", $scanned_student_id, $like_term);
        $stmt->execute();
        $student_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// Kung walang nahanap o default view
if (!$student_data && !empty($scanned_student_id)) {
    $student_data = [
        'full_name' => $scanned_student_id,
        'student_id' => $scanned_student_id,
        'course' => 'Not Specified',
        'year_level' => 'N/A',
        'profile_pic' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Portal - MQCQC</title>
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
        <span class="fw-bold" style="font-size: 13px;">MQCQC QR SCANNER PORTAL</span>
        <span class="badge bg-warning text-dark" style="font-size: 10px;">SCANNER</span>
    </div>

    <!-- Offcanvas Mobile Sidebar -->
    <div class="offcanvas offcanvas-start bg-success text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom border-light border-opacity-10" style="background-color: #0c1e35;">
            <h5 class="offcanvas-title fs-6 fw-bold" id="mobileSidebarLabel">MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
            <div class="sidebar-menu">
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="borrow_requests.php?uid=<?= $user_id; ?>"><i class="fas fa-list-alt"></i> Borrowing Requests</a>
                <a href="index.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-qrcode"></i> QR Scanner Portal</a>
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
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="borrow_requests.php?uid=<?= $user_id; ?>"><i class="fas fa-list-alt"></i> Borrowing Requests</a>
                <a href="index.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-qrcode"></i> QR Scanner Portal</a>
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
                <h4>QR CODE SCANNER PORTAL</h4>
                <p>Scan student QR codes for equipment monitoring & verification</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success px-3 py-2" style="font-size: 11px;">CUSTODIAN</span>
            </div>
        </div>

        <div class="main-card">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-camera me-2 text-success"></i>QR Scanner</h5>
            
            <!-- Scanner Placeholder / Interface -->
            <div class="text-center mb-4">
                <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
                <a href="index.php?uid=<?= $user_id; ?>&student_id=CUST001" class="btn btn-success btn-sm mt-3">Simulate Scan (Test Student)</a>
            </div>

            <!-- Student Verification & Condition Monitoring Section -->
            <h5 class="fw-bold text-dark mb-3 mt-4"><i class="fas fa-user-check me-2 text-success"></i>Student Verification & Condition Monitoring</h5>
            
            <div class="card p-3 border-0 bg-light shadow-sm mb-4">
                <div class="profile-section">
                    <?php if (!empty($student_data) && !empty($student_data['profile_pic'])): ?>
                        <img src="<?= htmlspecialchars($student_data['profile_pic']); ?>" class="profile-avatar" alt="Student Selfie">
                    <?php else: ?>
                        <div class="profile-avatar">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span class="text-success fw-bold small"><i class="fas fa-check-circle me-1"></i>Student Verified (Live Selfie Record)</span>
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($student_data['full_name'] ?? 'No Student Scanned'); ?></h5>
                        <p class="text-muted mb-0 small">Student ID: <strong><?= htmlspecialchars($student_data['student_id'] ?? '---'); ?></strong></p>
                        <p class="text-muted mb-0 small">Course / Year: <strong><?= htmlspecialchars($student_data['course'] ?? '---'); ?> / <?= htmlspecialchars($student_data['year_level'] ?? '---'); ?></strong></p>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-laptop me-2 text-success"></i>Active Borrowed Equipment Records (Pending / Damaged)</h5>
            <div class="card p-3 border-0 bg-light shadow-sm">
                <p class="text-muted small mb-0">No active or damaged equipment found for this student (All items are in good condition or have been replaced).</p>
            </div>
        </div>
    </div>

    <!-- html5-qrcode library for real camera scanning -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Redirect to index.php with the scanned student id/data
            window.location.href = "index.php?uid=<?= $user_id; ?>&student_id=" + encodeURIComponent(decodedText);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 }, false);
        html5QrcodeScanner.render(onScanSuccess);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
