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

// Siguraduhing may borrow_requests table at tamang column types
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
$conn->query("ALTER TABLE borrow_requests MODIFY COLUMN selfie_image LONGTEXT");

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');
$student_id_query = $_GET['sid'] ?? '';

$current_user = null;
if (!empty($user_id)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();
}

if (!$current_user && !empty($student_id_query)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id_query);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();
}

if (!$current_user) {
    $current_user = [
        'id' => 1,
        'full_name' => 'Ryan Jay Leoligao',
        'student_id' => !empty($student_id_query) ? $student_id_query : '123456',
        'course_year' => 'BSIT 3rd Year',
        'profile_pic' => ''
    ];
}

$success_msg = ''; $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kunin nang direkta mula sa hidden inputs o current_user para sigurado
    $student_id = trim($_POST['student_id'] ?? $current_user['student_id']);
    $full_name = trim($_POST['full_name'] ?? $current_user['full_name']);
    $course_year = trim($_POST['course_year'] ?? $current_user['course_year']);
    $item_to_borrow = trim($_POST['item_to_borrow'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $return_time = trim($_POST['return_time'] ?? '');
    $selfie_image = trim($_POST['selfie_image'] ?? '');

    if (!empty($item_to_borrow) && !empty($return_date) && !empty($return_time)) {
        $stmt = $conn->prepare("INSERT INTO borrow_requests (student_id, full_name, course_year, item_to_borrow, return_date, return_time, selfie_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        if ($stmt) {
            $stmt->bind_param("sssssss", $student_id, $full_name, $course_year, $item_to_borrow, $return_date, $return_time, $selfie_image);
            if ($stmt->execute()) {
                $stmt->close();
                // I-redirect agad sa borrower dashboard kasama ang sid at uid para mabasa ng tamang query
                header("Location: borrower.php?uid=" . $current_user['id'] . "&sid=" . urlencode($student_id));
                exit();
            } else {
                $error_msg = "May nangyaring error sa pag-save ng request: " . $conn->error;
            }
        }
    } else {
        $error_msg = "Paki-fill out lahat ng kinakailangang fields.";
    }
}
?>
