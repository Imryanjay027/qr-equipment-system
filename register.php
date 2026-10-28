<?php
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
