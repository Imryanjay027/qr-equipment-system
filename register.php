<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = trim($_POST['role'] ?? 'Student');
    $student_id = trim($_POST['student_id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // SECURITY CHECK: Kung Custodian ang pinili, suriin kung umabot na sa 2 ang Custodian
    if ($role === 'Custodian') {
        $count_sql = "SELECT COUNT(*) as total FROM tbl_users WHERE role = 'Custodian'";
        $count_result = $conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        
        if ($count_row['total'] >= 2) {
            $error = "Paumanhin, naabot na ang maximum limit na 2 Custodian sa sistema.";
            $role = 'Student';
        }
    }

    $course_year = ($role === 'Student') ? trim($_POST['course_year'] ?? '') : '';
    $filename = '';
    $upload_folder = 'uploads/';

    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    if (empty($error) && !empty($student_id) && !empty($fullname) && !empty($email)) {
        
        // 1. Kung Student, kunin ang base64 selfie mula sa webcam
        if ($role === 'Student' && !empty($_POST['selfie_image'])) {
            $selfie_data = $_POST['selfie_image'];
            $image_parts = explode(";base64,", $selfie_data);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'jpeg';
                $image_base64 = base64_decode($image_parts[1]);
                
                $filename = 'selfie_' . $student_id . '_' . time() . '.' . $image_type;
                file_put_contents($upload_folder . $filename, $image_base64);
            }
        } 
        // 2. Kung Custodian, tanggapin ang uploaded file mula sa form
        elseif ($role === 'Custodian' && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = 'custodian_' . $student_id . '_' . time() . '.' . $file_ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_folder . $filename);
        }

        // Suriin kung may kapareho nang email o username
        $check = $conn->prepare("SELECT id FROM tbl_users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $student_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ang Email o ID Number / Username na ito ay nakarehistro na.";
        } else {
            // Gumamit ng 'photo' column ayon sa database estruktura mo
            $stmt = $conn->prepare("INSERT INTO tbl_users (role, username, student_id, fullname, course_year, email, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $role, $student_id, $student_id, $fullname, $course_year, $email, $filename);
            
            if ($stmt->execute()) {
                $success = "Matagumpay na nairehistro ang account! Maaari ka nang mag-log in.";
            } else {
                $error = "May naganap na error sa pag-rehistro: " . $conn->error;
            }
        }
    } elseif (empty($error)) {
        $error = "Mangyaring punan ang lahat ng kinakailangang impormasyon.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration - Mary The Queen College of Quezon City</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .register-card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); 
            max-width: 520px;
            width: 100%;
        }
        .btn-primary { 
            background-color: #28a745; 
            border: none; 
        }
        .btn-primary:hover { 
            background-color: #218838; 
        }
        #videoElement {
            width: 100%;
            height: 260px;
            border-radius: 8px;
            background: #000;
            object-fit: cover;
            border: 1px solid #ccc;
        }
        #canvasElement {
            display: none;
        }
        .preview-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #28a745;
            display: none;
            margin: 10px auto;
        }
    </style>
</head>
<body>

    <div class="card register-card p-4 bg-white">
        <div class="text-center mb-3">
            <img src="logo.jpg" alt="Logo" class="mb-2" style="max-height: 60px;" onerror="this.style.display='none';">
            <h5 class="fw-bold text-success mb-1" style="font-size: 13px; line-height: 1.4;">"QR - BASED SCHOOL EQUIPMENT BORROWING, RETURN, AND CONDITION MONITORING SYSTEM"</h5>
            <p class="text-muted small mb-0">Account Registration</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger py-2 text-center small" role="alert">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <?php if (!empty($success)) { ?>
            <div class="alert alert-success py-2 text-center small" role="alert">
                <?= htmlspecialchars($success); ?>
            </div>
        <?php } ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3 text-start">
                <label for="role" class="form-label fw-semibold text-secondary small">Account Role</label>
                <select name="role" id="role" class="form-select" onchange="toggleRoleFields()">
                    <option value="Student">Student</option>
                    <option value="Custodian">Custodian</option>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="student_id" class="form-label fw-semibold text-secondary small">ID Number / Username</label>
                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter ID Number" required>
            </div>

            <div class="mb-3 text-start">
                <label for="fullname" class="form-label fw-semibold text-secondary small">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your Full name" required>
            </div>

            <!-- Student Specific Field (Course & Year) -->
            <div class="mb-3 text-start student-field">
                <label for="course_year" class="form-label fw-semibold text-secondary small">Course & Year / Grade & Section</label>
                <input type="text" class="form-control" id="course_year" name="course_year" placeholder="e.g. BSIT 3rd Year">
            </div>

            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold text-secondary small">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
            </div>

            <!-- Live Webcam Selfie Capture Section (Student Only) -->
            <div class="mb-3 text-center student-field">
                <label class="form-label fw-semibold text-secondary small d-block text-start">Live Selfie Capture</label>
                
                <div id="camera-box" class="mb-2">
                    <video id="videoElement" autoplay playsinline></video>
                </div>
                
                <canvas id="canvasElement"></canvas>
                
                <img id="previewImage" class="preview-img" alt="Selfie Preview">
                
                <input type="hidden" id="selfie_image" name="selfie_image">
                
                <button type="button" id="captureBtn" class="btn btn-secondary btn-sm px-4 fw-bold">Capture Selfie</button>
                <button type="button" id="retakeBtn" class="btn btn-outline-danger btn-sm px-4 fw-bold" style="display:none;">Retake Selfie</button>
            </div>

            <!-- Custodian Profile Picture Upload Section (Custodian Only) -->
            <div class="mb-3 text-start custodian-field" style="display: none;">
                <label for="profile_pic" class="form-label fw-semibold text-secondary small">Custodian Profile Picture</label>
                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Submit</button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login here</a></p>
        </div>
    </div>

    <!-- Script para sa pagpapalit ng fields depende sa Role -->
    <script>
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const captureBtn = document.getElementById('captureBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const previewImage = document.getElementById('previewImage');
        const selfieInput = document.getElementById('selfie_image');
        const cameraBox = document.getElementById('camera-box');
        const roleSelect = document.getElementById('role');
        const studentFields = document.querySelectorAll('.student-field');
        const custodianFields = document.querySelectorAll('.custodian-field');

        let mediaStream = null;

        function toggleRoleFields() {
            const role = roleSelect.value;
            if (role === 'Custodian') {
                studentFields.forEach(el => el.style.display = 'none');
                custodianFields.forEach(el => el.style.display = 'block');
                stopCamera();
            } else {
                studentFields.forEach(el => el.style.display = 'block');
                custodianFields.forEach(el => el.style.display = 'none');
                startCamera();
            }
        }

        async function startCamera() {
            if (roleSelect.value !== 'Student') return;
            try {
                if (!mediaStream) {
                    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    video.srcObject = mediaStream;
                }
            } catch (err) {
                console.error("Error accessing webcam: ", err);
            }
        }

        function stopCamera() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }
        }

        startCamera();

        captureBtn.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth || 320;
            canvas.height = video.videoHeight || 240;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataURL = canvas.toDataURL('image/jpeg');
            selfieInput.value = dataURL;

            previewImage.src = dataURL;
            previewImage.style.display = 'block';
            cameraBox.style.display = 'none';

            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';

            stopCamera();
        });

        retakeBtn.addEventListener('click', () => {
            previewImage.style.display = 'none';
            cameraBox.style.display = 'block';
            selfieInput.value = '';

            retakeBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';

            startCamera();
        });
    </script>
</body>
</html>
