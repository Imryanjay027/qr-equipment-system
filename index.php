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

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');

// Handle scanned student_id if passed via URL
$borrower_info = null;
if (isset($_GET['student_id'])) {
    $student_id = trim($_GET['student_id']);
    $stmt = $conn->prepare("SELECT * FROM borrowers WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $borrower_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Portal - Mary the Queen College of Quezon City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { background-color: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .scanner-container { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; max-width: 600px; margin: 40px auto; }
        #reader { width: 100%; border-radius: 8px; overflow: hidden; background: #000; min-height: 250px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <audio id="beepAudio" src="beep.mp3" preload="auto"></audio>

    <div class="top-navbar">
        <h2 class="fs-6 fw-bold m-0 text-uppercase">QR Scanner Portal - MQCQC</h2>
        <div>
            <a href="custodian_dashboard.php?uid=<?= htmlspecialchars($user_id); ?>" class="btn btn-light btn-sm fw-semibold">Back to Dashboard</a>
        </div>
    </div>

    <div class="container">
        <div class="scanner-container">
            <h4 class="fw-bold text-dark mb-3 text-center"><i class="fas fa-qrcode text-primary me-2"></i>Scan Student QR Code</h4>
            <p class="text-muted small text-center mb-4">Use your camera or upload a QR image to verify student equipment requests.</p>
            
            <div id="reader"></div>
            <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold mb-3" id="start-camera-btn">Start Camera</button>

            <hr class="my-4">

            <div class="mb-3">
                <label class="form-label small fw-bold">Or Upload QR Image File</label>
                <input type="file" id="qr-input-file" class="form-control form-control-sm" accept="image/*">
            </div>

            <?php if (isset($_GET['student_id'])): ?>
                <div class="alert alert-info mt-4">
                    <h6 class="fw-bold">Scanned Student ID: <?= htmlspecialchars($_GET['student_id']); ?></h6>
                    <?php if ($borrower_info): ?>
                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($borrower_info['fullname'] ?? '---'); ?></p>
                        <p class="mb-0"><strong>Course & Year:</strong> <?= htmlspecialchars($borrower_info['course_year'] ?? '---'); ?></p>
                    <?php else: ?>
                        <p class="mb-0 text-danger">No borrower record found for this Student ID.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const beepAudio = document.getElementById('beepAudio');
        const currentUid = "<?= htmlspecialchars($user_id); ?>";

        function playBeepAndRedirect(url) {
            beepAudio.play().catch(e => console.log("Audio blocked:", e));
            setTimeout(() => { window.location.href = url; }, 300);
        }

        // Camera Scanner Initialization
        let html5QrcodeScanner = null;
        const startCamBtn = document.getElementById('start-camera-btn');
        if(startCamBtn) {
            startCamBtn.addEventListener('click', function() {
                if (!html5QrcodeScanner) {
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                    html5QrcodeScanner.render((decodedText) => {
                        html5QrcodeScanner.clear();
                        playBeepAndRedirect("index.php?uid=" + currentUid + "&student_id=" + encodeURIComponent(decodedText));
                    }, (err) => {});
                    this.innerText = "Stop Camera";
                    this.className = "btn btn-danger btn-sm w-100 fw-semibold mb-3";
                }
            });
        }

        // Safe File Upload QR Scanning with Error Handling (Prevents White Screen)
        const fileInput = document.getElementById('qr-input-file');
        if (fileInput) {
            fileInput.addEventListener('change', e => {
                if (e.target.files.length === 0) return;
                const imageFile = e.target.files[0];
                
                const html5QrCode = new Html5Qrcode("reader");
                html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    playBeepAndRedirect("index.php?uid=" + currentUid + "&student_id=" + encodeURIComponent(decodedText));
                })
                .catch(err => {
                    alert("Unable to read QR code from the uploaded image. Please try another clear image.");
                    console.error("QR Scan Error: ", err);
                });
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
