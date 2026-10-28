<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = "";
$upload_dir = 'uploads/';
$db_file = $upload_dir . 'database.sqlite';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (file_exists($db_file)) {
        try {
            $db = new PDO("sqlite:" . $db_file);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) AND password = ?");
            $stmt->execute([$email, $password]);
            $matched_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($matched_user) {
                $_SESSION['user_id'] = $matched_user['student_id'];
                $_SESSION['user_name'] = $matched_user['full_name'];
                $_SESSION['user_role'] = $matched_user['role'];
                $_SESSION['user_selfie'] = $matched_user['selfie'];
                $_SESSION['course_year'] = isset($matched_user['course_year']) ? $matched_user['course_year'] : '';

                if ($matched_user['role'] === 'custodian') {
                    header("Location: custodian_dashboard.php");
                } else {
                    header("Location: borrower.php");
                }
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Database connection error.";
        }
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-0 m-0 font-sans">
    <div class="w-full min-h-screen flex flex-col lg:flex-row">
        <!-- Kaliwang Bahagi: School Branding -->
        <div class="lg:w-7/12 relative bg-[#00381d] flex flex-col justify-between p-6 sm:p-8 lg:p-12 text-white overflow-hidden min-h-[350px] lg:min-h-screen">
            <div class="absolute inset-0 bg-cover bg-center opacity-65" style="background-image: url('school_bg.jpg');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#002613]/80 via-[#004822]/40 to-transparent"></div>
            
            <div class="relative z-10 flex flex-col items-center text-center mx-auto max-w-lg pt-4">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-2 border-white object-cover bg-white shadow-lg mb-3" onerror="this.src='https://via.placeholder.com/80'">
                <h2 class="text-[11px] sm:text-xs md:text-sm font-bold tracking-wider text-emerald-200 mb-1">MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
                <h1 class="text-lg sm:text-xl md:text-3xl font-black tracking-tight text-white leading-tight">QR-BASED EQUIPMENT MONITORING SYSTEM</h1>
            </div>

            <div class="relative z-10 my-auto py-6 max-w-xl mx-auto w-full text-center">
                <p class="text-xs sm:text-sm md:text-base text-white font-semibold italic mb-6 drop-shadow-md">"Together for a Smarter, Safer and More Efficient Campus"</p>
                <div class="space-y-3 text-left">
                    <div class="bg-[#005a2b]/90 border border-emerald-400/60 p-3.5 rounded-lg shadow-lg backdrop-blur-sm">
                        <span class="text-xs sm:text-sm font-bold text-white flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-300 flex-shrink-0"></span> Track Equipment - Seamless borrowing & return logs</span>
                    </div>
                    <div class="bg-[#005a2b]/90 border border-emerald-400/60 p-3.5 rounded-lg shadow-lg backdrop-blur-sm">
                        <span class="text-xs sm:text-sm font-bold text-white flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-300 flex-shrink-0"></span> Improve Accountability - Real-time condition monitoring</span>
                    </div>
                    <div class="bg-[#005a2b]/90 border border-emerald-400/60 p-3.5 rounded-lg shadow-lg backdrop-blur-sm">
                        <span class="text-xs sm:text-sm font-bold text-white flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-300 flex-shrink-0"></span> Build a Smarter Campus - Automated digital verification</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-[11px] sm:text-xs text-emerald-200 font-medium text-center">Property Custodian & Borrower Portal &copy; 2026</div>
        </div>

        <!-- Kanang Bahagi: Login Form -->
        <div class="lg:w-5/12 bg-white flex flex-col justify-center p-6 sm:p-8 lg:p-10 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome Back!</h2>
                    <p class="text-xs text-gray-500 mt-1">Login to your account to continue</p>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Enter your email address">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Enter your password">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-3 rounded-lg text-sm font-semibold transition shadow-md">Login</button>
                    </div>
                </form>
                <div class="mt-6 text-center text-xs text-gray-500 border-t pt-4">Don't have an account yet? <a href="register.php" class="text-emerald-700 font-semibold underline">Register here</a></div>
            </div>
        </div>
    </div>
</body>
</html>
