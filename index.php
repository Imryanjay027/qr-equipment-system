<?php
// index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_connection.php');

// Ensure the custodian is logged in
$is_logged_in = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $is_logged_in = true;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
if (!$is_logged_in || ($role !== 'custodian' && $role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$fullname_custodian = $_SESSION['fullname'] ?? 'Custodian';

// Variables para sa Borrower details
$student_id = $_GET['student_id'] ?? '';
$student_name = "---";
$student_details = "Course & Year: ---";
$student_photo = "";
$borrowed_items = [];

if (!empty($student_id)) {
    // 1. Kunin ang impormasyon ng estudyante mula sa borrowers table
    $query = "SELECT fullname, course_year, selfie_image FROM borrowers WHERE student_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $student_name = $row['fullname'];
            $student_details = "Course & Year: " . ($row['course_year'] ?? '---');
            $student_photo = $row['selfie_image'] ?? '';
        } else {
            $student_name = "Student Not Found";
            $student_details = "Student ID: " . htmlspecialchars($student_id);
        }
        $stmt->close();
    }

    // 2. Kunin ang kasaysayan ng mga hiniram na gamit mula sa borrowed_equipment table
    $query_items = "SELECT item_to_borrow AS equipment_name, borrow_date, status FROM borrowed_equipment WHERE student_id = ?";
    $stmt_items = $conn->prepare($query_items);
    if ($stmt_items) {
        $stmt_items->bind_param("s", $student_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        while ($item = $items_result->fetch_assoc()) {
            $borrowed_items[] = $item;
        }
        $stmt_items->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MARY THE QUEEN COLLEGE OF QUEZON CITY - EQUIPMENT SCANNER</title>
    <!-- Html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; }
        
        .navbar { background-color: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 15px; font-weight: 600; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 10px; align-items: center; }
        .nav-links a { background-color: #2b4c7e; color: white; padding: 7px 14px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 500; transition: background 0.2s; }
        .nav-links a:hover { background-color: #3b629e; }
        .nav-links a.logout-btn { background-color: #d9534f; }
        .nav-links a.logout-btn:hover { background-color: #c9302c; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        .module-box { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 25px; margin-bottom: 25px; }
        .module-title { font-size: 15px; font-weight: 600; color: #1a365d; text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start; }
        @media (max-width: 768px) { .main-grid { grid-template-columns: 1fr; } }

        .options-container { display: flex; flex-direction: column; gap: 15px; }
        .option-box { border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; background: #fafafa; }
        .option-box label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: #444; }
        .option-box input[type="file"], .option-box input[type="text"] { font-size: 12px; width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; }

        #reader { width: 100%; border-radius: 6px; overflow: hidden; background: #000; min-height: 180px; margin-bottom: 8px; }
        .scan-btn { background: #1a365d; color: white; border: none; padding: 7px 15px; width: 100%; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .scan-btn:hover { background: #2b4c7e; }
        .submit-btn { background: #28a745; color: white; border: none; padding: 7px 15px; width: 100%; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 6px; }

        .result-container { border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; background: #fff; min-height: 330px; }
        .result-container h3 { font-size: 13px; font-weight: 600; color: #1a365d; text-align: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        
        .borrower-profile { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #edf2f7; }
        .borrower-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; background: #cbd5e1; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #555; flex-shrink: 0; cursor: pointer; transition: 0.2s; }
        .borrower-img:hover { opacity: 0.8; transform: scale(1.05); }
        .borrower-img img { width: 100%; height: 100%; object-fit: cover; }
        .borrower-info h4 { font-size: 14px; color: #1a365d; margin-bottom: 2px; font-weight: 600; }
        .borrower-info p { font-size: 11px; color: #555; margin-bottom: 2px; }

        .history-title { font-size: 12px; font-weight: 600; color: #333; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #edf2f7; }
        th { background-color: #1a365d; color: white; font-weight: 500; }
        .no-data { text-align: center; color: #888; padding: 20px; font-style: italic; }

        /* Badge Colors */
        .badge { padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 10px; display: inline-block; }
        .badge-cleared { background-color: #d4edda; color: #155724; }
        .badge-damaged { background-color: #f8d7da; color: #721c24; }
        .badge-replaced { background-color: #fff3cd; color: #856404; }

        /* Pinagandang Image Zoom Modal Styles */
        .img-modal { display: none; position: fixed; z-index: 9999; padding-top: 30px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.85); }
        .img-modal-content { margin: auto; display: block; width: 480px; max-width: 90%; height: auto; max-height: 80vh; object-fit: cover; border-radius: 10px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.8); border: 2px solid #ffffff; }
        .img-modal-close { position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<!-- Audio element para sa beep sound -->
<audio id="beepAudio" src="beep.mp3" preload="auto"></audio>

<div class="navbar">
    <h1>MARY THE QUEEN COLLEGE OF QUEZON CITY - EQUIPMENT SCANNER</h1>
    <div class="nav-links">
        <a href="custodian_dashboard.php">Dashboard</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Sigurado ka bang gusto mong mag-logout?');">Logout</a>
    </div>
</div>

<div class="container">
    <div class="module-box">
        <div class="module-title">QR-Track: Equipment Return & Verification</div>
        
        <div class="main-grid">
            <div class="options-container">
                <div class="option-box">
                    <label>Option 1: Upload QR Pass Image</label>
                    <input type="file" id="qr-input-file" accept="image/*">
                </div>

                <div class="option-box">
                    <label>Option 2: Live Webcam Scan</label>
                    <div id="reader"></div>
                    <button type="button" class="scan-btn" id="start-camera-btn">Start Scanning</button>
                </div>

                <div class="option-box">
                    <label>O kaya ay i-type ang Student ID:</label>
                    <form method="GET" action="index.php" onsubmit="playBeepAndSubmit(event, this)">
                        <input type="text" name="student_id" placeholder="Enter Student ID..." value="<?php echo htmlspecialchars($student_id); ?>">
                        <button type="submit" class="submit-btn">Find</button>
                    </form>
                </div>
            </div>

            <div class="result-container">
                <h3>Borrower Verification Result</h3>
                
                <div class="borrower-profile">
                    <div class="borrower-img" onclick="openImageModal(this.querySelector('img').src)">
                        <?php 
                            $photo_path = trim($student_photo);
                            if (empty($photo_path)) {
                                $photo_path = "uploads/default.png";
                            } elseif (strpos($photo_path, 'uploads/') === false) {
                                $photo_path = "uploads/" . $photo_path;
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Student Photo" onerror="this.onerror=null; this.src='uploads/default.png';">
                    </div>
                    <div class="borrower-info">
                        <h4><?php echo htmlspecialchars($student_name); ?></h4>
                        <p>Student ID: <?php echo htmlspecialchars($student_id !== '' ? $student_id : '---'); ?></p>
                        <p><?php echo htmlspecialchars($student_details); ?></p>
                    </div>
                </div>

                <div class="history-title">Borrowed Items History:</div>
                <table>
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Return Due / Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($borrowed_items)): ?>
                            <?php foreach ($borrowed_items as $item): ?>
                                <?php 
                                    $status = trim($item['status'] ?? 'Pending');
                                    $badge_class = 'badge-replaced';
                                    $lower_status = strtolower($status);
                                    
                                    if ($lower_status == 'cleared' || $lower_status == 'good') {
                                        $badge_class = 'badge-cleared';
                                    } elseif ($lower_status == 'damaged') {
                                        $badge_class = 'badge-damaged';
                                    } elseif ($lower_status == 'replaced') {
                                        $badge_class = 'badge-replaced';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['equipment_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['borrow_date']); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-data">
                                    <?php echo empty($student_id) ? "No QR code scanned yet." : "No records found for this student."; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Image Zoom Modal Structure -->
<div id="imageModal" class="img-modal" onclick="closeImageModal()">
    <span class="img-modal-close">&times;</span>
    <img class="img-modal-content" id="modalImage">
</div>

<script>
    const beepAudio = document.getElementById('beepAudio');

    function playBeepAndRedirect(url) {
        beepAudio.play().catch(e => console.log("Audio play blocked by browser:", e));
        setTimeout(() => {
            window.location.href = url;
        }, 300);
    }

    function playBeepAndSubmit(event, form) {
        event.preventDefault();
        beepAudio.play().catch(e => console.log("Audio play blocked:", e));
        setTimeout(() => {
            form.submit();
        }, 300);
    }

    // Modal functions para sa pinalaking pag-zoom ng larawan
    function openImageModal(imgSrc) {
        if (!imgSrc || imgSrc.includes('default.png')) return;
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        modal.style.display = "block";
        modalImg.src = imgSrc;
    }

    function closeImageModal() {
        document.getElementById("imageModal").style.display = "none";
    }

    let html5QrcodeScanner = null;

    document.getElementById('start-camera-btn').addEventListener('click', function() {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { fps: 10, qrbox: 200 }
            );
            
            html5QrcodeScanner.render((decodedText, decodedResult) => {
                html5QrcodeScanner.clear();
                playBeepAndRedirect("index.php?student_id=" + encodeURIComponent(decodedText));
            }, (errorMessage) => {});
            
            this.innerText = "Stop Scanning";
            this.style.backgroundColor = "#d9534f";
        }
    });

    const fileInput = document.getElementById('qr-input-file');
    fileInput.addEventListener('change', e => {
        if (e.target.files.length == 0) return;
        const imageFile = e.target.files[0];
        const html5QrCode = new Html5Qrcode("reader");
        
        html5QrCode.scanFile(imageFile, true)
        .then(decodedText => {
            playBeepAndRedirect("index.php?student_id=" + encodeURIComponent(decodedText));
        })
        .catch(err => {
            alert("Unable to read QR code from image. Please try another one.");
        });
    });
</script>

</body>
</html>