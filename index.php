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

// Handle actions directly from QR Scanner Portal (Mark Good/Clear, Mark Damaged, Mark Replaced)
if (isset($_GET['action']) && isset($_GET['req_id']) && isset($_GET['student_id'])) {
    $req_id = intval($_GET['req_id']);
    $action = $_GET['action'];
    $redirect_sid = $_GET['student_id'];

    if ($action == 'clear') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Cleared' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'damaged') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Damage needs for replacement' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'mark_replaced') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Replaced' WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?uid=" . ($_GET['uid'] ?? 1) . "&student_id=" . urlencode($redirect_sid));
    exit();
}

$raw_student_id = $_GET['student_id'] ?? '';
$student_id = trim($raw_student_id);
$borrower_data = null;
$borrowed_items = [];

if (!empty($student_id)) {
    if (preg_match('/MQCQC-ID:\s*([^\|]+)/i', $student_id, $matches)) {
        $student_id = trim($matches[1]);
    } else {
        $parts = explode('|', $student_id);
        $student_id = trim(str_replace('MQCQC-ID:', '', $parts[0]));
    }

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

    if ($borrower_data) {
        $b_name = $borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? '');
        $b_sid = $borrower_data['student_id'] ?? $student_id;
        $b_id = $borrower_data['id'] ?? '';
        
        $req_stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE (student_id = ? OR full_name = ? OR student_id = ? OR student_id = ?) AND status != 'Cleared' AND status != 'Replaced' ORDER BY id DESC");
        if ($req_stmt) {
            $req_stmt->bind_param("ssss", $b_sid, $b_name, $b_id, $student_id);
            $req_stmt->execute();
            $borrowed_items = $req_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $req_stmt->close();
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
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #ffffff; max-width: 800px; margin: 30px auto; padding: 25px; }
        .top-navbar { background-color: #217835; color: #ffffff; padding: 12px 20px; font-weight: bold; font-size: 0.9rem; }
        #reader { width: 100%; max-width: 400px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 2px solid #e9ecef; }
    </style>
</head>
<body>

    <!-- Audio Beep Effect -->
    <audio id="beepAudio" src="beep.mp3" preload="auto"></audio>

    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MQCQC - QR SCANNER PORTAL</div>
        <div>
            <a href="custodian_dashboard.php?uid=<?= $_GET['uid'] ?? 1; ?>" class="btn btn-light btn-sm text-success fw-bold">Custodian Dashboard</a>
        </div>
    </div>

    <div class="container my-4">
        <div class="main-card">
            <h4 class="fw-bold text-dark mb-3 text-center"><i class="fas fa-qrcode me-2 text-success"></i>Scan or Upload Student QR Code</h4>
            
            <div id="reader"></div>

            <div class="text-center mt-3">
                <button id="start-camera" class="btn btn-primary btn-sm px-4 fw-bold">Start Camera</button>
                <button id="stop-camera" class="btn btn-danger btn-sm px-4 fw-bold" style="display:none;">Stop Camera</button>
            </div>

            <hr class="my-4">

            <!-- UPLOAD QR IMAGE FILE OPTION -->
            <div class="mb-3 p-3 bg-light rounded border">
                <label for="qr-image-input" class="form-label fw-bold text-secondary small mb-1"><i class="fas fa-upload me-1"></i> Or Upload QR Image File</label>
                <input class="form-control form-control-sm" type="file" id="qr-image-input" accept="image/*">
            </div>

            <!-- Result Display / Error Handling & Equipment Status -->
            <div class="card p-3 border-0 bg-light">
                <h5 class="fw-bold text-secondary mb-3"><i class="fas fa-user-shield me-2"></i>Student Verification & Equipment Status</h5>
                
                <?php if (!empty($raw_student_id)): ?>
                    <?php if ($borrower_data): ?>
                        <div class="alert alert-success mb-3">
                            <h6 class="fw-bold mb-1">Student Found!</h6>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? 'N/A')); ?></p>
                            <p class="mb-1"><strong>Student ID:</strong> <?= htmlspecialchars($borrower_data['student_id'] ?? $student_id); ?></p>
                            <p class="mb-0"><strong>Course / Year:</strong> <?= htmlspecialchars($borrower_data['course_year'] ?? 'N/A'); ?></p>
                        </div>

                        <!-- Hiniram na Equipment, Selfie, at Status Monitoring -->
                        <h6 class="fw-bold text-dark mt-3 mb-2"><i class="fas fa-laptop me-1 text-success"></i> Active Borrowed Items & Monitoring</h6>
                        <?php if (!empty($borrowed_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center bg-white">
                                    <thead class="table-success">
                                        <tr style="font-size: 11px;">
                                            <th>Selfie Picture</th>
                                            <th>Equipment Name</th>
                                            <th>Due Date / Time</th>
                                            <th>Status</th>
                                            <th>Custodian Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowed_items as $item): ?>
                                            <?php 
                                                $st = trim($item['status'] ?? 'Pending');
                                                $badge_bg = 'bg-warning text-dark';
                                                $lower_st = strtolower($st);
                                                if (strpos($lower_st, 'damage') !== false) {
                                                    $badge_bg = 'bg-danger text-white';
                                                } elseif (strpos($lower_st, 'waiting') !== false) {
                                                    $badge_bg = 'bg-info text-dark';
                                                }
                                            ?>
                                            <tr style="font-size: 11px;">
                                                <td>
                                                    <?php if (!empty($item['selfie_image'])): ?>
                                                        <img src="<?= $item['selfie_image']; ?>" width="45" height="45" style="object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" alt="Selfie">
                                                    <?php else: ?>
                                                        <span class="text-muted">No Image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($item['item_to_borrow']); ?></td>
                                                <td><?= htmlspecialchars($item['return_date']) . '<br><small>(' . htmlspecialchars($item['return_time']) . ')</small>'; ?></td>
                                                <td><span class="badge <?= $badge_bg; ?> px-2 py-1"><?= htmlspecialchars($st); ?></span></td>
                                                <td>
                                                    <?php if (strpos($lower_st, 'damage') !== false || strpos($lower_st, 'waiting') !== false): ?>
                                                        <a href="index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=<?= urlencode($raw_student_id); ?>&action=mark_replaced&req_id=<?= $item['id']; ?>" class="btn btn-warning btn-sm text-dark fw-bold px-2 py-1" style="font-size: 10px;">
                                                            <i class="fas fa-sync-alt me-1"></i> Mark Replaced
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=<?= urlencode($raw_student_id); ?>&action=clear&req_id=<?= $item['id']; ?>" class="btn btn-success btn-sm fw-semibold px-2 py-1 mb-1" style="font-size: 10px;">Mark Good / Clear</a>
                                                        <a href="index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=<?= urlencode($raw_student_id); ?>&action=damaged&req_id=<?= $item['id']; ?>" class="btn btn-danger btn-sm fw-semibold px-2 py-1" style="font-size: 10px;">Mark Damaged</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info py-2 small mb-3">Walang kasalukuyang aktibong hiniram na kagamitan ang estudyanteng ito.</div>
                        <?php endif; ?>

                        <a href="borrower.php?uid=<?= $borrower_data['id'] ?? 1; ?>&sid=<?= htmlspecialchars($borrower_data['student_id'] ?? $student_id); ?>" class="btn btn-success w-100 fw-bold mt-2">Proceed to Borrower Portal</a>
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
        const html5QrCode = new Html5Qrcode("reader");

        function playBeep() {
            const audio = document.getElementById('beepAudio');
            if (audio) {
                audio.play().catch(e => console.log("Audio play blocked"));
            }
        }

        document.getElementById('start-camera').addEventListener('click', function() {
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText, decodedResult) => {
                    playBeep();
                    setTimeout(() => {
                        window.location.href = "index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=" + encodeURIComponent(decodedText);
                    }, 300);
                },
                (errorMessage) => {}
            ).then(() => {
                document.getElementById('start-camera').style.display = 'none';
                document.getElementById('stop-camera').style.display = 'inline-block';
            }).catch((err) => {
                alert("Error starting camera: " + err);
            });
        });

        document.getElementById('stop-camera').addEventListener('click', function() {
            html5QrCode.stop().then(() => {
                document.getElementById('start-camera').style.display = 'inline-block';
                document.getElementById('stop-camera').style.display = 'none';
            }).catch((err) => {
                console.log("Failed to stop scanner.");
            });
        });

        document.getElementById('qr-image-input').addEventListener('change', e => {
            if (e.target.files.length == 0) return;
            const imageFile = e.target.files[0];
            
            html5QrCode.scanFile(imageFile, true)
            .then(decodedText => {
                playBeep();
                setTimeout(() => {
                    window.location.href = "index.php?uid=<?= $_GET['uid'] ?? 1; ?>&student_id=" + encodeURIComponent(decodedText);
                }, 300);
            })
            .catch(err => {
                alert("Error scanning QR image. Make sure the uploaded image is clear and contains a valid QR code.");
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
