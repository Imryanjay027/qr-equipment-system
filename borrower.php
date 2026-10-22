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

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');
$student_id_query = $_GET['sid'] ?? '';

// Handle Mark as Replaced action
if (isset($_GET['action']) && $_GET['action'] == 'mark_replaced' && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $update_stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Waiting for Custodian approval' WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("i", $req_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: borrower.php?uid=" . $user_id . (!empty($student_id_query) ? "&sid=" . $student_id_query : ""));
    exit();
}

$current_user = null;
if (!empty($user_id)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $current_user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

if (!$current_user && !empty($student_id_query)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id_query);
        $stmt->execute();
        $current_user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

if (!$current_user) {
    $current_user = [
        'id' => !empty($user_id) ? $user_id : 1,
        'full_name' => 'Ryan Jay Leoligao',
        'student_id' => !empty($student_id_query) ? $student_id_query : '123456',
        'course_year' => 'BSIT 3rd Year',
        'profile_pic' => ''
    ];
}

$sid = $current_user['student_id'];
$uid = $current_user['id'];

// Kunin ang mga active items ng estudyante (Pending, Damaged, o Waiting)
$borrow_query = $conn->prepare("SELECT * FROM borrow_requests WHERE (student_id = ? OR full_name = ?) AND (status = 'Pending' OR status LIKE '%Damage%') ORDER BY id DESC");
$borrow_query->bind_param("ss", $sid, $current_user['full_name']);
$borrow_query->execute();
$borrowed_items = $borrow_query->get_result();
$borrow_query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower's Dashboard - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
        
        .profile-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .profile-info { display: flex; align-items: center; gap: 15px; }
        .profile-avatar { width: 65px; height: 65px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; }
        
        .qr-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }

        .nav-tabs { border-bottom: none; margin-bottom: 20px; gap: 10px; }
        .nav-tabs .nav-link { background-color: #f1f5f9; color: #64748b; font-weight: 600; border: none; border-radius: 8px; padding: 10px 20px; font-size: 12px; }
        .nav-tabs .nav-link.active { background-color: var(--primary-color); color: white; }

        .table th { background-color: var(--primary-color) !important; color: white !important; font-size: 12px; font-weight: 600; vertical-align: middle; border: none; }
        .table td { font-size: 12px; vertical-align: middle; }

        .action-banner { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; display: flex; justify-content: space-between; align-items: center; margin-top: 25px; flex-wrap: wrap; gap: 15px; }
        .btn-borrow { background-color: var(--primary-color); color: white; font-weight: 600; border-radius: 8px; padding: 10px 20px; font-size: 13px; border: none; }
        .btn-borrow:hover { background-color: #1a602a; color: white; }

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
                <a href="borrower.php?uid=<?= $uid; ?>" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="registration.php?uid=<?= $uid; ?>"><i class="fas fa-plus-circle"></i> Borrow an Item</a>
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
                <h4>BORROWER'S DASHBOARD</h4>
                <p>Equipment Borrowing, Return & Monitoring Portal</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="small fw-semibold text-secondary">
                    <i class="fas fa-user-graduate text-success me-1"></i> <?= htmlspecialchars($current_user['full_name']); ?>
                </span>
                <a href="login.php" class="btn btn-danger btn-sm px-3 fw-bold" style="font-size: 11px;">Logout</a>
            </div>
        </div>

        <div class="main-card">
            <!-- Profile & QR Section -->
            <div class="profile-section">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($current_user['full_name']); ?></h5>
                        <p class="text-muted mb-0 small">Student ID: <strong><?= htmlspecialchars($current_user['student_id']); ?></strong></p>
                        <p class="text-muted mb-0 small">Course / Year: <strong><?= htmlspecialchars($current_user['course_year']); ?></strong></p>
                    </div>
                </div>
                
                <div class="qr-box">
                    <div id="qrcode" class="d-flex justify-content-center"></div>
                    <span class="text-muted d-block mt-1" style="font-size: 10px;">Your QR Code</span>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="portalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="borrowed-tab" data-bs-toggle="tab" data-bs-target="#borrowed-content" type="button" role="tab">My Borrowed Equipment</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="portalTabsContent">
                <div class="tab-pane fade show active" id="borrowed-content" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Equipment Name</th>
                                    <th>Borrow Date / Due Date</th>
                                    <th>Status / Action</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($borrowed_items && $borrowed_items->num_rows > 0): ?>
                                    <?php while($row = $borrowed_items->fetch_assoc()): ?>
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
                                            <td class="fw-semibold"><?= htmlspecialchars($row['student_id']); ?></td>
                                            <td class="fw-bold"><i class="fas fa-laptop me-1 text-secondary"></i> <?= htmlspecialchars($row['item_to_borrow']); ?></td>
                                            <td><?= htmlspecialchars($row['return_date']) . '<br><small class="text-muted">(' . htmlspecialchars($row['return_time']) . ')</small>'; ?></td>
                                            <td><span class="badge <?= $badge_bg; ?> px-3 py-2"><?= htmlspecialchars($st); ?></span></td>
                                            <td>
                                                <?php if (strpos($lower_st, 'damage') !== false): ?>
                                                    <a href="borrower.php?uid=<?= $uid; ?>&sid=<?= $sid; ?>&req_id=<?= $row['id']; ?>&action=mark_replaced" class="btn btn-primary btn-sm px-3 py-1 fw-semibold" style="font-size: 11px;">Mark as Replaced</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">---</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-muted py-4 small">Walang kasalukuyang hiniram na aktibong kagamitan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Borrow Item Action Banner -->
            <div class="action-banner">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white rounded p-3 fs-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Need to borrow an item?</h6>
                        <p class="text-muted mb-0 small">Submit a new borrowing request from the inventory.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-borrow" onclick="window.location.replace('registration.php?uid=<?= $uid; ?>');">
                    <i class="fas fa-plus me-1"></i> Borrow an Item
                </button>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const qrDiv = document.getElementById('qrcode');
            if (qrDiv) {
                qrDiv.innerHTML = "";
                new QRCode(qrDiv, {
                    text: "MQCQC-ID: <?= htmlspecialchars($current_user['student_id']); ?> | Name: <?= htmlspecialchars($current_user['full_name']); ?>",
                    width: 75,
                    height: 75,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            }
        });
    </script>
</body>
</html>
