<?php
error_reporting(E_ALL);
// Itago ang warnings sa display para hindi lumitaw sa itaas ng form, pero i-log pa rin kung kailangan
ini_set('display_errors', 0);

include 'db_connection.php';
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = trim($_POST['role'] ?? 'Student');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $course_year = trim($_POST['course_year'] ?? '');
    
    $photo_path = "";

    // Gamitin ang tamang absolute path gamit ang __DIR__ para sigurado sa Coolify container
    $target_dir = __DIR__ . "/uploads/";
    if (!file_exists($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    // Handle photo upload based on role
    if ($role === 'Student') {
        $webcam_image = $_POST['webcam_image'] ?? '';
        if (!empty($webcam_image)) {
            $image_parts = explode(";base64,", $webcam_image);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $file_name = "student_" . time() . "_" . mt_rand(1000, 9999) . ".jpg";
                $photo_path = "uploads/" . $file_name; // Relative path para sa database
                file_put_contents($target_dir . $file_name, $image_base64);
            }
        }
    } else {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $file_name = "custodian_" . time() . "_" . mt_rand(1000, 9999) . "." . $file_extension;
            $photo_path = "uploads/" . $file_name; // Relative path para sa database
            @move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $file_name);
        }
    }

    if (!empty($email) && !empty($fullname)) {
        // Limit to maximum of 2 Custodians
        if ($role === 'Custodian') {
            $check_custodian = $conn->query("SELECT COUNT(*) as cnt FROM tbl_users WHERE role='Custodian'");
            $c_row = $check_custodian->fetch_assoc();
            if ($c_row['cnt'] >= 2) {
                $error = "Maximum limit of 2 Custodian accounts reached.";
            }
        }

        if (empty($error)) {
            $sql = "INSERT INTO tbl_users (role, username, student_id, fullname, course_year, email, photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssss", $role, $username, $student_id, $fullname, $course_year, $email, $photo_path);
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Error executing query: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Database prepare error: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration - QR-BASED EQUIPMENT MONITORING SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0 p-4">
            <!-- Logo at Pamagat -->
            <div class="text-center mb-4">
                <img src="logo.jpg" alt="Logo" style="max-height: 80px;" class="mb-2">
                <h6 class="text-success fw-bold">"QR-BASED EQUIPMENT MONITORING SYSTEM"</h6>
                <p class="text-muted small mb-0">Account Registration</p>
            </div>
            
            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php } ?>
            <?php if (!empty($success)) { ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Account Role</label>
                    <select name="role" id="role" class="form-select" onchange="toggleRoleFields()" required>
                        <option value="Student">Student</option>
                        <option value="Custodian">Custodian</option>
                    </select>
                </div>

                <div class="mb-3 student-field">
                    <label class="form-label fw-bold">ID Number / Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter ID Number">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Enter your Full name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>

                <div class="mb-3 student-field">
                    <label class="form-label fw-bold">Student ID</label>
                    <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID">
                </div>

                <div class="mb-3 student-field">
                    <label class="form-label fw-bold">Course & Year</label>
                    <input type="text" name="course_year" class="form-control" placeholder="Enter Course & Year">
                </div>

                <!-- Student Webcam Capture Section -->
                <div class="mb-3 student-field" id="webcam-section">
                    <label class="form-label fw-bold">Capture Photo via Webcam</label>
                    <div id="my_camera" style="width:320px; height:240px; background:#ddd; margin-bottom:10px;"></div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="take_snapshot()">Take Snapshot</button>
                    <input type="hidden" name="webcam_image" id="webcam_image">
                    <div id="results" class="mt-2"></div>
                </div>

                <!-- Custodian File Upload Section -->
                <div class="mb-3 custodian-field" id="file-section" style="display:none;">
                    <label class="form-label fw-bold">Upload Profile Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold text-white">Submit</button>
            </form>
            <div class="text-center mt-3">
                <p class="small text-muted">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Webcamjs CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script>
        Webcam.set({
            width: 320,
            height: 240,
            image_format: 'jpeg',
            jpeg_quality: 90
        });
        Webcam.attach('#my_camera');

        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                document.getElementById('results').innerHTML = '<img src="'+data_uri+'" class="img-thumbnail" style="max-height:150px;"/>';
                document.getElementById('webcam_image').value = data_uri;
            });
        }

        function toggleRoleFields() {
            let role = document.getElementById('role').value;
            let studentFields = document.querySelectorAll('.student-field');
            let custodianField = document.getElementById('file-section');

            if (role === 'Custodian') {
                studentFields.forEach(el => el.style.display = 'none');
                custodianField.style.display = 'block';
            } else {
                studentFields.forEach(el => el.style.display = 'block');
                custodianField.style.display = 'none';
            }
        }
    </script>
</body>
</html>
