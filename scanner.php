<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qr_equipment_db');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$scanned_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$borrower = null;

if (!empty($scanned_id)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? OR fullname LIKE ?");
    $searchTerm = "%" . $scanned_id . "%";
    $stmt->bind_param("ss", $scanned_id, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $borrower = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Scanner - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background-color: #0b1f36; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; margin: 0; }
        .header-btns a { text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 14px; margin-left: 10px; color: white; }
        .btn-dashboard { background-color: #007bff; }
        .btn-logout { background-color: #dc3545; }
        .main-container { max-width: 1200px; margin: 30px auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; }
        #reader { width: 100%; border-radius: 8px; overflow: hidden; background-color: #000; display: none; margin-bottom: 15px; }
        #reader video { object-fit: cover !important; }
        .borrower-card { display: flex; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .borrower-avatar { width: 50px; height: 50px; background-color: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; margin-right: 15px; flex-shrink: 0; }
        .borrower-info h4 { margin: 0 0 5px 0; font-size: 16px; color: #222; }
        .borrower-info p { margin: 0; font-size: 13px; color: #666; line-height: 1.4; }
        table.history-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        table.history-table th { background-color: #222; color: white; text-align: left; padding: 8px 10px; }
        table.history-table td { padding: 10px; border-bottom: 1px solid #eee; color: #444; text-align: center; }
        .no-records { text-align: center; padding: 20px; color: #777; font-style: italic; font-size: 13px; }
    </style>
</head>
<body>

    <!-- Audio element para sa beep.mp3 file -->
    <audio id="beep-audio" src="beep.mp3" preload="auto"></audio>

<div class="header">
    <h1>THE QUEEN COLLEGE OF QUEZON CITY - EQUIPMENT SCANNER</h1>
    <div class="header-btns">
        <a href="#" class="btn-dashboard">Dashboard</a>
        <a href="#" class="btn-logout">Logout</a>
    </div>
</div>

<div class="main-container">
    <div class="text-center fw-bold mb-4" style="font-size: 22px; color: #222;">QR-Track: Equipment Return & Verification</div>

    <div class="row">
        <!-- Kaliwang Panel: Scanner & Upload -->
        <div class="col-md-6 mb-4">
            <div class="p-3 border rounded bg-white">
                <h5 class="fw-bold mb-3 text-success"><i class="fas fa-qrcode me-2"></i>Custodian QR Scanner</h5>
                
                <div class="d-grid gap-2 mb-3">
                    <button id="toggle-scanner" class="btn btn-success fw-bold" onclick="startCameraScanner()">
                        <i class="fas fa-camera me-2"></i>Buksan ang Camera Scanner
                    </button>
                </div>

                <div id="reader"></div>

                <div class="mb-3">
                    <label for="qr-file-input" class="btn btn-outline-success w-100 fw-bold">
                        <i class="fas fa-upload me-2"></i>O kaya ay Mag-upload ng QR Code Image File
                    </label>
                    <input type="file" id="qr-file-input" accept="image/*" class="d-none" onchange="scanQrFromFile(event)">
                </div>

                <div class="d-grid gap-2 mb-2">
                    <a href="scanner.php?student_id=2" class="btn btn-warning text-dark fw-bold" onclick="playBeep()">
                        <i class="fas fa-bolt me-2"></i>⚡ Simulate Scan (Test ID: 2)
                    </a>
                </div>
                
                <div id="scan-result" class="mt-3 alert alert-info d-none" role="alert">
                    <span id="result-text">Scanning...</span>
                </div>
            </div>
        </div>

        <!-- Kanang Panel: Verification Result -->
        <div class="col-md-6">
            <div class="p-3 border rounded bg-white">
                <h5 class="fw-bold mb-3 text-dark">Borrower Verification Result</h5>

                <?php if ($borrower): ?>
                    <div class="borrower-card">
                        <div class="borrower-avatar">👤</div>
                        <div class="borrower-info">
                            <h4><?php echo htmlspecialchars($borrower['fullname']); ?></h4>
                            <p><b>User ID / QR Info:</b> ID: <?php echo htmlspecialchars($borrower['id']); ?> | Role: <?php echo htmlspecialchars($borrower['role']); ?></p>
                            <p><b>Email:</b> <?php echo htmlspecialchars($borrower['email']); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="borrower-card">
                        <div class="borrower-avatar">🛡️</div>
                        <div class="borrower-info">
                            <h4>System Custodian</h4>
                            <p><b>User ID / QR Info:</b> MQCQC-ID: CUST001 | Name: System Custodian</p>
                            <p><b>Course & Year:</b> BSIT 3rd Year</p>
                        </div>
                    </div>
                <?php endif; ?>

                <h5 class="fw-bold mt-4 mb-2 text-dark" style="font-size: 16px;">Borrowed Items History:</h5>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Return Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="no-records">Wala pang nakatalagang hiniram na kagamitan para sa ID na ito.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // I-unlock ang audio sa unang click ng user para mapagana sa mobile/browser
    document.addEventListener('click', function() {
        const audio = document.getElementById('beep-audio');
        if (audio) {
            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
            }).catch(e => {});
        }
    }, { once: true });

    // Function para patugtugin ang beep.mp3
    function playBeep(callback) {
        const audio = document.getElementById('beep-audio');
        if (audio) {
            audio.currentTime = 0;
            audio.play().then(() => {
                if (callback) {
                    setTimeout(callback, 200);
                }
            }).catch(err => {
                if (callback) callback();
            });
        } else {
            if (callback) callback();
        }
    }

    let html5QrCode = null;

    function startCameraScanner() {
        const readerDiv = document.getElementById('reader');
        readerDiv.style.display = 'block';

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const qrConfig = {
            fps: 30,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            videoConstraints: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: "environment"
            }
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            qrConfig, 
            (decodedText) => {
                html5QrCode.stop().then(() => {
                    document.getElementById('scan-result').classList.remove('d-none');
                    document.getElementById('result-text').innerText = "Scanned: " + decodedText;
                    playBeep(() => {
                        window.location.href = "scanner.php?student_id=" + encodeURIComponent(decodedText);
                    });
                }).catch(() => {
                    playBeep(() => {
                        window.location.href = "scanner.php?student_id=" + encodeURIComponent(decodedText);
                    });
                });
            },
            (error) => {}
        ).catch(err => {
            html5QrCode.start(
                { facingMode: "user" },
                qrConfig,
                (decodedText) => {
                    html5QrCode.stop().then(() => {
                        playBeep(() => {
                            window.location.href = "scanner.php?student_id=" + encodeURIComponent(decodedText);
                        });
                    });
                },
                (error) => {}
            ).catch(e => {
                alert("Hindi ma-start ang camera: " + e);
            });
        });
    }

    function scanQrFromFile(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        html5QrCode.scanFile(file, true)
            .then(decodedText => {
                document.getElementById('scan-result').classList.remove('d-none');
                document.getElementById('result-text').innerText = "Scanned File: " + decodedText;
                playBeep(() => {
                    window.location.href = "scanner.php?student_id=" + encodeURIComponent(decodedText);
                });
            })
            .catch(err => {
                alert("Hindi mabasa ang QR code mula sa in-upload na larawan.");
                console.error(err);
            });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
