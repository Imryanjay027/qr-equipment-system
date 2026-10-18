<?php
// custodian_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connection.php');

// Siguraduhing naka-login at tama ang role nang hindi basta-basta nag-a-unauthorize
$is_logged_in = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $is_logged_in = true;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
if (!$is_logged_in || ($role !== 'custodian' && $role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$fullname_custodian = $_SESSION['fullname'] ?? 'Property Custodian';
$user_student_id = $_SESSION['student_id'] ?? 'CUST-001';
$user_photo = '';

// Ligtas na pagkuha ng profile gamit ang user_id o kaya ay ang email
$user_id = $_SESSION['user_id'] ?? 0;
$user_email = $_SESSION['email'] ?? 'ryanjayleoligao64@gmail.com';

if ($conn) {
    $user_query = null;
    if ($user_id > 0) {
        $user_query = mysqli_query($conn, "SELECT * FROM tbl_users WHERE id = '$user_id' LIMIT 1");
    }
    
    if ((!$user_query || mysqli_num_rows($user_query) === 0) && !empty($user_email)) {
        $user_query = mysqli_query($conn, "SELECT * FROM tbl_users WHERE email = '$user_email' LIMIT 1");
    }

    if ($user_query && mysqli_num_rows($user_query) > 0) {
        $u_data = mysqli_fetch_assoc($user_query);
        if (!empty($u_data['fullname'])) $fullname_custodian = $u_data['fullname'];
        if (!empty($u_data['student_id'])) $user_student_id = $u_data['student_id'];
        if (!empty($u_data['photo'])) $user_photo = trim($u_data['photo']);
        
        // I-save ulit sa session para permanenteng magtuloy-tuloy
        $_SESSION['user_id'] = $u_data['id'];
        $_SESSION['fullname'] = $fullname_custodian;
        $_SESSION['student_id'] = $user_student_id;
        $_SESSION['photo'] = $user_photo;
    }
}

// Ayusin ang path ng larawan
if (empty($user_photo)) {
    $user_photo_url = "uploads/default.png";
} else {
    $user_photo_url = (strpos($user_photo, 'uploads/') === false) ? "uploads/" . $user_photo : $user_photo;
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

        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; width: 100%; }

        /* Property Custodian Profile Overview Card */
        .profile-card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #1a365d; }
        .profile-avatar { width: 75px; height: 75px; border-radius: 50%; object-fit: cover; border: 3px solid #0d6efd; box-shadow: 0 2px 6px rgba(0,0,0,0.15); background-color: #e2e8f0; }
        .profile-details h2 { font-size: 18px; color: #1a365d; font-weight: 700; margin-bottom: 4px; }
        .profile-badge { background-color: #e2e8f0; color: #2d3748; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; font-family: monospace; display: inline-block; margin-top: 4px; }
        .profile-role { font-size: 13px; color: #4a5568; margin-top: 2px; font-weight: 500; }

        .module-box { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 25px; margin-bottom: 25px; overflow-x: auto; }
        .module-title { font-size: 16px; font-weight: 600; color: #1a365d; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; min-width: 800px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        th { background-color: #1a365d; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        
        .selfie-thumb { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #2b4c7e; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .student-badge { background-color: #f1f3f5; color: #343a40; padding: 3px 6px; border-radius: 4px; font-weight: 600; font-family: monospace; font-size: 12px; border: 1px solid #dee2e6; }
        .no-data { text-align: center; color: #888; padding: 40px; font-style: italic; }

        .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; border: none; cursor: pointer; color: white; text-decoration: none; display: inline-block; margin-right: 3px; margin-bottom: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn-good { background-color: #28a745; }
        .btn-good:hover { background-color: #218838; }
        .btn-damaged { background-color: #dc3545; }
        .btn-damaged:hover { background-color: #c82333; }
        .btn-replaced { background-color: #ffc107; color: #333; }
        .btn-replaced:hover { background-color: #e0a800; }

        /* Mobile Responsive Media Queries */
        @media (max-width: 768px) {
            .navbar { padding: 12px 15px; flex-direction: column; align-items: flex-start; }
            .nav-links { width: 100%; justify-content: space-between; }
            .container { margin: 15px auto; padding: 0 10px; }
            .profile-card { flex-direction: column; text-align: center; padding: 15px; }
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
    <!-- Property Custodian Profile Overview -->
    <div class="profile-card">
        <img src="<?php echo htmlspecialchars($user_photo_url); ?>" alt="Profile Picture" class="profile-avatar" onerror="this.src='uploads/default.png';">
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($fullname_custodian); ?></h2>
            <div class="profile-role">Role: <strong>Property Custodian</strong></div>
            <span class="profile-badge">ID: <?php echo htmlspecialchars($user_student_id); ?></span>
        </div>
    </div>

    <div class="module-box">
        <div class="module-title">
            <span>Active Equipment Borrowing Requests & Monitoring</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
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
                                <td><span class="student-badge"><?php echo htmlspecialchars($row['student_id'] ?? 'N/A'); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['fullname'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['course_year'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_to_borrow'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(($row['return_date'] ?? '') . ' ' . ($row['return_time'] ?? '')); ?></td>
                                <td><span style="color: #856404; background: #fff3cd; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; border: 1px solid #ffeeba;"><?php echo htmlspecialchars(!empty($row['status']) ? $row['status'] : 'Pending'); ?></span></td>
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
