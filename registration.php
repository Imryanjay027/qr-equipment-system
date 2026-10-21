<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Siguraduhing may borrow_requests table para sa mga hiram na kagamitan
$conn->query("CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    course_year VARCHAR(100) NOT NULL,
    item_to_borrow VARCHAR(255) NOT NULL,
    return_date DATE NOT NULL,
    return_time VARCHAR(50) NOT NULL,
    selfie_image TEXT,
    status VARCHAR(50) DEFAULT 'Pending'
)");

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $course_year = trim($_POST['course_year'] ?? '');
    $item_to_borrow = trim($_POST['item_to_borrow'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $return_time = trim($_POST['return_time'] ?? '');
    $selfie_image = $_POST['selfie_image'] ?? '';

    if (!empty($student_id) && !empty($full_name) && !empty($item_to_borrow)) {
        $stmt = $conn->prepare("INSERT INTO borrow_requests (student_id, full_name, course_year, item_to_borrow, return_date, return_time, selfie_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("sssssss", $student_id, $full_name, $course_year, $item_to_borrow, $return_date, $return_time, $selfie_image);
        
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $success_msg = "Borrow request submitted successfully! Redirecting to dashboard...";
            echo "<script>setTimeout(function() { window.location.href = 'borrower.php?uid={$last_id}'; }, 1500);</script>";
        } else {
            $error_msg = "Database error: " . $conn->error;
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Borrower Registration Form - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .top-navbar {
            background-color: #217835;
            color: #ffffff;
            padding: 12px 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .form-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            padding: 30px;
            max-width: 700px;
            margin: 30px auto;
        }
        .logo-img {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .btn-green {
            background-color: #217835;
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 10px;
        }
        .btn-green:hover {
            background-color: #1a602a;
            color: #ffffff;
        }
        .camera-box {
            border: 2px dashed #ced4da;
            border-radius: 8px;
            background: #000;
            overflow: hidden;
            position: relative;
            width: 100%;
            height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        video, canvas, #captured-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <!-- Top Header Bar -->
    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MARY THE QUEEN COLLEGE - BORROWER PORTAL</div>
        <div class="d-flex gap-2">
            <a href="borrower.php" class="btn btn-dark btn-sm px-3">Back to your Dashboard</a>
            <a href="login.php" class="btn btn-danger btn-sm px-3">Log In</a>
        </div>
    </div>

    <div class="container my-4">
        <div class="card form-card">
            
            <div class="text-center mb-4">
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/8/82/Mary_the_Queen_College_of_Quezon_City_logo.png/220px-Mary_the_Queen_College_of_Quezon_City_logo.png" alt="Logo" class="logo-img mb-2">
                <h4 class="fw-bold text-dark mb-1">Student Borrower Registration Form</h4>
                <p class="text-muted small mb-0">QR - BASED SCHOOL EQUIPMENT BORROWING, RETURN, AND CONDITION MONITORING SYSTEM</p>
                <p class="text-muted small">Please complete the registration form below, take a live selfie, and submit to borrow an item.</p>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success text-center"><?= $success_msg; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
            <?php endif; ?>

            <form action="registration.php" method="POST">
                <!-- Live Selfie Verification -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small">Live Selfie Verification:</label>
                    <div class="camera-box mb-2">
                        <video id="video" autoplay playsinline></video>
                        <canvas id="canvas" style="display:none;"></canvas>
                        <img id="captured-preview" style="display:none;" alt="Captured Selfie"/>
                    </div>
                    <input type="hidden" name="selfie_image" id="selfie_image">
                    <div class="text-center">
                        <button type="button" id="snap-btn" class="btn btn-outline-secondary btn-sm px-4">Retake Selfie</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Student ID Number:</label>
                    <input type="text" class="form-control" name="student_id" required placeholder="Enter Student ID" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Full Name:</label>
                    <input type="text" class="form-control" name="full_name" required placeholder="Enter Full Name" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Course & Year / Grade & Section:</label>
                    <input type="text" class="form-control" name="course_year" required placeholder="e.g. BSIT 3rd Year" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Item to Borrow:</label>
                    <input type="text" class="form-control" name="item_to_borrow" required placeholder="e.g. Keyboard or Projector" autocomplete="off">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark small">Return Date:</label>
                        <input type="date" class="form-control" name="return_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark small">Return Time:</label>
                        <input type="time" class="form-control" name="return_time" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-green w-100 mt-3">Submit Registration</button>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Camera streaming para sa live selfie verification
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturedPreview = document.getElementById('captured-preview');
        const snapBtn = document.getElementById('snap-btn');
        const selfieInput = document.getElementById('selfie_image');

        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.log("Camera access error: " + err);
            });

        snapBtn.addEventListener('click', function() {
            canvas.width = video.videoWidth || 320;
            canvas.height = video.videoHeight || 240;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const dataURL = canvas.toDataURL('image/png');
            capturedPreview.src = dataURL;
            capturedPreview.style.display = 'block';
            video.style.display = 'none';
            
            selfieInput.value = dataURL;
        });
    </script>
</body>
</html>
