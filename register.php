<?php
session_start();

$error_message = "";
$success_message = "";

if (!isset($_SESSION['registered_users'])) {
    $_SESSION['registered_users'] = [
        [
            'student_id' => 'admin',
            'email' => 'admin@mqcqc.edu.ph',
            'full_name' => 'Ryan Jay Leoligao',
            'password' => 'admin123',
            'role' => 'custodian',
            'selfie' => 'logo.jpg'
        ]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'borrower';
    $course_year = trim($_POST['course_year']);
    
    // Handle captured selfie image (Base64 data galing sa webcam)
    $selfie_path = 'logo.jpg';
    if (!empty($_POST['selfie_base64'])) {
        $data = $_POST['selfie_base64'];
        $data = str_replace('data:image/jpeg;base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        $decoded_data = base64_decode($data);
        
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_selfie.jpg';
        $selfie_path = $upload_dir . $file_name;
        file_put_contents($selfie_path, $decoded_data);
    }

    // Test Case: I-check kung nagamit na ang email o student ID
    $isDuplicate = false;
    foreach ($_SESSION['registered_users'] as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: Ang email address na ito ay nakarehistro na sa sistema!";
            break;
        }
        if (strcasecmp($user['student_id'], $student_id) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: Ang Student ID na ito ay nagamit na!";
            break;
        }
    }

    if (!$isDuplicate) {
        $_SESSION['registered_users'][] = [
            'student_id' => $student_id,
            'email' => $email,
            'full_name' => $full_name,
            'password' => $password,
            'role' => $role,
            'course_year' => $course_year,
            'selfie' => $selfie_path
        ];
        $success_message = "Test Case Passed: Matagumpay na nakapag-register ang account!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QR Equipment Monitoring System[cite: 3]</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-0 m-0 font-sans">

    <div class="w-full min-h-screen flex flex-col lg:flex-row">
        
        <!-- Kaliwang Bahagi: School Branding & Background Image (Katulad sa Reference)[cite: 3] -->
        <div class="lg:w-7/12 relative bg-[#00381d] flex flex-col justify-between p-8 lg:p-12 text-white overflow-hidden min-h-[300px] lg:min-h-screen">
            <!-- Background Image overlay -->
            <div class="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-overlay" style="background-image: url('logo.jpg');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#002613] via-[#004822]/80 to-transparent"></div>

            <!-- Top Logo & Title -->
            <div class="relative z-10 flex items-center space-x-4">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white shadow-md" onerror="this.src='https://via.placeholder.com/60'">
                <div>
                    <h2 class="text-xs md:text-sm font-bold tracking-wider text-emerald-200">MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
                    <h1 class="text-xl md:text-3xl font-black tracking-tight text-white">QR-BASED EQUIPMENT MONITORING SYSTEM[cite: 3]</h1>
                </div>
            </div>

            <!-- Middle Tagline -->
            <div class="relative z-10 my-auto py-8 max-w-xl">
                <p class="text-sm md:text-base text-gray-200 italic mb-6">"Together for a Smarter, Safer and More Efficient Campus"[cite: 3]</p>
                
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 bg-black/30 p-3 rounded-lg border border-emerald-500/30 backdrop-blur-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="text-xs md:text-sm font-medium">Track Equipment - Seamless borrowing & return logs[cite: 3]</span>
                    </div>
                    <div class="flex items-center space-x-3 bg-black/30 p-3 rounded-lg border border-emerald-500/30 backdrop-blur-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="text-xs md:text-sm font-medium">Improve Accountability - Real-time condition monitoring[cite: 3]</span>
                    </div>
                    <div class="flex items-center space-x-3 bg-black/30 p-3 rounded-lg border border-emerald-500/30 backdrop-blur-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="text-xs md:text-sm font-medium">Build a Smarter Campus - Automated digital verification[cite: 3]</span>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <div class="relative z-10 text-xs text-emerald-300">
                Property Custodian & Borrower Portal &copy; 2026
            </div>
        </div>

        <!-- Kanang Bahagi: Registration Form na may Malaking Live Selfie Capture[cite: 3] -->
        <div class="lg:w-5/12 bg-white flex flex-col justify-center p-6 lg:p-10 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">
                
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Create Account</h2>
                    <p class="text-xs text-gray-500">I-fill out ang mga detalye kasama ang iyong live selfie.</p>
                </div>

                <!-- Error Alert -->
                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Success Alert -->
                <?php if (!empty($success_message)): ?>
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
                        <?php echo htmlspecialchars($success_message); ?>
                        <div class="mt-2">
                            <a href="login.php" class="text-emerald-900 font-bold underline">Mag-login na dito &rarr;</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" id="register-form" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Student / Employee ID</label>
                        <input type="text" name="student_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Hal. MQCQC-12345">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Pangalan at Apelyido">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="email@mqcqc.edu.ph">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="********">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Register As</label>
                        <select name="role" id="role-select" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                            <option value="borrower">Student</option>
                            <option value="custodian">Property Custodian</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Course & Year</label>
                        <input type="text" name="course_year" placeholder="e.g. BSIT 3-A" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white[cite: 3]">
                    </div>

                    <!-- Malaking Live Selfie Capture Widget (Katulad sa Reference)[cite: 3] -->
                    <div id="selfie-section">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Live Selfie Capture[cite: 3]</label>
                        
                        <div class="relative w-full bg-black rounded-lg overflow-hidden border border-gray-300 mb-2 flex items-center justify-center min-h-[180px]">
                            <video id="webcam" autoplay playsinline class="w-full h-[180px] object-cover"></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            <img id="captured-preview" src="" alt="Captured Selfie" class="hidden w-full h-[180px] object-cover">
                        </div>

                        <input type="hidden" name="selfie_base64" id="selfie_base64">

                        <button type="button" id="capture-btn" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-2 rounded-lg text-xs font-semibold transition shadow-sm mb-1">
                            Capture Selfie[cite: 3]
                        </button>
                        <button type="button" id="retake-btn" class="hidden w-full bg-gray-600 hover:bg-gray-700 text-white py-1.5 rounded-lg text-xs font-semibold transition shadow-sm">
                            Retake Photo
                        </button>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-3 rounded-lg text-sm font-semibold transition shadow-md">
                            Register[cite: 3]
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-xs text-gray-500 border-t pt-4">
                    Already have an account? <a href="login.php" class="text-emerald-700 font-semibold hover:underline">Login here[cite: 3]</a>
                </div>

            </div>
        </div>

    </div>

    <script>
        // Live Webcam Streaming para sa Live Selfie Capture[cite: 3]
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const capturedPreview = document.getElementById('captured-preview');
        const selfieBase64Input = document.getElementById('selfie_base64');

        let streamInstance = null;

        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then(stream => {
                streamInstance = stream;
                video.srcObject = stream;
            })
            .catch(err => {
                console.warn("Webcam access denied or unavailable:", err);
            });

        captureBtn.addEventListener('click', () => {
            if (!streamInstance) {
                alert("Hindi ma-access ang camera.");
                return;
            }
            canvas.width = video.videoWidth || 320;
            canvas.height = video.videoHeight || 240;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const dataURL = canvas.toDataURL('image/jpeg');
            selfieBase64Input.value = dataURL;
            
            capturedPreview.src = dataURL;
            capturedPreview.classList.remove('hidden');
            video.classList.add('hidden');
            
            captureBtn.classList.add('hidden');
            retakeBtn.classList.remove('hidden');
        });

        retakeBtn.addEventListener('click', () => {
            selfieBase64Input.value = '';
            capturedPreview.classList.add('hidden');
            video.classList.remove('hidden');
            retakeBtn.classList.add('hidden');
            captureBtn.classList.remove('hidden');
        });
    </script>
</body>
</html><?php
session_start();

$error_message = "";
$success_message = "";

$json_file = 'requests.json';

// Initialize registration storage kung wala pa
if (!isset($_SESSION['registered_users'])) {
    $_SESSION['registered_users'] = [
        [
            'student_id' => 'admin',
            'email' => 'admin@mqcqc.edu.ph',
            'full_name' => 'Ryan Jay Leoligao',
            'password' => 'admin123',
            'role' => 'custodian',
            'selfie' => 'logo.jpg'
        ]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'borrower';
    
    // Handle selfie image upload kung meron
    $selfie_path = 'logo.jpg';
    if (isset($_FILES['selfie_image']) && $_FILES['selfie_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['selfie_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['selfie_image']['name']);
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $selfie_path = $upload_dir . $file_name;
        move_uploaded_file($file_tmp, $selfie_path);
    }

    // Test Case: I-check kung nagamit na ang email o student ID
    $isDuplicate = false;
    foreach ($_SESSION['registered_users'] as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: Ang email address na ito ay nakarehistro na sa sistema!";
            break;
        }
        if (strcasecmp($user['student_id'], $student_id) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: Ang Student ID na ito ay nagamit na!";
            break;
        }
    }

    if (!$isDuplicate) {
        $_SESSION['registered_users'][] = [
            'student_id' => $student_id,
            'email' => $email,
            'full_name' => $full_name,
            'password' => $password,
            'role' => $role,
            'selfie' => $selfie_path
        ];
        $success_message = "Test Case Passed: Matagumpay na nakapag-register ang account!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QR Equipment Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-md border border-gray-200 p-8 my-8">
        
        <div class="text-center mb-6">
            <img src="logo.jpg" alt="Logo" class="w-16 h-16 mx-auto rounded-full border border-emerald-600 object-cover bg-white mb-2" onerror="this.src='https://via.placeholder.com/60'">
            <h1 class="text-xl font-bold text-gray-800">CREATE ACCOUNT</h1>
            <p class="text-xs text-gray-500">Mary the Queen College of Quezon City</p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error_message)): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Success Alert -->
        <?php if (!empty($success_message)): ?>
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-medium">
                <?php echo htmlspecialchars($success_message); ?>
                <div class="mt-2">
                    <a href="login.php" class="text-emerald-800 font-bold underline">Mag-login na dito &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data" class="space-y-4 pb-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Student / Employee ID</label>
                <input type="text" name="student_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600" placeholder="Hal. MQCQC-12345">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                <input type="text" name="full_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600" placeholder="Pangalan at Apelyido">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600" placeholder="email@mqcqc.edu.ph">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600" placeholder="********">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">User Role</label>
                <select name="role" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600 bg-white">
                    <option value="borrower">Borrower (Student)</option>
                    <option value="custodian">Property Custodian</option>
                </select>
            </div>

            <!-- Live Selfie / Photo Upload para sa Student Verification -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Live Selfie / Student Photo</label>
                <input type="file" name="selfie_image" accept="image/*" capture="user" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-gray-200 rounded-lg p-2">
                <p class="text-[11px] text-gray-400 mt-1">Gamitin ang camera para sa live selfie o mag-upload ng larawan.</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    Register Account
                </button>
            </div>
        </form>

        <div class="mt-4 text-center text-xs text-gray-500 border-t pt-4">
            May account na ba? <a href="login.php" class="text-emerald-700 font-semibold hover:underline">Mag-login</a>
        </div>

    </div>

</body>
</html>
