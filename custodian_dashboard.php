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

// Get User ID from Session or URL Parameter
$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');
if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

// Safe User Details Fetching
$current_user = [
    'id' => $user_id,
    'full_name' => 'Property Custodian',
    'role' => 'Custodian'
];

$user_result = $conn->query("SELECT * FROM tbl_users WHERE id = " . intval($user_id));
if ($user_result && $user_result->num_rows > 0) {
    $u_data = $user_result->fetch_assoc();
    $current_user['full_name'] = $u_data['full_name'] ?? ($u_data['fullname'] ?? ($u_data['name'] ?? 'Property Custodian'));
    $current_user['role'] = $u_data['role'] ?? ($u_data['user_type'] ?? 'Custodian');
}

$_SESSION['user_id'] = $current_user['id'];
$_SESSION['role'] = $current_user['role'];
$_SESSION['full_name'] = $current_user['full_name'];

// Handle Status Updates (Mark Good / Damaged / Replaced)
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $action = $_GET['action'];
    $new_status = 'Pending';

    if ($action == 'good') {
        $new_status = 'Cleared';
    } elseif ($action == 'damaged') {
        $new_status = 'Damage needs for replacement';
    } elseif ($action == 'replaced') {
        $new_status = 'Replaced';
    }

    $update_stmt = $conn->prepare("UPDATE borrowed_equipment SET status = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("si", $new_status, $req_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: custodian_dashboard.php?uid=" . $user_id);
    exit();
}

// Dynamic Column Detection para sa Borrowed Equipment at Borrowers tables
$eq_columns = [];
$res_c = $conn->query("SHOW COLUMNS FROM borrowed_equipment");
if ($res_c) {
    while ($rc = $res_c->fetch_assoc()) { $eq_columns[] = $rc['Field']; }
}

$borrower_columns = [];
$res_b = $conn->query("SHOW COLUMNS FROM borrowers");
if ($res_b) {
    while ($rb = $res_b->fetch_assoc()) { $borrower_columns[] = $rb['Field']; }
}

$be_stud_col = in_array('student_id', $eq_columns) ? 'student_id' : (in_array('stud_id', $eq_columns) ? 'stud_id' : 'student_id');
$b_stud_col = in_array('student_id', $borrower_columns) ? 'student_id' : (in_array('stud_id', $borrower_columns) ? 'stud_id' : 'student_id');
$b_name_col = in_array('fullname', $borrower_columns) ? 'fullname' : (in_array('full_name', $borrower_columns) ? 'full_name' : 'name');

// Robust Query para sa Borrowing Requests Table
$requests_query = "SELECT be.id as req_id, be.{$be_stud_col} as student_id, be.item_to_borrow, be.status, 
                          b.{$b_name_col} as fullname, b.course_year, b.selfie_image 
                   FROM borrowed_equipment be 
                   LEFT JOIN borrowers b ON be.{$be_stud_col} = b.{$b_stud_col} 
                   ORDER BY be.id DESC";

$result_requests = @$conn->query($requests_query);
$total_active = ($result_requests) ? $result_requests->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        :root { --sidebar-width: 260px; --primary-color: #1a365d; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand img { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #ffc107; }
        .sidebar-brand h5 { font-size: 13px; font-weight: 700; line-height: 1.4; margin: 0; }
        
        .sidebar-menu { padding: 15px 10px; flex-grow: 1; }
        .sidebar-menu a { display: flex; align-items: center; color: rgba(255,255,255,0.8); padding: 12px 15px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; margin-bottom: 5px; transition: all 0.2s; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(255,255,255,0.12); color: white; }
        .sidebar-menu a i { width: 25px; font-size: 15px; }

        .sidebar-footer { padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 11px; color: rgba(255,255,255,0.6); text-align: center; }

        .main-content { margin-left: var(--sidebar-width); padding: 25px; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .top-navbar h4 { font-size: 16px; font-weight: 700; color: var(--primary-color); margin: 0; }
        
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--primary-color); }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-info h3 { font-size: 22px; font-weight: 700; margin: 0; color: #333; }
        .stat-info p { font-size: 12px; color: #777; margin: 0; font-weight: 500; }
        .stat-icon { width: 45px; height: 45px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: #eef2f7; color: var(--primary-color); }

        .content-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        
        .table th { background-color: var(--primary-color) !important; color: white !important; font-size: 12px; font-weight: 600; vertical-align: middle; }
        .table td { font-size: 12px; vertical-align: middle; }
        .student-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; background: #ddd; }
        .badge-status { padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 11px; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-cleared { background-color: #d4edda; color: #155724; }
        .badge-damaged { background-color: #f8d7da; color: #721c24; }
        .badge-replaced { background-color: #d1ecf1; color: #0c5460; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <audio id="beepAudio" src="beep.mp3" preload="auto"></audio>

    <div class="sidebar">
        <div>
            <div class="sidebar-brand">
                <img src="logo.jpg" alt="School Logo" onerror="this.src='uploads/default.png';">
                <h5>MARY THE QUEEN COLLEGE OF QUEZON CITY</h5>
            </div>
            <div class="sidebar-menu">
                <a href="javascript:void(0);" class="active" onclick="switchTab('dashboard-section', this)"><i class="fas fa-home"></i> Dashboard</a>
                <a href="javascript:void(0);" onclick="switchTab('requests-section', this)"><i class="fas fa-clipboard-list"></i> Borrowing Requests</a>
                <a href="index.php?uid=<?= $user_id; ?>"><i class="fas fa-qrcode"></i> QR Scanner Portal</a>
                <a href="login.php" class="text-danger mt-4" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="sidebar-footer">
            <p class="mb-0">"Discipline today, excellence tomorrow."</p>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h4>PROPERTY CUSTODIAN PANEL</h4>
            <div class="d-flex align-items-center">
                <span class="small fw-semibold text-secondary me-3">
                    <i class="fas fa-user-shield text-warning me-1"></i> <?= htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <span class="badge bg-primary text-uppercase px-2 py-1"><?= htmlspecialchars($_SESSION['role']); ?></span>
            </div>
        </div>

        <!-- DASHBOARD TAB -->
        <div id="dashboard-section" class="tab-section">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $total_active; ?></h3>
                            <p>Total Active Requests</p>
                        </div>
                        <div class="stat-icon text-primary"><i class="fas fa-boxes"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card success">
                        <div class="stat-info">
                            <h3>Active</h3>
                            <p>Equipment Monitoring Status</p>
                        </div>
                        <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card warning">
                        <div class="stat-info">
                            <h3>MQCQC</h3>
                            <p>System Portal Active</p>
                        </div>
                        <div class="stat-icon text-warning"><i class="fas fa-school"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REQUESTS TAB -->
        <div id="requests-section" class="tab-section" style="display: none;">
            <div class="content-card">
                <h5 class="fw-bold text-dark mb-4"><i class="fas fa-clipboard-list me-2 text-primary"></i>Active Equipment Borrowing Requests & Monitoring</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
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
                            <?php if ($result_requests && $result_requests->num_rows > 0): ?>
                                <?php while ($row = $result_requests->fetch_assoc()): ?>
                                    <?php 
                                        $status = trim($row['status'] ?? 'Pending');
                                        $badge_class = 'badge-pending';
                                        $lower_st = strtolower($status);
                                        if (strpos($lower_st, 'cleared') !== false || strpos($lower_st, 'good') !== false) {
                                            $badge_class = 'badge-cleared';
                                        } elseif (strpos($lower_st, 'damage') !== false) {
                                            $badge_class = 'badge-damaged';
                                        } elseif (strpos($lower_st, 'replaced') !== false) {
                                            $badge_class = 'badge-replaced';
                                        }

                                        $photo = trim($row['selfie_image'] ?? '');
                                        if (empty($photo)) {
                                            $photo = 'uploads/default.png';
                                        } elseif (strpos($photo, 'uploads/') === false) {
                                            $photo = 'uploads/' . $photo;
                                        }
                                    ?>
                                    <tr>
                                        <td><img src="<?= htmlspecialchars($photo); ?>" alt="Selfie" class="student-avatar" onerror="this.src='uploads/default.png';"></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['student_id']); ?></td>
                                        <td><?= htmlspecialchars($row['fullname'] ?? '---'); ?></td>
                                        <td><?= htmlspecialchars($row['course_year'] ?? '---'); ?></td>
                                        <td><?= htmlspecialchars($row['item_to_borrow']); ?></td>
                                        <td><span class="badge badge-status <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                        <td>
                                            <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=good" class="btn btn-success btn-sm px-2 py-1 fw-semibold" style="font-size: 11px;">Mark Good</a>
                                            <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=damaged" class="btn btn-danger btn-sm px-2 py-1 fw-semibold" style="font-size: 11px;">Mark Damaged</a>
                                            <a href="custodian_dashboard.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=replaced" class="btn btn-warning btn-sm px-2 py-1 fw-semibold text-dark" style="font-size: 11px;">Mark Replaced</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No active borrowing requests recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const beepAudio = document.getElementById('beepAudio');
        const currentUid = "<?= $user_id; ?>";

        function switchTab(sectionId, element) {
            document.querySelectorAll('.tab-section').forEach(sec => sec.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
            
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            if(element) element.classList.add('active');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
