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

// Handle Actions (Clear, Damaged, Mark Replaced)
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $action = $_GET['action'];

    if ($action == 'clear') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Cleared' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'damaged') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Damage needs for replacement' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'mark_replaced') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Replaced' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: custodian_dashboard.php?uid=" . $user_id . "&tab=requests");
    exit();
}

// Kunin ang user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current_user) {
    $current_user = [
        'id' => 1,
        'full_name' => 'Property Custodian',
        'student_id' => 'CUST001',
        'course_year' => 'Staff'
    ];
}

// Kunin ang mga active requests
$requests_query = $conn->query("SELECT * FROM borrow_requests WHERE status != 'Cleared' AND status != 'Replaced' ORDER BY id DESC");
$active_requests = $requests_query ? $requests_query->fetch_all(MYSQLI_ASSOC) : [];

$active_tab = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-color: #217835; --navy-dark: #0c1e35; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); background-color: var(--navy-dark); }
        .sidebar-brand img { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #ffc107; }
        .sidebar-brand h5 { font-size: 12px; font-weight: 700; line-height: 1.4; margin: 0; }
        
        .sidebar-menu { padding: 15px 10px; flex-grow: 1; }
        .sidebar-menu a { display: flex; align-items: center; color: rgba(255,255,255,0.8); padding: 12px 15px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; margin-bottom: 5px; transition: all 0.2s; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(255,255,255,0.15); color: white; }
        .sidebar-menu a i { width: 25px; font-size: 15px; }

        .sidebar-footer { padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 11px; color: rgba(255,255,255,0.7); text-align: center; }

        .main-content { margin-left: var(--sidebar-width); padding: 25px; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .top-navbar h4 { font-size: 15px; font-weight: 700; color: var(--navy-dark); margin: 0; }
        .top-navbar p { font-size: 11px; color: #777; margin: 0; }

        .main-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); padding: 25px; margin-bottom: 25px; }
        
        .table th { background-color: var(--primary-color) !important; color: white !important; font-size: 12px; font-weight: 600; vertical-align: middle; border: none; }
        .table td { font-size: 12px; vertical-align: middle; }

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
                <img src="logo.jpg" alt="Logo" onerror="this.src='uploads/default.png';">
                <h5>MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            </div>
            <div class="sidebar-menu">
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&tab=dashboard" class="<?= ($active_tab == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&tab=requests" class="<?= ($active_tab == 'requests') ? 'active' : ''; ?>"><i class="fas fa-list-alt"></i> Borrowing Requests</a>
                <!-- Updated link patungong index.php para sa QR Scanner Portal -->
                <a href="index.php?uid=<?= $user_id; ?>"><i class="fas fa-qrcode"></i> QR Scanner Portal</a>
                <a href="login.php" class="text-white mt-4" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <span class="small fw-semibold text-secondary">
                    <i class="fas fa-user-shield text-success me-1"></i> <?= htmlspecialchars($current_user['full_name']); ?>
                </span>
                <span class="badge bg-success px-2 py-1" style="font-size: 10px;">CUSTODIAN</span>
                <a href="login.php" class="btn btn-danger btn-sm px-3 fw-bold" style="font-size: 11px;">Logout</a>
            </div>
        </div>

        <div class="main-card">
            <?php if ($active_tab == 'requests'): ?>
                <h5 class="fw-bold text-dark mb-3"><i class="fas fa-list-alt me-2 text-success"></i>Active Equipment Borrowing Requests & Monitoring</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <th>Selfie</th>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Course & Year</th>
                                <th>Item to Borrow</th>
                                <th>Status</th>
                                <th>Actions / Condition Monitoring</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($active_requests)): ?>
                                <?php foreach($active_requests as $row): ?>
                                    <?php 
                                        $st = trim($row['status'] ?? 'Pending');
                                        $badge_bg = 'bg-warning text-dark';
                                        $lower_st = strtolower($st);
                                        if (strpos($lower_st, 'damage') !== false) {
                                            $badge_bg = 'bg-danger text-white';
                                        } elseif (strpos($lower_st, 'waiting') !== false) {
                                            $badge_bg = 'bg-info text-dark';
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($row['selfie_image'])): ?>
                                                <img src="<?= $row['selfie_image']; ?>" width="45" height="45" style="object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" alt="Selfie">
                                            <?php else: ?>
                                                <span class="text-muted small">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['student_id']); ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['full_name']); ?></td>
                                        <td><?= htmlspecialchars($row['course_year']); ?></td>
                                        <td class="fw-semibold text-success"><?= htmlspecialchars($row['item_to_borrow']); ?></td>
                                        <td><span class="badge <?= $badge_bg; ?> px-3 py-2"><?= htmlspecialchars($st); ?></span></td>
                                        <td>
                                            <?php 
                                            // Kapag Damaged o Waiting na, ang button ay nagiging Mark Replaced (kulay dilaw)
                                            if (strpos($lower_st, 'damage') !== false || strpos($lower_st, 'waiting') !== false): 
                                            ?>
                                                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&action=mark_replaced&req_id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-dark fw-bold px-3 py-1" style="font-size: 11px;">
                                                    <i class="fas fa-sync-alt me-1"></i> Mark Replaced
                                                </a>
                                            <?php else: ?>
                                                <!-- Normal/Pending state -->
                                                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&action=clear&req_id=<?= $row['id']; ?>" class="btn btn-success btn-sm fw-semibold px-2 py-1" style="font-size: 11px;">Mark Good / Clear</a>
                                                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&action=damaged&req_id=<?= $row['id']; ?>" class="btn btn-danger btn-sm fw-semibold px-2 py-1" style="font-size: 11px;">Mark Damaged</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-muted py-4 small">Walang kasalukuyang aktibong borrowing requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h5 class="fw-bold text-dark mb-3">Dashboard Overview</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="text-muted small">Total Active Requests</h6>
                            <h3 class="fw-bold text-success mb-0"><?= count($active_requests); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
