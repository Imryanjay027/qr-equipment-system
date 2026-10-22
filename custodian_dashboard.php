<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION (Safe Environment Check)
$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username,$password, $database, (int)$port);
if ($conn->connect_error) {$conn = @new mysqli('mysql', $username,$password, $database, (int)$port);
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

// Dynamic Check for tbl_users columns
$user_cols = [];
$res_user_cols =$conn->query("SHOW COLUMNS FROM tbl_users");
if ($res_user_cols) {
    while ($row =$res_user_cols->fetch_assoc()) {
        $user_cols[] =$row['Field'];
    }
}

$name_col = in_array('full_name',$user_cols) ? 'full_name' : (in_array('fullname', $user_cols) ? 'fullname' : 'full_name');$role_col = in_array('role', $user_cols) ? 'role' : (in_array('user_type',$user_cols) ? 'user_type' : 'role');

// Get User Details
$stmt =$conn->prepare("SELECT *, {$name_col} as full_name, {$role_col} as role FROM tbl_users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();$current_user = $stmt->get_result()->fetch_assoc();$stmt->close();
} else {
    $current_user = null;
}

if (!$current_user) {
    header("Location: login.php");
    exit();
}

$_SESSION['user_id'] =$current_user['id'];
$_SESSION['role'] =$current_user['role'] ?? 'Custodian';
$_SESSION['full_name'] =$current_user['full_name'] ?? 'Property Custodian';

// Handle Status Updates (Mark Good / Damaged / Replaced)
if (isset($_GET['action']) && isset($_GET['req_id'])) {$req_id = intval($_GET['req_id']);$action = $_GET['action'];$new_status = 'Pending';

    if ($action == 'good') {$new_status = 'Cleared';
    } elseif ($action == 'damaged') {$new_status = 'Damage needs for replacement';
    } elseif ($action == 'replaced') {$new_status = 'Replaced';
    }

    $update_stmt =$conn->prepare("UPDATE borrowed_equipment SET status = ? WHERE id = ?");
    if ($update_stmt) {$update_stmt->bind_param("si", $new_status,$req_id);
        $update_stmt->execute();$update_stmt->close();
    }
    header("Location: custodian_dashboard.php?uid=" . $user_id);
    exit();
}

// Dynamic Check for borrowed_equipment columns
$eq_cols = [];
$res_eq_cols =$conn->query("SHOW COLUMNS FROM borrowed_equipment");
if ($res_eq_cols) {
    while ($row =$res_eq_cols->fetch_assoc()) {
        $eq_cols[] =$row['Field'];
    }
}

$date_col = 'id';
if (in_array('borrow_date', $eq_cols)) {$date_col = 'borrow_date';
} elseif (in_array('date_borrowed', $eq_cols)) {$date_col = 'date_borrowed';
} elseif (in_array('created_at', $eq_cols)) {$date_col = 'created_at';
}

// Query for Borrowing Requests Table
$requests_query = "SELECT be.id as req_id, be.student_id, be.item_to_borrow, be.{$date_col} as borrow_date, be.status, 
                          b.fullname, b.course_year, b.selfie_image 
                   FROM borrowed_equipment be 
                   LEFT JOIN borrowers b ON be.student_id = b.student_id 
                   ORDER BY be.id DESC";
$result_requests =$conn->query($requests_query);$total_active = ($result_requests) ?$result_requests->num_rows : 0;
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
        .sidebar-menu a { display: flex; align-items: center; color: rgba(255,255,255,0.8); padding: 12px 1
