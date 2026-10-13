<?php
// custodian_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connection.php');

$is_logged_in = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $is_logged_in = true;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
if (!$is_logged_in || ($role !== 'custodian' && $role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$fullname_custodian = 'Custodian';
if (isset($_SESSION['fullname'])) {
    $fullname_custodian = $_SESSION['fullname'];
}

if (isset($_POST['update_status']) && isset($_POST['record_id']) && isset($_POST['status'])) {
    $record_id = intval($_POST['record_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $get_info = mysqli_query($conn, "SELECT student_id, item_to_borrow FROM borrowers WHERE id = $record_id");
    if ($get_info && mysqli_num_rows($get_info) > 0) {
        $info_row = mysqli_fetch_assoc($get_info);
        $s_id = $info_row['student_id'];
        $item_name = $info_row['item_to_borrow'];
        
        mysqli_query($conn, "UPDATE borrowers SET status = '$new_status' WHERE id = $record_id");
        mysqli_query($conn, "UPDATE borrowed_equipment SET status = '$new_status' WHERE student_id = '$s_id' AND (item_to_borrow = '$item_name' OR equipment_name = '$item_name')");
    }

    header("Location: custodian_dashboard.php");
    exit();
}

$query_borrowers = "SELECT * FROM borrowers WHERE status = 'Pending' OR status = '' OR status IS NULL ORDER BY id DESC";
$result_borrowers = mysqli_query($conn, $query_borrowers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - Mary The Queen College of Quezon City</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; }
        
        .navbar { background-color: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 10px; }
        .navbar h1 { font-size: 15px; font-weight: 600; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .nav-links a { background-color: #2b4c7e; color: white; padding: 7px 14px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 500; }
        .nav-links a.logout-btn { background-color: #d9534f; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; width: 100%; }

        .module-box { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 25px; margin-bottom: 25px; overflow-x: auto; }
        .module-title { font-size: 16px; font-weight: 600; color: #1a365d; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; min-width: 750px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        th { background-color: #1a365d; color: white; font-weight: 500; }
        .no-data { text-align: center; color: #888; padding: 30px; font-style: italic; }

        .btn-action { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; border: none; cursor: pointer; color: white; text-decoration: none; display: inline-block; margin-right: 3px; margin-bottom: 3px; }
        .btn-good { background-color: #28a745; }
        .btn-damaged { background-color: #dc3545; }
        .btn-replaced { background-color: #ffc107; color: #333; }

        .selfie-thumb { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc; }

        /* Mobile Responsive Media Queries */
        @media (max-width: 768px) {
            .navbar { padding: 12px 15px; flex-direction: column; align-items: flex-start; }
            .nav-links { width: 100%; justify-content: space-between; }
            .container { margin: 15px auto; padding: 0 10px; }
            .module-box { padding: 15px; border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
    <div class="nav-links">
        <a href="index.php">QR Scanner Portal</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout (<?php echo htmlspecialchars($fullname_custodian); ?>)</a>
    </div>
</div>

<div class="container">
    <div class="module-box">
        <div class="module-title">
            <span>Active Equipment Borrowing Requests & Monitoring</span>
        </div>

        <div class="table-responsive">
            <table>
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
                    <?php if ($result_borrowers && mysqli_num_rows($result_borrowers) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_borrowers)): ?>
                            <tr>
                                <td>
                                    <?php 
                                        $photo = isset($row['selfie_image']) ? trim($row['selfie_image']) : '';
                                        $photo_url = empty($photo) ? "uploads/default.png" : (strpos($photo, 'uploads/') === false ? "uploads/" . $photo : $photo);
                                    ?>
                                    <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Selfie" class="selfie-thumb" onerror="this.src='uploads/default.png';">
                                </td>
                                <td><?php echo htmlspecialchars($row['student_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['fullname'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['course_year'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['item_to_borrow'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(($row['return_date'] ?? '') . ' ' . ($row['return_time'] ?? '')); ?></td>
                                <td><span style="color: #856404; background: #fff3cd; padding: 3px 6px; border-radius: 4px; font-weight: bold;"><?php echo htmlspecialchars(!empty($row['status']) ? $row['status'] : 'Pending'); ?></span></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <button type="submit" name="status" value="Good" class="btn-action btn-good">Mark Good</button>
                                        <button type="submit" name="status" value="Damaged" class="btn-action btn-damaged">Mark Damaged</button>
                                        <button type="submit" name="status" value="Replaced" class="btn-action btn-replaced">Mark Replaced</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">No active or pending equipment borrowing requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>