<?php
// custodian_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Siguraduhing dumaan at nag-verify via OTP o regular login
$is_logged_in = false;
if ((isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) || 
    (isset($_SESSION['custodian_logged_in']) && $_SESSION['custodian_logged_in'] === true)) {
    $is_logged_in = true;
}

// Default role kung galing sa OTP login
$role = strtolower(trim($_SESSION['role'] ?? 'custodian'));

if (!$is_logged_in || ($role !== 'custodian' && $role !== 'admin')) {
    header("Location: login.php");
    exit();
}

include('db_connection.php');

// Awtomatikong gumawa ng 'uploads' folder kung wala pa sa server
$target_dir = __DIR__ . "/uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$user_id = $_SESSION['user_id'] ?? 9; // Default ID para kay Ryan Jay Leoligao (CUST-001)

// Kunin ang records ng user mula sa database kung may connection
$user = null;
if (isset($conn) && $conn) {
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }
}

// Force fallback o kunin ang photo mula sa database kung available
$photo = !empty($user['photo']) ? $user['photo'] : 'ryan.jpg';
$photo_path = 'uploads/' . $photo;

// Kung wala physically ang file sa server, gamitin ang ryan.jpg o default placeholder
if (!file_exists(__DIR__ . '/' . $photo_path)) {
    $photo_path = 'uploads/ryan.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4 mb-4">
        <span class="navbar-brand mb-0 h1">PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</span>
        <div>
            <a href="qr_scanner.php" class="btn btn-outline-light btn-sm me-2">QR Scanner Portal</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout (<?php echo htmlspecialchars($user['full_name'] ?? 'Ryan Jay Leoligao'); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <!-- Profile Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center">
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Picture" class="profile-img me-3" onerror="this.src='uploads/default.png';">
                <div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name'] ?? 'Ryan Jay Leoligao'); ?></h4>
                    <p class="text-muted mb-1">Role: Property Custodian</p>
                    <span class="badge bg-secondary">ID: CUST-001</span>
                </div>
            </div>
        </div>

        <!-- Active Requests & Monitoring Section -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Active Equipment Borrowing Requests & Monitoring
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>PHOTO</th>
                                <th>STUDENT ID</th>
                                <th>FULL NAME</th>
                                <th>ITEM TO BORROW</th>
                                <th>RETURN DUE</th>
                                <th>STATUS</th>
                                <th>ACTIONS / CONDITION MONITORING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-muted py-4">No active or pending equipment borrowing requests found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
