<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? 'student');
    
    $education_level = null;
    $course_year_section = null;
    $profile_picture = null;

    if ($role === 'student') {
        $education_level = trim($_POST['education_level'] ?? 'College');
        $course_year_section = trim($_POST['course_year_section'] ?? '');
        $profile_picture = $_POST['profile_picture'] ?? ''; 
    } else {
        if (isset($_FILES['profile_picture_file']) && $_FILES['profile_picture_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture_file']['tmp_name'];
            $fileData = file_get_contents($fileTmpPath);
            $profile_picture = 'data:' . mime_content_type($fileTmpPath) . ';base64,' . base64_encode($fileData);
        }
    }

    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('Lahat ng kinakailangang field ay dapat punan!'); window.history.back();</script>";
        exit();
    }

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('Ang email na ito ay nakarehistro na!'); window.history.back();</script>";
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, education_level, course_year_section, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role, $education_level, $course_year_section, $profile_picture]);

        echo "<script>alert('Matagumpay ang pag-rehistro! Maaari ka nang mag-login.'); window.location.href='login.php';</script>";
        exit();

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - QR-Based Equipment Monitoring System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7f6; display: flex; height: 100vh; overflow: hidden; }
        
        .split-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .brand-panel {
            flex: 1.1;
            background: linear-gradient(rgba(0, 40, 20, 0.85), rgba(0, 30, 60, 0.9)), url('school_bg.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 60px;
        }

        .brand-top {
            text-align: center;
        }

        .brand-top img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.9);
            margin-bottom: 15px;
            object-fit: cover;
            background: white;
        }

        .brand-top h3 {
            font-size: 13px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #e0e0e0;
        }

        .brand-top h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .brand-top p {
            font-size: 12px;
            color: #d0d0d0;
            font-style: italic;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            background: rgba(0, 128, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 18px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }

        .feature-item .icon {
            background: #008000;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }

        .form-panel {
            flex: 1;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            overflow-y: auto;
        }

        .register-container {
            width: 100%;
            max-width: 440px;
        }

        .register-container h2 {
            font-size: 24px;
            color: #111;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .register-container .subtitle {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #333;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
        }

        .section-box {
            background: #f8f9fa;
            padding: 15px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
        }

        #webcam {
            width: 100%;
            max-width: 260px;
            height: 180px;
            background: #000;
            border-radius: 6px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .capture-btn {
            background-color: #008000;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .submit-btn {
            width: 100%;
            background-color: #008000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }

        .login-link {
            text-align: center;
            font-size: 13px;
            color: #555;
            margin-top: 15px;
        }

        .login-link a {
            color: #008000;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .split-container { flex-direction: column; overflow: auto; }
            body { overflow: auto; }
            .brand-panel { display: none; }
        }
    </style>
</head>
<body>

    <div class="split-container">
        <div class="brand-panel">
            <div class="brand-top">
                <img src="logo.jpg" alt="School Logo" onerror="this.src='https://via.placeholder.com/90'">
                <h3>MARY THE QUEEN COLLEGE OF QUEZON CITY</h3>
                <h1>QR-BASED EQUIPMENT MONITORING SYSTEM</h1>
                <p>Together for a Smarter, Safer and More Efficient Campus</p>
            </div>

            <div class="features-list">
                <div class="feature-item">
                    <div class="icon">📦</div>
                    <div><strong>Track Equipment</strong><br><span style="font-size:11px; color:#d0d0d0;">Seamless borrowing & return logs</span></div>
                </div>
                <div class="feature-item">
                    <div class="icon">🛡️</div>
                    <div><strong>Improve Accountability</strong><br><span style="font-size:11px; color:#d0d0d0;">Real-time condition monitoring</span></div>
                </div>
                <div class="feature-item">
                    <div class="icon">⚡</div>
                    <div><strong>Build a Smarter Campus</strong><br><span style="font-size:11px; color:#d0d0d0;">Automated digital verification</span></div>
                </div>
            </div>
        </div>

        <div class="form-panel">
            <div class="register-container">
                <h2>Create Your Account</h2>
                <p class="subtitle">Fill in the details below to register</p>

                <form action="register.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Enter your password">
                    </div>

                    <div class="form-group">
                        <label>Register As</label>
                        <select name="role" id="roleSelect" onchange="toggleRoleFields()" required>
                            <option value="student" selected>Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="staff">Staff</option>
                            <option value="custodian">Property Custodian</option>
                        </select>
                    </div>

                    <div id="studentFields">
                        <div class="form-group">
                            <label>Education Level</label>
                            <select name="education_level" id="eduLevel" onchange="updateEduFields()">
                                <option value="College">College</option>
                                <option value="Elementary-SHS">Elementary to SHS</option>
                            </select>
                        </div>

                        <div class="form-group" id="infoField">
                            <label>Course & Year</label>
                            <input type="text" name="course_year_section" placeholder="BSIT 3-A">
                        </div>

                        <div class="form-group section-box">
                            <label style="text-align:left; margin-bottom: 6px; display:block; font-weight:600; font-size:12px;">Live Selfie Capture</label>
                            <video id="webcam" autoplay playsinline></video><br>
                            <button type="button" class="capture-btn" onclick="captureSnapshot()">📸 Capture Selfie</button>
                            <canvas id="canvas" width="260" height="180" style="display:none;"></canvas>
                            <input type="hidden" name="profile_picture" id="profilePictureInput" required>
                        </div>
                    </div>

                    <div id="staffFields" style="display: none;">
                        <div class="form-group section-box">
                            <label style="text-align:left; display:block; font-weight:600; font-size:12px; margin-bottom:5px;">Profile Picture (Optional)</label>
                            <input type="file" name="profile_picture_file" accept="image/*" style="font-size:12px;">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Register</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');

        function startCamera() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => { 
                        video.srcObject = stream; 
                        video.play().catch(e => console.log("Play error:", e));
                    })
                    .catch(err => { 
                        console.log("Webcam error: " + err); 
                    });
            }
        }

        window.onload = function() { 
            startCamera(); 
        };

        function toggleRoleFields() {
            const role = document.getElementById('roleSelect').value;
            const studentFields = document.getElementById('studentFields');
            const staffFields = document.getElementById('staffFields');
            const profileInput = document.getElementById('profilePictureInput');

            if (role === 'student') {
                studentFields.style.display = 'block';
                staffFields.style.display = 'none';
                profileInput.required = true;
                startCamera();
            } else {
                studentFields.style.display = 'none';
                staffFields.style.display = 'block';
                profileInput.required = false;
                if (video.srcObject) {
                    let tracks = video.srcObject.getTracks();
                    tracks.forEach(track => track.stop());
                }
            }
        }

        function captureSnapshot() {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 260, 180);
            const imageData = canvas.toDataURL('image/png');
            document.getElementById('profilePictureInput').value = imageData;
            alert("Live selfie captured successfully!");
        }

        function updateEduFields() {
            const level = document.getElementById('eduLevel').value;
            const infoField = document.getElementById('infoField');
            if (level === 'College') {
                infoField.innerHTML = '<label>Course & Year</label><input type="text" name="course_year_section" placeholder="BSIT 3-A">';
            } else {
                infoField.innerHTML = '<label>Grade & Section</label><input type="text" name="course_year_section" placeholder="Grade 11 - St. Joseph">';
            }
        }
    </script>
</body>
</html>
