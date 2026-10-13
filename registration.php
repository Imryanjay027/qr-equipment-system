<?php
// registration.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect("localhost", "root", "", "qrcode");
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

$get_student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id     = mysqli_real_escape_string($conn, $_POST['student_id']);
    $fullname       = mysqli_real_escape_string($conn, $_POST['fullname']);
    $course_year    = mysqli_real_escape_string($conn, $_POST['course_year']);
    $item_to_borrow = mysqli_real_escape_string($conn, $_POST['item_to_borrow']);
    $return_date    = mysqli_real_escape_string($conn, $_POST['return_date']);
    $return_time    = mysqli_real_escape_string($conn, $_POST['return_time']);
    
    $selfie_data    = $_POST['selfie_image'] ?? '';
    $filename       = '';

    if (!empty($selfie_data)) {
        $image_parts = explode(";base64,", $selfie_data);
        if (count($image_parts) == 2) {
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'jpeg';
            $image_base64 = base64_decode($image_parts[1]);
            
            $filename = 'selfie_' . $student_id . '_' . time() . '.' . $image_type;
            
            $upload_folder = 'uploads/';
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }
            file_put_contents($upload_folder . $filename, $image_base64);
        }
    }

    date_default_timezone_set('Asia/Manila');
    $borrow_date    = date('Y-m-d H:i:s');
    $status         = 'Pending';

    // 1. Safe query check for users table
    $user_check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$student_id'");
    if ($user_check && mysqli_num_rows($user_check) > 0) {
        mysqli_query($conn, "UPDATE users SET fullname = '$fullname', course_year = '$course_year' WHERE username = '$student_id'");
    } else {
        mysqli_query($conn, "INSERT INTO users (username, fullname, course_year) VALUES ('$student_id', '$fullname', '$course_year')");
    }

    // 2. Save to borrowers table
    $query_borrower = "INSERT INTO borrowers (student_id, fullname, course_year, item_to_borrow, return_date, return_time, status, selfie_image) VALUES ('$student_id', '$fullname', '$course_year', '$item_to_borrow', '$return_date', '$return_time', '$status', '$filename')";
    if (!mysqli_query($conn, $query_borrower)) {
        die("Error on borrowers query: " . mysqli_error($conn));
    }

    // 3. Save to borrowed_equipment table for custodian dashboard reflection
    $query_equipment = "INSERT INTO borrowed_equipment (student_id, equipment_name, item_to_borrow, borrow_date, return_date, return_time, status) 
                        VALUES ('$student_id', '$item_to_borrow', '$item_to_borrow', '$borrow_date', '$return_date', '$return_time', '$status')";
    
    if (!mysqli_query($conn, $query_equipment)) {
        die("Error on borrowed_equipment query: " . mysqli_error($conn));
    }

    echo "<script>window.location.href = 'borrower.php?student_id=" . urlencode($student_id) . "&tab=active';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Borrower Registration Form - Mary The Queen College</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding-bottom: 40px; }
        .top-header { background: #28a745; color: white; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 10px; }
        .top-header-title { font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .header-btns { display: flex; gap: 10px; align-items: center; }
        .btn-dashboard { background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-dashboard:hover { background: #0056b3; }
        .btn-back-login { background: white; color: #28a745; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-back-login:hover { background: #f8f9fa; }
        
        .container { max-width: 600px; width: 100%; margin: 30px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .school-logo { width: 85px; height: 85px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #28a745; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #1a365d; margin: 5px 0 3px 0; font-size: 22px; }
        .system-subtitle { font-size: 11px; color: #555; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px; line-height: 1.3; }
        .sub-instruction { text-align: left; color: #666; font-size: 13px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 5px; }
        input[type="text"], input[type="date"], input[type="time"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        input:focus { border-color: #28a745; outline: none; }
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
        
        .webcam-box { text-align: center; margin-bottom: 15px; background: #f8f9fa; padding: 10px; border: 1px dashed #ccc; border-radius: 4px; }
        #video, #canvas, #snapshot-preview { width: 100%; max-width: 320px; height: 240px; border-radius: 4px; object-fit: cover; background: #000; }
        .btn-capture { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 8px; width: 100%; max-width: 320px; }
        .btn-capture:hover { background: #0056b3; }
        .btn-submit { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 15px; margin-top: 15px; }
        .btn-submit:hover { background: #218838; }

        /* Modal Styles for Terms and Conditions */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 10px; }
        .modal-content { background: #fff; width: 100%; max-width: 500px; padding: 25px; border-radius: 8px; text-align: left; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-content h3 { color: #1a365d; margin-top: 0; margin-bottom: 10px; font-size: 18px; text-align: center; }
        .terms-body { font-size: 12px; color: #444; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background: #f9f9f9; line-height: 1.5; }
        .checkbox-group { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 15px; font-size: 12px; color: #333; }
        .modal-btns { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; }
        .btn-agree { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; }
        .btn-agree:disabled { background: #cbd5e1; cursor: not-allowed; }

        /* Mobile Responsive Media Queries */
        @media (max-width: 768px) {
            body { padding: 0; }
            .container { margin: 10px auto; padding: 15px; width: 95% !important; box-shadow: none; border-radius: 0; }
            .top-header { padding: 10px; flex-direction: column; align-items: flex-start; gap: 8px; }
            .header-btns { width: 100%; justify-content: space-between; }
            .row { flex-direction: column; gap: 0; }
            .col { width: 100%; }
            #video, #canvas, #snapshot-preview { width: 100% !important; max-width: 100% !important; height: 200px; }
            .btn-capture { width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="top-header">
        <span class="top-header-title">MARY THE QUEEN COLLEGE - BORROWER PORTAL</span>
        <div class="header-btns">
            <a href="<?php echo !empty($get_student_id) ? 'borrower.php?student_id=' . htmlspecialchars($get_student_id) . '&tab=active' : 'borrower.php'; ?>" id="topDashboardBtn" class="btn-dashboard">Back to your Dashboard</a>
            <a href="login.php" class="btn-back-login">Log in</a>
        </div>
    </div>

    <div class="container">
        <img src="logo.jpg" alt="School Logo" class="school-logo" onerror="this.style.display='none';">
        <h2>Student Borrower Registration Form</h2>
        <div class="system-subtitle">QR - BASED SCHOOL EQUIPMENT BORROWING, RETURN, AND CONDITION MONITORING SYSTEM</div>
        
        <div class="sub-instruction">
            Please complete the registration form below, take a live selfie, and submit to borrow an item.
        </div>

        <form action="" method="POST" id="registrationForm" onsubmit="return handleFormSubmit(event);">
            <div class="form-group">
                <label>Live Selfie Verification:</label>
                <div class="webcam-box">
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas" style="display:none;"></canvas>
                    <img id="snapshot-preview" style="display:none;" alt="Live Selfie Preview">
                    <br>
                    <button type="button" id="captureBtn" class="btn-capture" onclick="takeSnapshot()">Capture Selfie</button>
                </div>
                <input type="hidden" name="selfie_image" id="selfie_image" required>
            </div>

            <div class="form-group">
                <label>Student ID Number:</label>
                <input type="text" name="student_id" id="student_id_input" value="<?php echo htmlspecialchars($get_student_id); ?>" placeholder="e.g. 4561239" required>
            </div>

            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="fullname" placeholder="First Name and Last Name" required>
            </div>

            <div class="form-group">
                <label>Course & Year:</label>
                <input type="text" name="course_year" placeholder="e.g. BSIT - 3rd Year" required>
            </div>

            <div class="form-group">
                <label>Item to Borrow:</label>
                <input type="text" name="item_to_borrow" placeholder="e.g. Projector, Multimeter" required>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Return Date:</label>
                    <input type="date" name="return_date" required>
                </div>
                <div class="col form-group">
                    <label>Return Time:</label>
                    <input type="time" name="return_time" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Submit Registration</button>
        </form>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Terms and Conditions</h3>
            <div class="terms-body">
                <p><b>MARY THE QUEEN COLLEGE OF QUEZON CITY</b><br>Property Custodian Office - Equipment Borrowing Policy</p>
                <br>
                <p>By submitting this registration form, you (the borrower) agree to the following policies and guidelines:</p>
                <ul>
                    <li><b>Equipment Responsibility:</b> It is the student's responsibility to handle, secure, and maintain the borrowed equipment in proper condition while in their custody.</li>
                    <li><b>Return Schedule:</b> Equipment must be returned on or before the scheduled return date and time. Delays or failure to return on time may incur penalties or account clearance flagging.</li>
                    <li><b>Damage and Loss:</b> If the equipment is damaged, compromised, or lost due to negligence, the borrower is obliged to provide an appropriate replacement or settle repair fees as assessed by the Property Custodian Office.</li>
                    <li><b>Live Verification:</b> The captured live selfie serves as official visual verification of your identity for this borrowing transaction.</li>
                </ul>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="agreeCheckbox" onchange="toggleAgreeButton()">
                <label for="agreeCheckbox" style="font-weight: normal; margin-bottom:0;">I have read and agree to the terms and conditions of the Mary The Queen College of Quezon City Property Custodian Office.</label>
            </div>
            <div class="modal-btns">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="button" id="confirmSubmitBtn" class="btn-agree" disabled onclick="submitActualForm()">Agree & Submit</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('student_id_input').addEventListener('input', function() {
            const val = this.value.trim();
            const btn = document.getElementById('topDashboardBtn');
            if (val !== '') {
                btn.href = 'borrower.php?student_id=' + encodeURIComponent(val) + '&tab=active';
            } else {
                btn.href = 'borrower.php';
            }
        });

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const snapshotPreview = document.getElementById('snapshot-preview');
        const captureBtn = document.getElementById('captureBtn');
        const selfieInput = document.getElementById('selfie_image');

        window.addEventListener('DOMContentLoaded', () => {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 }, audio: false })
                    .then(stream => {
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.error("Webcam error: ", err);
                        alert("Hindi ma-access ang webcam. Pakitiyak na pinayagan mo ito sa browser.");
                    });
            } else {
                alert("Ang iyong browser ay hindi sumusuporta sa paggamit ng webcam.");
            }
        });

        function takeSnapshot() {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth || 320;
            canvas.height = video.videoHeight || 240;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const dataURL = canvas.toDataURL('image/jpeg');
            selfieInput.value = dataURL;
            
            snapshotPreview.src = dataURL;
            snapshotPreview.style.display = 'block';
            video.style.display = 'none';
            captureBtn.innerText = "Retake Selfie";
            captureBtn.onclick = retakeSnapshot;
        }

        function retakeSnapshot() {
            video.style.display = 'block';
            snapshotPreview.style.display = 'none';
            selfieInput.value = '';
            captureBtn.innerText = "Capture Selfie";
            captureBtn.onclick = takeSnapshot;
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            if (!selfieInput.value) {
                alert('Please capture a live selfie before submitting your registration.');
                return false;
            }
            document.getElementById('termsModal').style.display = 'flex';
            return false;
        }

        function toggleAgreeButton() {
            const checkbox = document.getElementById('agreeCheckbox');
            const submitBtn = document.getElementById('confirmSubmitBtn');
            submitBtn.disabled = !checkbox.checked;
        }

        function closeModal() {
            document.getElementById('termsModal').style.display = 'none';
        }

        function submitActualForm() {
            document.getElementById('registrationForm').submit();
        }
    </script>
</body>
</html>