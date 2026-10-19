<?php
// register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connection.php');

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $image_data = $_POST['image_data'] ?? '';

    if ($email) {
        $photo_filename = '';
        if (!empty($image_data)) {
            // I-process ang base64 image galing sa canvas capture
            $image_parts = explode(";base64,", $image_data);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $target_dir = __DIR__ . "/uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $photo_filename = 'user_' . time() . '.png';
                file_put_contents($target_dir . $photo_filename, $image_base64);
            }
        }

        // I-save sa database (Siguraduhing tugma sa istruktura ng tbl_users mo)
        $stmt = $conn->prepare("INSERT INTO tbl_users (email, photo, role) VALUES (?, ?, 'borrower')");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $photo_filename);
            if ($stmt->execute()) {
                $message = "Matagumpay ang pagrehistro! Maaari ka nang mag-login.";
                $status = "success";
            } else {
                $message = "May error sa database: " . $stmt->error;
                $status = "error";
            }
        }
    } else {
        $message = "Mangyaring maglagay ng tamang email address.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QR-Based Equipment Monitoring System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .register-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 480px; text-align: center; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #222; font-size: 13px; }
        .form-group input[type="email"] { width: 100%; padding: 11px 14px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; outline: none; }
        .camera-container { position: relative; width: 100%; max-width: 400px; margin: 0 auto 15px auto; background: #000; border-radius: 6px; overflow: hidden; }
        video, canvas { width: 100%; height: auto; display: block; }
        canvas { display: none; }
        .btn { background-color: #2d6a4f; color: white; padding: 11px; border: none; border-radius: 4px; width: 100%; font-size: 15px; cursor: pointer; font-weight: 600; margin-bottom: 10px; }
        .btn:hover { background-color: #1b4332; }
        .btn-capture { background-color: #495057; }
        .btn-capture:hover { background-color: #343a40; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; text-align: left; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .login-link { margin-top: 15px; font-size: 13px; color: #555; }
        .login-link a { color: #2d6a4f; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="register-card">
    <h3>Register Account</h3>
    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">QR-Based Equipment Monitoring System</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="registerForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label>Live Selfie Capture</label>
            <div class="camera-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
            </div>
            <button type="button" id="captureBtn" class="btn btn-capture">Capture Selfie</button>
        </div>

        <input type="hidden" name="image_data" id="imageData">
        <button type="submit" class="btn">Register</button>
    </form>

    <div class="login-link">Already have an account? <a href="login.php">Login via OTP here</a></div>
</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('captureBtn');
    const imageDataInput = document.getElementById('imageData');
    let mediaStream = null;

    // Paganahin ang camera sa pagbukas ng page
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(stream => {
            mediaStream = stream;
            video.srcObject = stream;
        })
        .catch(err => {
            console.error("Hindi ma-access ang camera: ", err);
            alert("Hindi ma-access ang iyong camera. Mangyaring suriin ang mga pahintulot.");
        });

    // Kapag-click ang Capture Selfie
    captureBtn.addEventListener('click', function() {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        // Kopyahin ang kasalukuyang frame mula sa video papunta sa canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // I-convert ang canvas sa Base64 data URL
        const dataURL = canvas.toDataURL('image/png');
        imageDataInput.value = dataURL;

        // Ihinto ang lahat ng media tracks ng camera para tumigil ito
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
        }

        // Itago ang video stream at ipakita ang nakuha at naka-freeze na litrato sa canvas
        video.style.display = 'none';
        canvas.style.display = 'block';
        captureBtn.textContent = "Selfie Captured!";
        captureBtn.disabled = true;
        captureBtn.style.backgroundColor = "#6c757d";
    });
</script>

</body>
</html>
