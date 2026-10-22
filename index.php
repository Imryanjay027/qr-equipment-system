<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQL_HOST');
if (!$host) {
    $host = getenv('DB_HOST');
    if (!$host) {
        $host = 'mysql';
    }
}

$port = getenv('MYSQL_PORT');
if (!$port) {
    $port = getenv('DB_PORT');
    if (!$port) {
        $port = 3306;
    }
}

$username = getenv('MYSQL_USER');
if (!$username) {
    $username = getenv('DB_USER');
    if (!$username) {
        $username = 'root';
    }
}

$password = getenv('MYSQL_PASSWORD');
if ($password === false) {
    $password = getenv('DB_PASSWORD');
    if ($password === false) {
        $password = '';
    }
}

$database = getenv('MYSQL_DATABASE');
if (!$database) {
    $database = getenv('DB_NAME');
    if (!$database) {
        $database = 'qrcode';
    }
}

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}
if ($conn->connect_error) {
    $conn = @new mysqli('127.0.0.1', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("<div class='alert alert-danger m-4'><strong>Database Connection Failed:</strong> Hindi makakonekta sa database server.</div>");
}

$action_param = isset($_GET['action']) ? $_GET['action'] : '';
$req_id_param = isset($_GET['req_id']) ? $_GET['req_id'] : '';
$student_id_param = isset($_GET['student_id']) ? $_GET['student_id'] : '';

if ($action_param AND $req_id_param AND $student_id_param) {
    $req_id = intval($req_id_param);
    $action = $action_param;
    $redirect_sid = $student_id_param;

    $new_status = 'Good';
    if ($action == 'good') {
        $new_status = 'Good';
    } elseif ($action == 'damaged') {
        $new_status = 'Damaged';
    } elseif ($action == 'replaced') {
        $new_status = 'Replaced';
    }

    $stmt = $conn->prepare("UPDATE borrow_requests SET status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $new_status, $req_id);
        $stmt->execute();
        $stmt->close();
    }

    $get_uid = isset($_GET['uid']) ? $_GET['uid'] : 1;
    header("Location: index.php?uid=" . $get_uid . "&student_id=" . urlencode($redirect_sid));
    exit();
}

$raw_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$decoded_student_id = urldecode($raw_student_id);
$student_id = trim($decoded_student_id);
$borrower_data = null;
$borrowed_items = array();

if ($student_id !== '') {
    $clean_sid = str_replace('MQCQC-ID:', '', $student_id);
    $clean_sid = trim(str_replace(' ', '', $clean_sid));
    $pipe_char = chr(124);
    $pos = strpos($clean_sid, $pipe_char);
    if ($pos !== false) {
        $parts = explode($pipe_char, $clean_sid);
        $clean_sid = trim($parts[0]);
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $clean_sid, $clean_sid);
        $stmt->execute();
        $res = $stmt->get_result();
        $borrower_data = $res->fetch_assoc();
        $stmt->close();
    }

    if (!$borrower_data) {
        $stmt2 = $conn->prepare("SELECT * FROM borrowers WHERE student_id = ?");
        if ($stmt2) {
            $stmt2->bind_param("s", $clean_sid);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $borrower_data = $res2->fetch_assoc();
            $stmt2->close();
        }
    }

    $search_name = isset($borrower_data['full_name']) ? $borrower_data['full_name'] : (isset($borrower_data['fullname']) ? $borrower_data['fullname'] : '');

    // Dito natin sinasala: ipapakita lamang ang mga item na HINDI pa Good at HINDI pa Replaced (hal. Damaged o Pending)
    $sql = "SELECT * FROM borrow_requests WHERE (student_id = ? OR full_name = ?) AND LOWER(status) NOT LIKE '%good%' AND LOWER(status) NOT LIKE '%replaced%' AND LOWER(status) NOT LIKE '%cleared%' ORDER BY id DESC";
    $stmt_req = $conn->prepare($sql);
    if ($stmt_req) {
        $stmt_req->bind_param("ss", $clean_sid, $search_name);
        $stmt_req->execute();
        $res_req = $stmt_req->get_result();
        while($row = $res_req->fetch_assoc()) {
            $borrowed_items[] = $row;
        }
        $stmt_req->close();
    }
}

$portal_uid = isset($_GET['uid']) ? $_GET['uid'] : 1;
$full_name_display = 'N/A';
if ($borrower_data) {
    if (isset($borrower_data['full_name'])) {
        $full_name_display = $borrower_data['full_name'];
    } elseif (isset($borrower_data['fullname'])) {
        $full_name_display = $borrower_data['fullname'];
    }
}
$student_id_display = $borrower_data ? (isset($borrower_data['student_id']) ? $borrower_data['student_id'] : $student_id) : $student_id;
$course_year_display = $borrower_data ? (isset($borrower_data['course_year']) ? $borrower_data['course_year'] : (isset($borrower_data['grade_section']) ? $borrower_data['grade_section'] : 'N/A')) : 'N/A';
$borrower_id_val = $borrower_data ? (isset($borrower_data['id']) ? $borrower_data['id'] : 1) : 1;
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
        .main-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #ffffff; max-width: 950px; margin: 30px auto; padding: 25px; }
        .top-navbar { background-color: #217835; color: #ffffff; padding: 12px 20px; font-weight: bold; font-size: 0.9rem; }
        #reader { width: 100%; max-width: 400px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 2px solid #e9ecef; }
    </style>
</head>
<body>

    <audio id="beepAudio" src="beep.mp3" preload="auto"></audio>

    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MQCQC - QR SCANNER PORTAL</div>
        <div>
            <a href="custodian_dashboard.php?uid=<?php echo $portal_uid; ?>" class="btn btn-light btn-sm text-success fw-bold">Custodian Dashboard</a>
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

            <div class="mb-3 p-3 bg-light rounded border">
                <label for="qr-image-input" class="form-label fw-bold text-secondary small mb-1"><i class="fas fa-upload me-1"></i> Or Upload QR Image File</label>
                <input class="form-control form-control-sm" type="file" id="qr-image-input" accept="image/*">
            </div>

            <div class="card p-3 border-0 bg-light">
                <h5 class="fw-bold text-secondary mb-3"><i class="fas fa-user-shield me-2"></i>Student Verification & Condition Monitoring</h5>
                
                <?php if ($raw_student_id !== ''): ?>
                    <?php if ($borrower_data): ?>
                        <div class="alert alert-success mb-3">
                            <h6 class="fw-bold mb-1">Student Found!</h6>
                            <p class="mb-1"><strong>Borrower Name:</strong> <?php echo htmlspecialchars($full_name_display); ?></p>
                            <p class="mb-1"><strong>Student ID:</strong> <?php echo htmlspecialchars($student_id_display); ?></p>
                            <p class="mb-0"><strong>Course / Year:</strong> <?php echo htmlspecialchars($course_year_display); ?></p>
                        </div>

                        <h6 class="fw-bold text-dark mt-3 mb-2"><i class="fas fa-laptop me-1 text-success"></i> Active Borrowed Equipment Records (Pending / Damaged)</h6>
                        
                        <?php if (!empty($borrowed_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center bg-white">
                                    <thead class="table-success">
                                        <tr style="font-size: 11px;">
                                            <th>Selfie Picture</th>
                                            <th>Borrower Name</th>
                                            <th>Student ID</th>
                                            <th>Course / Year</th>
                                            <th>Equipment Name</th>
                                            <th>Condition Status</th>
                                            <th>Update Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowed_items as $item): ?>
                                            <?php 
                                                $st = isset($item['status']) ? trim($item['status']) : 'Good';
                                                $lower_st = strtolower($st);
                                                
                                                $badge_class = 'bg-warning text-dark';
                                                $display_status = 'Pending / Active';
                                                
                                                if (strpos($lower_st, 'damage') !== false || strpos($lower_st, 'sira') !== false) {
                                                    $badge_class = 'bg-danger text-white';
                                                    $display_status = 'Damaged';
                                                } else {
                                                    $display_status = $st;
                                                }

                                                $item_id = isset($item['id']) ? $item['id'] : 0;
                                                $item_selfie = isset($item['selfie_image']) ? $item['selfie_image'] : '';
                                                $item_borrower_name = isset($item['full_name']) ? $item['full_name'] : $full_name_display;
                                                $item_sid = isset($item['student_id']) ? $item['student_id'] : $student_id_display;
                                                $item_course = isset($item['course_year']) ? $item['course_year'] : (isset($item['grade_section']) ? $item['grade_section'] : $course_year_display);
                                                $item_name = isset($item['item_to_borrow']) ? $item['item_to_borrow'] : '';
                                            ?>
                                            <tr style="font-size: 11px;">
                                                <td>
                                                    <?php if ($item_selfie !== ''): ?>
                                                        <img src="<?php echo $item_selfie; ?>" width="45" height="45" style="object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" alt="Selfie">
                                                    <?php else: ?>
                                                        <span class="text-muted">No Image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($item_borrower_name); ?></td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($item_sid); ?></td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($item_course); ?></td>
                                                <td class="fw-bold text-success"><?php echo htmlspecialchars($item_name); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $badge_class; ?> px-3 py-2" style="font-size: 11px;"><?php echo htmlspecialchars($display_status); ?></span>
                                                </td>
                                                <td>
                                                    <a href="index.php?uid=<?php echo $portal_uid; ?>&student_id=<?php echo urlencode($raw_student_id); ?>&action=good&req_id=<?php echo $item_id; ?>" class="btn btn-success btn-sm px-2 py-1 mb-1" style="font-size: 9px;" title="Mark as Good">Good</a>
                                                    <a href="index.php?uid=<?php echo $portal_uid; ?>&student_id=<?php echo urlencode($raw_student_id); ?>&action=damaged&req_id=<?php echo $item_id; ?>" class="btn btn-danger btn-sm px-2 py-1 mb-1" style="font-size: 9px;" title="Mark as Damaged">Damaged</a>
                                                    <a href="index.php?uid=<?php echo $portal_uid; ?>&student_id=<?php echo urlencode($raw_student_id); ?>&action=replaced&req_id=<?php echo $item_id; ?>" class="btn btn-warning btn-sm text-dark px-2 py-1" style="font-size: 9px;" title="Mark as Replaced">Replaced</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success py-2 small mb-3">Walang nakitang active o sirang kagamitan para sa estudyanteng ito (Lahat ay maayos o naipalit na).</div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> No borrower record found for Student ID: <strong><?php echo htmlspecialchars($student_id); ?></strong>
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
                        window.location.href = "index.php?uid=<?php echo $portal_uid; ?>&student_id=" + encodeURIComponent(decodedText);
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
                console.log("Failed to stop scanner.", err);
            });
        });

        document.getElementById('qr-image-input').addEventListener('change', e => {
            if (e.target.files.length == 0) return;
            const imageFile = e.target.files[0];
            
            html5QrCode.scanFile(imageFile, true)
            .then(decodedText => {
                playBeep();
                setTimeout(() => {
                    window.location.href = "index.php?uid=<?php echo $portal_uid; ?>&student_id=" + encodeURIComponent(decodedText);
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
