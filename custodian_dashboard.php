<?php
// index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Database Connection muna para masuri ang user sakaling galing sa session o URL uid
$host = "wfbyfhr4404k2y9u2spiv101";
$username = "root";
$password = "ryan072797";
$database = "qrcode";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kunin ang user id mula sa session o kaya ay sa URL parameter (?uid=)
$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');

if (!empty($user_id)) {
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user_data) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = $user_data['role'];
        $_SESSION['fullname'] = $user_data['full_name'] ?? ($user_data['fullname'] ?? 'Custodian');
    }
}

// Suriin kung naka-log in ang custodian o admin
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!$is_logged_in || (strpos($role, 'custodian') === false && strpos($role, 'admin') === false)) {
    header("Location: login.php");
    exit();
}

$fullname_custodian = $_SESSION['fullname'] ?? 'Custodian';
