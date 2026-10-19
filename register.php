<?php
// register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connection.php');

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $role = $_POST['role'] ?? 'student';
    $academic_info = trim($_POST['academic_info'] ?? '');
    $image_data = $_POST['image_data'] ?? '';

    if ($email && !empty($full_name)) {
        $photo_filename = '';

        // Live selfie para sa Student
        if ($role === 'student' && !empty($image_data)) {
            $image_parts = explode(";base64,", $image_data);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $target_dir = __DIR__ . "/uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $photo_filename = 'student_' . time() . '.png';
                file_put_contents($target_dir . $photo_filename, $image_base64);
            }
        }

        $stmt = $conn->prepare("INSERT INTO tbl_users (full_name, email, role, photo, academic_info) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $full_name, $email, $role, $photo_filename, $academic_info);
            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
                $status = "success";
            } else {
                $message = "Database error: " . $stmt->error;
                $status = "error";
            }
        } else {
            $message = "Query preparation failed: " . $conn->error;
            $status = "error";
        }
    } else {
        $message = "Please enter your full name and a valid email address.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MQCQC Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .login-card { background: white; padding: 40px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 480px; text-align: center; }
        .login-card img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; }
        .login-card h3 { margin: 10px 0 5px 0; color: #212529; font-size: 18px; font-weight: bold; }
        .login-card p.subtitle { color: #6c757d; font-size: 13px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #212529; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .camera-box { width: 100%; background-color: #000; border-radius: 6px; overflow: hidden; margin-bottom: 10px; position: relative; }
        video, canvas { width: 100%; height: auto; display: block; }
        canvas { display: none; }
        .btn { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background-color 0.2s; }
        .btn:hover { background-color: #1b5e20; }
        .btn-capture { background-color: #495057; margin-bottom: 15px; }
        .btn-capture:hover { background-color: #343a40; }
        .register-link { margin-top: 20px; font-size: 13px; color: #6c757d; }
        .register-link a { color: #2e7d32; text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Modal Styles para sa Terms & Conditions */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-content h4 { color: #2e7d32; margin-bottom: 15px; }
        .modal-content p { font-size: 13px; color: #444; line-height: 1.6; margin-bottom: 15px; }
        .terms-checkbox { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>

<div class="login-card">
    <img src="logo.jpg" alt="MQCQC Logo" onerror="this.src='uploads/default.png'">
    <h3>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h3>
    <p class="subtitle">Account Registration</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label for="role">Register As</label>
            <select id="role" name="role" required onchange="handleRoleChange()">
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="custodian">Property Custodian</option>
            </select>
        </div>

        <!-- Student Specific Section (Live Selfie & Academic Info) -->
        <div id="studentSection" class="conditional-section">
            <div class="form-group">
                <label>Live Selfie Capture (Student Only)</label>
                <div class="camera-box" id="cameraBox">
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas"></canvas>
                </div>
                <button type="button" id="captureBtn" class="btn btn-capture">Capture Selfie</button>
            </div>

            <div class="form-group">
                <label for="student_level">Student Level Category</label>
                <select id="student_level" onchange="handleLevelChange()">
                    <option value="college">College</option>
                    <option value="basic_ed">Elementary to SHS</option>
                </select>
            </div>
            
            <div class="form-group">
                <label id="academicLabel" for="academic_info">Course & Year</label>
                <input type="text" id="academic_info" name="academic_info" placeholder="e.g. BSIT 3-1">
            </div>
        </div>

        <input type="hidden" name="image_data" id="imageData">
        <button type="button" class="btn" onclick="handleRegisterClick()">Register</button>
    </form>

    <div class="register-link">Already have an account? <a href="login.php">Login via OTP here</a></div>
</div>

<!-- Translated Terms & Conditions Modal Pop-up -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <h4>Terms & Conditions (Equipment Borrowing)</h4>
        <p>By registering as a Student in the <strong>MQCQC Equipment Monitoring System</strong>, you agree to comply with the school's policy:</p>
        <p><em>"In the event that any borrowed equipment is damaged, lost, or rendered defective while under your care and responsibility, you are strictly required to replace or repair it with an item of the exact same specification, make, and quality (exact replacement)."</em></p>
        
        <div class="terms-checkbox">
            <input type="checkbox" id="agreeTerms" onchange="toggleAgree()">
            <label for="agreeTerms">I have read and agree to the Terms & Conditions.</label>
        </div>

        <button type="button" class="btn" id="proceedBtn" disabled style="opacity: 0.5;" onclick="submitForm()">Proceed & Register</button>
    </div>
</div>

<script>
    const registerForm = document.getElementById('registerForm');
    const roleSelect = document.getElementById('role');
    const studentSection = document.getElementById('studentSection');
    
    const studentLevel = document.getElementById('student_level');
    const academicLabel = document.getElementById('academicLabel');
    const academicInfoInput = document.getElementById('academic_info');

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('captureBtn');
    const imageDataInput = document.getElementById('imageData');
    let mediaStream = null;

    const termsModal = document.getElementById('termsModal');
    const agreeTerms = document.getElementById('agreeTerms');
    const proceedBtn = document.getElementById('proceedBtn');

    function handleRoleChange() {
        const selectedRole = roleSelect.value;

        if (selectedRole === 'student') {
            studentSection.style.display = 'block';
            academicInfoInput.required = true;
            startCamera();
        } else {
            studentSection.style.display = 'none';
            academicInfoInput.required = false;
            stopCamera();
        }
    }

    function handleLevelChange() {
        if (studentLevel.value === 'college') {
            academicLabel.textContent = "Course & Year";
            academicInfoInput.placeholder = "e.g. BSIT 3rd Year";
        } else {
            academicLabel.textContent = "Grade & Section";
            academicInfoInput.placeholder = "e.g. Grade 11 - St. Joseph";
        }
    }

    function handleRegisterClick() {
        const fullName = document.getElementById('full_name').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!fullName || !email) {
            alert("Please fill out your Full Name and Email Address first.");
            return;
        }

        if (roleSelect.value === 'student' && academicInfoInput.required && !academicInfoInput.value.trim()) {
            alert("Please fill out your " + academicLabel.textContent + ".");
            return;
        }

        // Kapag Student ang role, ipakita ang Terms & Conditions modal bago mag-submit
        if (roleSelect.value === 'student') {
            termsModal.style.display = 'flex';
        } else {
            registerForm.submit();
        }
    }

    function toggleAgree() {
        if (agreeTerms.checked) {
            proceedBtn.disabled = false;
            proceedBtn.style.opacity = "1";
        } else {
            proceedBtn.disabled = true;
            proceedBtn.style.opacity = "0.5";
        }
    }

    function submitForm() {
        termsModal.style.display = 'none';
        registerForm.submit();
    }

    function startCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(stream => {
                    mediaStream = stream;
                    if (video) video.srcObject = stream;
                })
                .catch(err => {
                    console.error("Camera access error: ", err);
                });
        }
    }

    function stopCamera() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
    }

    if (captureBtn) {
        captureBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (video && canvas) {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                if (imageDataInput) {
                    imageDataInput.value = canvas.toDataURL('image/png');
                }

                stopCamera();

                video.style.display = 'none';
                canvas.style.display = 'block';
                
                captureBtn.textContent = "Selfie Captured!";
                captureBtn.disabled = true;
                captureBtn.style.backgroundColor = "#6c757d";
            }
        });
    }

    // Run once on load
    handleRoleChange();
</script>

</body>
</html>
