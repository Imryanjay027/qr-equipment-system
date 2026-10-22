<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$username = 'root';
$password = '';
$database = 'qrcode';

$conn = null;
$hosts = ['127.0.0.1', 'localhost', 'mysql'];

foreach ($hosts as $host) {
    try {
        $conn = @new mysqli($host, $username, $password, $database);
        if ($conn && !$conn->connect_error) {
            break;
        }
    } catch (\Throwable $e) {
        // Subukan ang susunod na host kung mag-fail
    }
}

if (!$conn || $conn->connect_error) {
    die("<div class='alert alert-danger m-4'><strong>Database Connection Failed:</strong> Tiyaking nakabukas o tumatakbo ang iyong MySQL/XAMPP server.</div>");
}

$student_id = $_GET['student_id'] ?? '';
$borrower_data = null;
$borrowed_items = [];

if (!empty($student_id)) {
    $clean_id = trim(str_replace('MQCQC-ID:', '', $student_id));
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $clean_id, $clean_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $borrower_data = $res->fetch_assoc();
        $stmt->close();
    }

    $sql = "SELECT * FROM borrow_requests ORDER BY id DESC";
    $res_req = $conn->query($sql);
    if ($res_req) {
        while($row = $res_req->fetch_assoc()) {
            $borrowed_items[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Scanner Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 800px;">
        <h3 class="text-success mb-3">QR Scanner Portal</h3>
        
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="student_id" class="form-control" placeholder="Enter or Scan Student ID" value="<?php echo htmlspecialchars($student_id); ?>">
                <button type="submit" class="btn btn-success">Verify</button>
            </div>
        </form>

        <?php if (!empty($student_id)): ?>
            <?php if ($borrower_data): ?>
                <div class="alert alert-success">
                    <h5 class="mb-1">Student Found!</h5>
                    <p class="mb-0"><strong>Name:</strong> <?php echo htmlspecialchars($borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? 'N/A')); ?></p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">No student record found for: <?php echo htmlspecialchars($student_id); ?></div>
            <?php endif; ?>

            <h5 class="mt-4">Active Borrowed Items (Total nahanap: <?php echo count($borrowed_items); ?>)</h5>
            <?php if (!empty($borrowed_items)): ?>
                <ul class="list-group">
                    <?php foreach ($borrowed_items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong><?php echo htmlspecialchars($item['item_to_borrow'] ?? ''); ?></strong> (Borrower: <?php echo htmlspecialchars($item['full_name'] ?? ''); ?>)</span>
                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($item['status'] ?? 'Pending'); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-info">Walang nakitang kagamitan sa borrow_requests table.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
