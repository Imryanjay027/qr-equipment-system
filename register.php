<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $education_level = trim($_POST['education_level'] ?? 'College');
    $course_year_section = trim($_POST['course_year_section'] ?? '');
    $profile_picture = $_POST['profile_picture'] ?? ''; // Base64 image

    if (empty($name) || empty($email) || empty($password) || empty($profile_picture)) {
        echo "<script>alert('All fields and live selfie are required!'); window.history.back();</script>";
        exit();
    }

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('This email is already registered!'); window.history.back();</script>";
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'borrower';

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, education_level, course_year_section, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role, $education_level, $course_year_section, $profile_picture]);

        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
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
        }

        .register-container h2 {
            color: #008000;
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
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

        .webcam-section {
            text-align: center;
            margin-top: 20px;
            background: #fafafa;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 6px;
        }

        .webcam-section label {
            text-align: left;
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
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
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: background 0.2s;
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
            transition: background 0.2s;
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

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>BORROWER REGISTRATION</h2>
        <form action="register.php" method="POST">
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
                <label>Education Level</label>
                <select name="education_level" id="eduLevel" onchange="updateFields()">
                    <option value="College">College</option>
                    <option value="Elementary-SHS">Elementary to SHS</option>
                </select>
            </div>

            <div class="form-group" id="infoField">
                <label>Course & Year</label>
                <input type="text" name="course_year_section" required placeholder="BSIT 3-A">
            </div>

            <div class="webcam-section">
                <label>Live Selfie Verification</label>
                <video id="webcam" autoplay playsinline></video><br>
                <button type="button" class="capture-btn" onclick="captureSnapshot()">📸 Capture Selfie</button>
                <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                <input type="hidden" name="profile_picture" id="profilePictureInput" required>
            </div>

            <button type="submit" class="submit-btn">Register Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => { alert("Camera access denied or unavailable: " + err); });

        function captureSnapshot() {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);
            const imageData = canvas.toDataURL('image/png');
            document.getElementById('profilePictureInput').value = imageData;
            alert("Live selfie captured successfully!");
        }

        function updateFields() {
            const level = document.getElementById('eduLevel').value;
            const infoField = document.getElementById('infoField');
            if (level === 'College') {
                infoField.innerHTML = '<label>Course & Year</label><input type="text" name="course_year_section" required placeholder="BSIT 3-A">';
            } else {
                infoField.innerHTML = '<label>Grade & Section</label><input type="text" name="course_year_section" required placeholder="Grade 11 - St. Joseph">';
            }
        }
    </script>
</body>
</html>
