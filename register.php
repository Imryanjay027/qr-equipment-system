<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $course_year = isset($_POST['course_year']) ? trim($_POST['course_year']) : '';
    
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

    $isDuplicate = false;
    foreach ($_SESSION['registered_users'] as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: This email address is already registered in the system!";
            break;
        }
        if (strcasecmp($user['student_id'], $student_id) === 0) {
            $isDuplicate = true;
            $error_message = "Test Case Failed: This Student/Employee ID is already in use!";
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
        $success_message = "Test Case Passed: Account successfully registered!";
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
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-0 m-0 font-sans">

    <div class="w-full min-h-screen flex flex-col lg:flex-row">
        
        <!-- Kaliwang Bahagi: Eksaktong Layout at Estilo ayon sa Reference Image mo -->
        <div class="lg:w-7/12 relative bg-[#00381d] flex flex-col justify-between p-8 lg:p-12 text-white overflow-hidden min-h-[400px] lg:min-h-screen">
            <!-- Background Image na school_bg.jpg -->
            <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-overlay" style="background-image: url('school_bg.jpg');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#002613]/90 via-[#004822]/60 to-transparent"></div>

            <!-- Top Logo & Title -->
            <div class="relative z-10 flex items-center space-x-4">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white shadow-md" onerror="this.src='https://via.placeholder.com/60'">
                <div>
                    <h2 class="text-xs md:text-sm font-bold tracking-wider text-emerald-200">MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
                    <h1 class="text-xl md:text-3xl font-black tracking-tight text-white">QR-BASED EQUIPMENT MONITORING SYSTEM[cite: 3]</h1>
                </div>
            </div>

            <!-- Middle Tagline & Green Boxes katulad sa reference image -->
            <div class="relative z-10 my-auto py-8 max-w-xl">
                <p class="text-sm md:text-base text-gray-100 italic mb-6 font-medium">"Together for a Smarter, Safer and More Efficient Campus"[cite: 3]</p>
                
                <div class="space-y-3">
                    <div class="bg-[#005a2b]/80 border border-emerald-400/50 p-3.5 rounded-lg shadow-md backdrop-blur-sm">
                        <span class="text-xs md:text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Track Equipment - Seamless borrowing & return logs[cite: 3]
                        </span>
                    </div>
                    <div class="bg-[#005a2b]/80 border border-emerald-400/50 p-3.5 rounded-lg shadow-md backdrop-blur-sm">
                        <span class="text-xs md:text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Improve Accountability - Real-time condition monitoring[cite: 3]
                        </span>
                    </div>
                    <div class="bg-[#005a2b]/80 border border-emerald-400/50 p-3.5 rounded-lg shadow-md backdrop-blur-sm">
                        <span class="text-xs md:text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Build a Smarter Campus - Automated digital verification[cite: 3]
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="relative z-10 text-xs text-emerald-200">
                Property Custodian & Borrower Portal &copy; 2026
            </div>
        </div>

        <!-- Kanang Bahagi: Kumpletong Form ng Borrower at Custodian na hindi binago -->
        <div class="lg:w-5/12 bg-white flex flex-col justify-center p-6 lg:p-10 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">
                
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Create Account[cite: 3]</h2>
                    <p class="text-xs text-gray-500">Please fill out the details to register.</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
                        <?php echo htmlspecialchars($success_message); ?>
                        <div class="mt-2">
                            <a href="login.php" class="text-emerald-900 font-bold underline">Login here &rarr;[cite: 3]</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" id="register-form" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Student / Employee ID</label>
                        <input type="text" name="student_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="e.g. MQCQC-12345">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="First Name and Last Name">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="email@mqcqc.edu.ph">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Enter your password[cite: 8]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Register As[cite: 8]</label>
                            <select name="role" id="role-select" onchange="toggleCourseField()" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                                <option value="borrower">Student[cite: 8]</option>
                                <option value="faculty">Faculty</option>
                                <option value="staff">Staff</option>
                                <option value="custodian">Property Custodian</option>
                            </select>
                        </div>
                        
                        <div id="course-container">
                            <label id="course-label" class="block text-xs font-semibold text-gray-700 mb-1">Course & Year / Grade & Section</label>
                            <input type="text" name="course_year" id="course_input" placeholder="e.g. BSIT 3-A[cite: 8] or Grade 11-ICT" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                        </div>
                    </div>

                    <!-- Malaking Live Selfie Capture Widget na may Video Stream -->
                    <div id="selfie-section" class="bg-slate-50 border border-gray-200 p-4 rounded-xl">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Live Selfie Capture[cite: 3]</label>
                        
                        <div class="relative w-full bg-black rounded-lg overflow-hidden border border-gray-300 mb-3 flex items-center justify-center min-h-[180px]">
                            <video id="webcam" autoplay playsinline class="w-full h-[180px] object-cover"></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            <img id="captured-preview" src="" alt="Captured Selfie" class="hidden w-full h-[180px] object-cover">
                        </div>

                        <input type="hidden" name="selfie_base64" id="selfie_base64">

                        <div class="flex gap-2">
                            <button type="button" id="capture-btn" class="flex-1 bg-[#005a2b] hover:bg-[#004822] text-white py-2 rounded-lg text-xs font-semibold transition shadow-sm">
                                Capture Selfie[cite: 3]
                            </button>
                            <button type="button" id="retake-btn" class="hidden flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg text-xs font-semibold transition shadow-sm">
                                Retake Photo
                            </button>
                        </div>
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
        function toggleCourseField() {
            const roleSelect = document.getElementById('role-select');
            const courseContainer = document.getElementById('course-container');
            const courseInput = document.getElementById('course_input');
            const courseLabel = document.getElementById('course-label');

            if (roleSelect.value === 'borrower') {
                courseContainer.style.display = 'block';
                courseInput.required = true;
                courseLabel.innerText = "Course & Year / Grade & Section";
                courseInput.placeholder = "e.g. BSIT 3-A or Grade 11-ICT";
            } else if (roleSelect.value === 'faculty' || roleSelect.value === 'staff') {
                courseContainer.style.display = 'block';
                courseInput.required = false;
                courseLabel.innerText = "Department / Office";
                courseInput.placeholder = "e.g. CCS Department / Registrar";
            } else {
                courseContainer.style.display = 'none';
                courseInput.required = false;
                courseInput.value = '';
            }
        }

        toggleCourseField();

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
                alert("Camera access is not available.");
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
</html>
