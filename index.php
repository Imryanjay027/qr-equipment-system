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
$borrowed_items = [];
if (isset($_GET['student_id'])) {
    $raw_student_id = trim($_GET['student_id']);
    
    // Parse the ID if it contains formatting like "MQCQC-ID: 123456 | Name: ..."
    $student_id = $raw_student_id;
    if (strpos($raw_student_id, 'MQCQC-ID:') !== false) {
        preg_match('/MQCQC-ID:\s*([^|]+)/i', $raw_student_id, $matches);
        if (isset($matches[1])) {
            $student_id = trim($matches[1]);
        }
    }

    // Fetch borrower details including selfie_image and course_year
    $stmt = $conn->prepare("SELECT * FROM borrowers WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $borrower_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // Fetch borrowed equipment details (item, dates, status)
    $stmt_eq = $conn->prepare("SELECT * FROM borrowed_equipment WHERE student_id = ? ORDER BY id DESC");
    if ($stmt_eq) {
        $stmt_eq->bind_param("s", $student_id);
        $stmt_eq->execute();
        $borrowed_items = $stmt_eq->get_result();
        $stmt_eq->close();
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
        .scanner-container { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; max-width: 750px; margin: 40px auto; }
        #reader { width: 100%; border-radius: 8px; overflow: hidden; background: #000; min-height: 250px; margin-bottom: 15px; }
        .student-profile-img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #1a365d; background: #ddd; }
        .badge-status { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 11px; }
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
            <h4 class="fw-bold text-dark mb-3 text-center"><i class="fas fa-qrcode text-primary me-2"></i>Scan Student QR Code for Return & Monitoring</h4>
            <p class="text-muted small text-center mb-4">Use your camera or upload a QR image to verify student details and item conditions.</p>
            
            <div id="reader"></div>
            <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold mb-3" id="start-camera-btn">Start Camera</button>

            <hr class="my-4">

            <div class="mb-3">
                <label class="form-label small fw-bold">Or Upload QR Image File</label>
                <input type="file" id="qr-input-file" class="form-control form-control-sm" accept="image/*">
            </div>

            <?php if (isset($_GET['student_id'])): ?>
                <div class="card mt-4 border-primary shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="fas fa-user-graduate me-2"></i> Student Verification & Equipment Status
                    </div>
                    <div class="card-body">
                        <?php if ($borrower_info): ?>
                            <?php 
                                $photo = trim($borrower_info['selfie_image'] ?? '');
                                if (empty($photo)) {
                                    $photo = 'uploads/default.png';
                                } elseif (strpos($photo, 'uploads/') === false) {
                                    $photo = 'uploads/' . $photo;
                                }
                            ?>
                            <div class="row align-items-center mb-4">
                                <div class="col-auto text-center">
                                    <img src="<?= htmlspecialchars($photo); ?>" alt="Student Photo" class="student-profile-img shadow-sm" onerror="this.src='uploads/default.png';">
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($borrower_info['fullname'] ?? '---'); ?></h5>
                                    <p class="mb-1 text-muted small"><strong>Student ID:</strong> <?= htmlspecialchars($borrower_info['student_id'] ?? '---'); ?></p>
                                    <p class="mb-0 text-muted small"><strong>Course & Year:</strong> <?= htmlspecialchars($borrower_info['course_year'] ?? '---'); ?></p>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Borrowed Items & Condition Monitoring</h6>
                            <?php if ($borrowed_items && $borrowed_items->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Return Due</th>
                                                <th>Return Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($item = $borrowed_items->fetch_assoc()): ?>
                                                <?php 
                                                    $st = trim($item['status'] ?? 'Pending');
                                                    $badge_bg = 'bg-warning text-dark';
                                                    $lower_st = strtolower($st);
                                                    if (strpos($lower_st, 'cleared') !== false || strpos($lower_st, 'good') !== false) {
                                                        $badge_bg = 'bg-success';
                                                    } elseif (strpos($lower_st, 'damage') !== false) {
                                                        $badge_bg = 'bg-danger';
                                                    } elseif (strpos($lower_st, 'replaced') !== false) {
                                                        $badge_bg = 'bg-info text-dark';
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= htmlspecialchars($item['item_to_borrow']); ?></td>
                                                    <td><?= htmlspecialchars($item['return_due'] ?? ($item['borrow_date'] ?? '---')); ?></td>
                                                    <td><?= htmlspecialchars($item['return_date'] ?? 'Not yet returned'); ?></td>
                                                    <td><span class="badge badge-status <?= $badge_bg; ?>"><?= htmlspecialchars($st); ?></span></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0 small">Walang nahanap na hiniram na kagamitan para sa estudyanteng ito.</div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-danger mb-0 small">
                                <i class="fas fa-exclamation-triangle me-1"></i> No borrower record found for this Student ID.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const beepAudio = document.getElementById('beepAudio');
        const currentUid = "<?= htmlspecialchars($user_id); ?>";

        function handleSuccessfulScan(decodedText) {
            if (beepAudio) {
                beepAudio.currentTime = 0;
                beepAudio.play().catch(error => {
                    console.log("Audio autoplay prevented by browser:", error);
                });
            }

            setTimeout(() => {
                window.location.href = "index.php?uid=" + currentUid + "&student_id=" + encodeURIComponent(decodedText);
            }, 700);
        }

        let html5QrcodeScanner = null;
        const startCamBtn = document.getElementById('start-camera-btn');
        if(startCamBtn) {
            startCamBtn.addEventListener('click', function() {
                if (!html5QrcodeScanner) {
                    if (beepAudio) {
                        beepAudio.play().then(() => { beepAudio.pause(); beepAudio.currentTime = 0; }).catch(e => {});
                    }

                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                    html5QrcodeScanner.render((decodedText) => {
                        html5QrcodeScanner.clear();
                        handleSuccessfulScan(decodedText);
                    }, (err) => {});
                    this.innerText = "Stop Camera";
                    this.className = "btn btn-danger btn-sm w-100 fw-semibold mb-3";
                }
            });
        }

        const fileInput = document.getElementById('qr-input-file');
        if (fileInput) {
            fileInput.addEventListener('change', e => {
                if (e.target.files.length === 0) return;
                const imageFile = e.target.files[0];
                
                if (beepAudio) {
                    beepAudio.play().then(() => { beepAudio.pause(); beepAudio.currentTime = 0; }).catch(e => {});
                }

                const html5QrCode = new Html5Qrcode("reader");
                html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    handleSuccessfulScan(decodedText);
                })
                .catch(err => {
                    alert("Hindi mabasa ang QR code mula sa larawan. Subukan ang ibang malinaw na imahe.");
                    console.error("QR Scan Error: ", err);
                });
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
