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

// Kunin ang student_id mula sa URL o scanner query
$raw_student_id = $_GET['student_id'] ?? '';
$student_id = trim($raw_student_id);

$borrower_data = null;

// PARSING LOGIC: Linisin ang QR code format (hal. MQCQC-ID: 123456 | Name: ...)
if (!empty($student_id)) {
    if (preg_match('/MQCQC-ID:\s*([^\|]+)/i', $student_id, $matches)) {
        $student_id = trim($matches[1]);
    } else {
        $parts = explode('|', $student_id);
        $student_id = trim(str_replace('MQCQC-ID:', '', $parts[0]));
    }

    // Hanapin ang estudyante sa database gamit ang nalinis na Student ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $student_id, $student_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $borrower_data = $res->fetch_assoc();
        $stmt->close();
    }

    if (!$borrower_data) {
        $stmt2 = $conn->prepare("SELECT * FROM borrowers WHERE student_id = ?");
        if ($stmt2) {
            $stmt2->bind_param("s", $student_id);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $borrower_data = $res2->fetch_assoc();
            $stmt2->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Portal - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html5-qrcode library para sa camera at file upload scanning -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #ffffff; max-width: 700px; margin: 30px auto; padding: 25px; }
        .top-navbar { background-color: #217835; color: #ffffff; padding: 12px 20px; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MQCQC - QR SCANNER PORTAL</div>
        <div>
            <a href="custodian_dashboard.php?uid=<?= $_GET['uid'] ?? 1; ?>" class="btn btn-light btn-sm text-success fw-bold">Custodian Dashboard</a>
        </div>
    </div>

    <div class="container my-4">
        <div class="main-card">
            <h4 class="fw-bold text-dark mb-3 text-center"><i class="fas fa-qrcode me-2 text-success"></i>Scan or Upload Student QR Code</h4>
            
            <!-- QR Reader Container -->
            <div id="reader" width="100%" class="mb-3"></div>

            <hr class="my-4">

            <!-- Result Display / Error Handling -->
            <div class="card p-3 border-0 bg-light">
                <h5 class="fw-bold text-secondary mb-3"><i class="fas fa-user-shield me-2"></i>Student Verification & Equipment Status</h5>
                
                <?php if (!empty($raw_student_id)): ?>
                    <?php if ($borrower_data): ?>
                        <div class="alert alert-success">
                            <h6 class="fw-bold mb-1">Student Found!</h6>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? 'N/A')); ?></p>
                            <p class="mb-1"><strong>Student ID:</strong> <?= htmlspecialchars($borrower_data['student_id'] ?? $student_id); ?></p>
                            <p class="mb-0"><strong>Course / Year:</strong> <?= htmlspecialchars($borrower_data['course_year'] ?? 'N/A'); ?></p>
                        </div>
                        <a href="borrower.php?uid=<?= $borrower_data['id'] ?? 1; ?>&sid=<?= htmlspecialchars($borrower_data['student_id'] ?? $student_id); ?>" class="btn btn-success w-100 fw-bold">Proceed to Borrower Portal</a>
                    <?php else: ?>
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> No borrower record found for Student ID: <strong><?= htmlspecialchars($student_id); ?></strong>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-muted text-center py-2 small">Please use the camera or upload a QR image file above to scan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Kapag na-scan, i-redirect sa pahina kasama ang scanned text bilang student_id parameter
            window.location.href = "index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=" + encodeURIComponent(decodedText);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        html5QrcodeScanner.render(onScanSuccess);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
