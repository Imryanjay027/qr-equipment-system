<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connection.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error_message = "Mangyaring punan ang lahat ng field.";
    } else {
        try {
            // Hanapin ang user gamit ang email sa database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$identifier]);
            $found_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($found_user && password_verify($password, $found_user['password'])) {
                // I-set ang session variables mula sa mismong database record ng user
                $_SESSION['student_id'] = $found_user['student_id'] ?? $found_user['id'] ?? $identifier;
                $_SESSION['user_name'] = $found_user['name'] ?? $found_user['full_name'] ?? 'User';
                $_SESSION['user_role'] = (strtolower($found_user['role'] ?? '') === 'custodian') ? 'Property Custodian' : 'Borrower';
                $_SESSION['course_year'] = $found_user['course_year_section'] ?? $found_user['course_year'] ?? '';
                
                // Kunin nang tama ang live selfie mula sa database column (profile_picture o selfie)
                $_SESSION['profile_photo'] = !empty($found_user['profile_picture']) ? $found_user['profile_picture'] : (!empty($found_user['selfie']) ? $found_user['selfie'] : 'logo.jpg');

                if (strtolower($found_user['role'] ?? '') === 'custodian') {
                    header("Location: custodian_dashboard.php");
                } else {
                    header("Location: borrower.php");
                }
                exit;
            } else {
                $error_message = "Invalid Email or Password!";
            }
        } catch (PDOException $e) {
            $error_message = "Database Error: " . $e->getMessage();
        }
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
        <!-- Left Side: School Branding -->
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
            </div>

            <div class="relative z-10 text-[11px] sm:text-xs text-emerald-200 font-medium text-center">Property Custodian & Borrower Portal &copy; 2026</div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:w-5/12 bg-white flex flex-col justify-center p-6 sm:p-8 lg:p-10 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Welcome Back</h2>
                    <p class="text-xs text-gray-500">Please enter your credentials to login.</p>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="text" name="identifier" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Enter your email address">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Enter your password">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-3 rounded-lg text-sm font-semibold transition shadow-md">Login</button>
                    </div>
                </form>
                <div class="mt-6 text-center text-xs text-gray-500 border-t pt-4">Don't have an account? <a href="register.php" class="text-emerald-700 font-semibold underline">Register here</a></div>
            </div>
        </div>
    </div>
</body>
</html>
