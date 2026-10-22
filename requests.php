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

// Kunin ang user id mula sa session o URL
$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');
if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

// Kung may pinindot na action button (Mark Good, Damaged, o Replaced)
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
    
    header("Location: requests.php?uid=" . $user_id);
    exit();
}

// Kunin ang mga active borrowing requests mula sa database
$requests_query = "SELECT be.id as req_id, be.student_id, be.item_to_borrow, be.borrow_date, be.status, 
                          b.fullname, b.course_year, b.selfie_image 
                   FROM borrowed_equipment be 
                   LEFT JOIN borrowers b ON be.student_id = b.student_id 
                   ORDER BY be.id DESC";
$result = $conn->query($requests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - Mary the Queen College of Quezon City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { background-color: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .top-navbar h2 { font-size: 14px; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; margin: 30px auto; max-width: 1300px; }
        .table th { background-color: #1a365d !important; color: white !important; font-size: 12px; font-weight: 500; vertical-align: middle; }
        .table td { font-size: 12px; vertical-align: middle; }
        .student-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #ddd; }
        .badge-status { padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 11px; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-cleared { background-color: #d4edda; color: #155724; }
        .badge-damaged { background-color: #f8d7da; color: #721c24; }
        .badge-replaced { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

    <div class="top-navbar">
        <h2>PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
        <div>
            <a href="index.php?uid=<?= $user_id; ?>" class="btn btn-primary btn-sm fw-semibold me-2">QR Scanner Portal</a>
            <a href="login.php" class="btn btn-danger btn-sm fw-semibold">Logout (<?= htmlspecialchars($_SESSION['full_name'] ?? 'Custodian'); ?>)</a>
        </div>
    </div>

    <div class="container">
        <div class="table-container">
            <h4 class="fw-bold text-dark mb-4" style="font-size: 16px;">Active Equipment Borrowing Requests & Monitoring</h4>
            
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Selfie</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course & Year</th>
                            <th>Item to Borrow</th>
                            <th>Return Due</th>
                            <th>Status</th>
                            <th>Actions / Condition Monitoring</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
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
                                    <td>
                                        <img src="<?= htmlspecialchars($photo); ?>" alt="Selfie" class="student-avatar" onerror="this.src='uploads/default.png';">
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['student_id']); ?></td>
                                    <td><?= htmlspecialchars($row['fullname'] ?? '---'); ?></td>
                                    <td><?= htmlspecialchars($row['course_year'] ?? '---'); ?></td>
                                    <td><?= htmlspecialchars($row['item_to_borrow']); ?></td>
                                    <td><?= htmlspecialchars($row['borrow_date']); ?></td>
                                    <td><span class="badge badge-status <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                    <td>
                                        <a href="requests.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=good" class="btn btn-success btn-sm px-2 py-1 fw-semibold" style="font-size: 11px;">Mark Good</a>
                                        <a href="requests.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=damaged" class="btn btn-danger btn-sm px-2 py-1 fw-semibold" style="font-size: 11px;">Mark Damaged</a>
                                        <a href="requests.php?uid=<?= $user_id; ?>&req_id=<?= $row['req_id']; ?>&action=replaced" class="btn btn-warning btn-sm px-2 py-1 fw-semibold text-dark" style="font-size: 11px;">Mark Replaced</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Wala pang nakatalang active borrowing requests.</td>
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
