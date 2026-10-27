<?php
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
        $profile_picture = $_POST['profile_picture'] ?? ''; // Base64 mula sa webcam
    } else {
        // Para sa Custodian, Faculty, o Staff (File Upload)
        if (isset($_FILES['profile_picture_file']) && $_FILES['profile_picture_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture_file']['tmp_name'];
            $fileData = file_get_contents($fileTmpPath);
            $profile_picture = 'data:' . mime_content_type($fileTmpPath) . ';base64,' . base64_encode($fileData);
        }
    }

    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('Lahat ng pangunahing field ay kinakailangan!'); window.history.back();</script>";
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
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .register-container img {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .register-container h1 {
            font-size: 16px;
            color: #008000;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .register-container .subtitle {
            font-size: 13px;
            color: #555;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #007bff;
        }

        .section-box {
            background: #fafafa;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 6px;
            margin-top: 15px;
        }

        #webcam {
            width: 100%;
            max-width: 320px;
            height: 240px;
            background: #000;
            border-radius: 6px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .capture-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .capture-btn:hover {
            background-color: #218838;
        }

        .submit-btn {
            width: 100%;
            background-color: #0066ff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 25px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .login-link {
            text-align: center;
            font-size: 13px;
            color: #555;
            margin-top: 20px;
        }

        .login-link a {
            color: #0066ff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <img src="logo.jpg" alt="School Logo" onerror="this.src='https://via.placeholder.com/75'">
        
        <h1>QR-BASED EQUIPMENT MONITORING SYSTEM</h1>
        <p class="subtitle">Account Registration</p>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="Juan Dela Cruz">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="example@gmail.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label>Register As</label>
                <select name="role" id="roleSelect" onchange="toggleRoleFields()" required>
                    <option value="" disabled selected>Select Account Type</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="staff">Staff</option>
                    <option value="custodian">Property Custodian</option>
                </select>
            </div>

            <!-- Student Exclusive Fields -->
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

                <div class="form-group section-box" style="text-align:center;">
                    <label style="text-align:left;">Live Selfie Verification</label>
                    <video id="webcam" autoplay playsinline></video><br>
                    <button type="button" class="capture-btn" onclick="captureSnapshot()">📸 Capture Selfie</button>
                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                    <input type="hidden" name="profile_picture" id="profilePictureInput">
                </div>
            </div>

            <!-- Non-Student Exclusive Fields (Custodian, Faculty, Staff) -->
            <div id="staffFields" style="display: none;">
                <div class="form-group section-box">
                    <label>Profile Picture (Optional)</label>
                    <input type="file" name="profile_picture_file" accept="image/*">
                </div>
            </div>

            <button type="submit" class="submit-btn">Register Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        
        // Simulan ang webcam kung sakaling student ang naka-select
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => { console.log("Webcam not accessed: " + err); });

        function toggleRoleFields() {
            const role = document.getElementById('roleSelect').value;
            const studentFields = document.getElementById('studentFields');
            const staffFields = document.getElementById('staffFields');
            const profileInput = document.getElementById('profilePictureInput');

            if (role === 'student') {
                studentFields.style.display = 'block';
                staffFields.style.display = 'none';
                profileInput.required = true;
            } else {
                studentFields.style.display = 'none';
                staffFields.style.display = 'block';
                profileInput.required = false;
            }
        }

        function captureSnapshot() {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);
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
