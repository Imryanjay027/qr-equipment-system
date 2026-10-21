<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Ensure borrow_requests table exists
$conn->query("CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    course_year VARCHAR(100) NOT NULL,
    item_to_borrow VARCHAR(255) NOT NULL,
    return_date DATE NOT NULL,
    return_time VARCHAR(50) NOT NULL,
    selfie_image LONGTEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');
$student_id_query =$_GET['sid'] ?? '';

$current_user = null;
if (!empty($user_id)) {
    $stmt =$conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);$stmt->execute();
    $current_user =$stmt->get_result()->fetch_assoc();
}

if (!$current_user && !empty($student_id_query)) {
    $stmt =$conn->prepare("SELECT * FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id_query);$stmt->execute();
    $current_user =$stmt->get_result()->fetch_assoc();
}

if (!$current_user) {$current_user = [
        'id' => 1,
        'full_name' => 'System Custodian',
        'student_id' => !empty($student_id_query) ?$student_id_query : 'CUST001',
        'course_year' => 'Staff',
        'profile_pic' => ''
    ];
}

$success_msg = '';$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$student_id = $_POST['student_id'] ?? $current_user['student_id'];
    $full_name =$_POST['full_name'] ?? $current_user['full_name'];$course_year = $_POST['course_year'] ?? $current_user['course_year'];
    $item_to_borrow =$_POST['item_to_borrow'] ?? '';
    $return_date =$_POST['return_date'] ?? '';
    $return_time =$_POST['return_time'] ?? '';
    $selfie_image =$_POST['selfie_image'] ?? '';

    if (!empty($item_to_borrow) && !empty($return_date) && !empty($return_time)) {
        $stmt =$conn->prepare("INSERT INTO borrow_requests (student_id, full_name, course_year, item_to_borrow, return_date, return_time, selfie_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("sssssss", $student_id, $full_name,$course_year, $item_to_borrow,$return_date, $return_time,$selfie_image);
        if ($stmt->execute()) {
            header("Location: borrower.php?uid=" . $current_user['id']);
            exit();
        } else {
            $error_msg = "May nangyaring error sa pag-save ng request: " . $conn->error;
        }
    } else {
        $error_msg = "Paki-fill out lahat ng kinakailangang fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Registration - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .top-navbar { background-color: #217835; color: #ffffff; padding: 10px 15px; font-weight: bold; font-size: 0.85rem; }
        .main-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #ffffff; }
        .btn-green { background-color: #217835; color: #ffffff; font-weight: 600; border: none; border-radius: 8px; padding: 12px 24px; width: 100%; cursor: pointer; }
        .btn-green:hover { background-color: #1a602a; color: #ffffff; }
        #video, #canvas { width: 100%; max-width: 320px; border-radius: 8px; border: 2px solid #217835; }
        .system-logo { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid #217835; display: inline-block; }
    </style>
</head>
<body>

    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MQCQC - EQUIPMENT BORROWING FORM</div>
        <div>
            <a href="borrower.php?uid=<?= $current_user['id']; ?>" class="btn btn-dark btn-sm px-2 py-1" style="font-size: 0.75rem;">Back to Dashboard</a>
        </div>
    </div>

    <div class="container my-4">
        <div class="card main-card p-3 p-md-4 mx-auto" style="max-width: 700px;">
            
            <!-- Logo Section & Subtitle -->
            <div class="text-center mb-3">
                <img src="logo.jpg" alt="Logo" class="system-logo mb-2 shadow-sm" onerror="this.onerror=null; this.src='https://via.placeholder.com/80/217835/FFFFFF?text=MQCQC';">
                <h5 class="fw-bold text-success mb-1" style="font-size: 0.95rem; letter-spacing: 0.5px;">QR-BASED EQUIPMENT MONITORING SYSTEM</h5>
                <h4 class="fw-bold mb-0 text-dark fs-5">Borrow Equipment Registration</h4>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger py-2 small"><?= $error_msg; ?></div>
            <?php endif; ?>

            <form id="borrowForm" method="POST" action="">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($current_user['student_id']); ?>">
                <input type="hidden" name="full_name" value="<?= htmlspecialchars($current_user['full_name']); ?>">
                <input type="hidden" name="course_year" value="<?= htmlspecialchars($current_user['course_year']); ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold small">Full Name</label>
                    <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($current_user['full_name']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Student ID / Course & Year</label>
                    <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($current_user['student_id'] . ' - ' .$current_user['course_year']); ?>" disabled>
                </div>

                <!-- Text field input para i-type ang kagamitang hihiramin -->
                <div class="mb-3">
                    <label class="form-label fw-bold small">Equipment Name / Item to Borrow</label>
                    <input type="text" name="item_to_borrow" id="item_to_borrow" class="form-control form-control-sm" placeholder="e.g. Projector, HDMI Cable, Keyboard" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">Return Date</label>
                        <input type="date" name="return_date" id="return_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">Return Time</label>
                        <input type="time" name="return_time" id="return_time" class="form-control form-control-sm" required>
                    </div>
                </div>

                <div class="mb-3 text-center">
                    <label class="form-label fw-bold small d-block">Selfie Verification</label>
                    <div class="d-flex flex-column align-items-center gap-2">
                        <video id="video" autoplay playsinline></video>
                        <button type="button" id="snap" class="btn btn-dark btn-sm px-3">Capture Selfie</button>
                        <canvas id="canvas" style="display:none;"></canvas>
                        <input type="hidden" name="selfie_image" id="selfie_image" required>
                        <div id="preview-container" class="mt-2"></div>
                    </div>
                </div>

                <!-- Trigger button para mag-pop up ang Terms & Conditions modal -->
                <button type="button" id="openTermsBtn" class="btn btn-green mt-3">Submit Borrow Request</button>
            </form>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="
