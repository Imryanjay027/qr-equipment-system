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

// Handle status updates if any action is triggered
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $action = $_GET['action'];
    $new_status = 'Pending';

    if ($action == 'approve') {
        $new_status = 'Approved';
    } elseif ($action == 'good') {
        $new_status = 'Good';
    } elseif ($action == 'damaged') {
        $new_status = 'Damaged';
    } elseif ($action == 'replaced') {
        $new_status = 'Replaced';
    }

    $update_stmt = $conn->prepare("UPDATE borrow_requests SET status = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("si", $new_status, $req_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: borrow_requests.php?uid=" . $user_id);
    exit();
}

// Kunin ang lahat ng borrow requests mula sa database
$requests = array();
$result = $conn->query("SELECT * FROM borrow_requests ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Requests - MQCQC</title>
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
                <img src="logo.jpg" alt="School Logo" onerror="this.src='uploads/default.png';">
                <h5>MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            </div>
            <div class="sidebar-menu">
                <a href="custodian_dashboard.php?uid=<?= $user_id; ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="borrow_requests.php?uid=<?= $user_id; ?>" class="active"><i class="fas fa-list-alt"></i> Borrowing Requests</a>
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
                <h4>BORROWING REQUESTS MANAGEMENT</h4>
                <p>View and manage all equipment borrowing requests</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success px-3 py-2" style="font-size: 11px;">CUSTODIAN</span>
            </div>
        </div>

        <div class="main-card">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-list-alt me-2 text-success"></i>All Borrowing Requests</h5>
            
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center bg-white">
                    <thead class="table-success">
                        <tr style="font-size: 11px;">
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Borrower Name</th>
                            <th>Equipment Name</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($requests)): ?>
                            <?php foreach ($requests as $req): ?>
                                <?php 
                                    $st = trim($req['status'] ?? 'Pending');
                                    $badge_class = 'bg-warning text-dark';
                                    $lower_st = strtolower($st);
                                    if (strpos($lower_st, 'damage') !== false) {
                                        $badge_class = 'bg-danger text-white';
                                    } elseif (strpos($lower_st, 'good') !== false || strpos($lower_st, 'cleared') !== false) {
                                        $badge_class = 'bg-success text-white';
                                    } elseif (strpos($lower_st, 'replaced') !== false) {
                                        $badge_class = 'bg-info text-dark';
                                    }
                                ?>
                                <tr style="font-size: 11px;">
                                    <td><?= $req['id']; ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($req['student_id'] ?? ''); ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($req['full_name'] ?? ''); ?></td>
                                    <td class="fw-bold text-success"><?= htmlspecialchars($req['item_to_borrow'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($req['return_date'] ?? ''); ?> <br><small class="text-muted">(<?= htmlspecialchars($req['return_time'] ?? ''); ?>)</small></td>
                                    <td>
                                        <span class="badge <?= $badge_class; ?> px-2 py-1" style="font-size: 10px;"><?= htmlspecialchars($st); ?></span>
                                    </td>
                                    <td>
                                        <a href="borrow_requests.php?uid=<?= $user_id; ?>&action=good&req_id=<?= $req['id']; ?>" class="btn btn-success btn-sm px-2 py-1 mb-1" style="font-size: 9px;" title="Mark as Good">Good</a>
                                        <a href="borrow_requests.php?uid=<?= $user_id; ?>&action=damaged&req_id=<?= $req['id']; ?>" class="btn btn-danger btn-sm px-2 py-1 mb-1" style="font-size: 9px;" title="Mark as Damaged">Damaged</a>
                                        <a href="borrow_requests.php?uid=<?= $user_id; ?>&action=replaced&req_id=<?= $req['id']; ?>" class="btn btn-warning btn-sm text-dark px-2 py-1 mb-1" style="font-size: 9px;" title="Mark as Replaced">Replaced</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4 small">Wala pang naitalang request sa sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
