<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connection.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$studentId   = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['user_name'] ?? 'User';
$courseYear  = $_SESSION['course_year'] ?? '';
$qrCodeUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($studentId);

$profilePhoto = 'logo.jpg';
try {
    $profileColumns = [];
    try {
        $profileColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $profileColumns = ['profile_picture'];
    }

    $where = [];
    $params = [];
    foreach (['email', 'id', 'student_id', 'id_number', 'user_id'] as $column) {
        if (!in_array($column, $profileColumns, true)) {
            continue;
        }
        $where[] = "`$column` = ?";
        if ($column === 'email') {
            $params[] = (string) ($_SESSION['email'] ?? '');
        } else {
            $params[] = (string) $studentId;
        }
    }

    if ($where) {
        $stmtPhoto = $pdo->prepare("SELECT * FROM users WHERE (" . implode(' OR ', $where) . ") LIMIT 1");
        $stmtPhoto->execute($params);
        $usrData = $stmtPhoto->fetch(PDO::FETCH_ASSOC);

        foreach (['profile_picture', 'selfie', 'profile_pic', 'photo', 'student_selfie'] as $photoColumn) {
            if (isset($usrData[$photoColumn]) && $usrData[$photoColumn] !== '' && $usrData[$photoColumn] !== null) {
                $photoCandidate = (string) $usrData[$photoColumn];
                if ($photoCandidate !== 'logo.jpg') {
                    if (strpos($photoCandidate, '/') === false) {
                        $photoCandidate = 'uploads/' . $photoCandidate;
                    }
                    $profilePhoto = $photoCandidate;
                    break;
                }
            }
        }
    }
} catch (Throwable $e) {
    // Keep fallback below if the users table or query fails.
}

if (($profilePhoto === 'logo.jpg' || empty($profilePhoto)) && !empty($_SESSION['profile_photo']) && $_SESSION['profile_photo'] !== 'logo.jpg') {
    $profilePhoto = $_SESSION['profile_photo'];
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $return_time = trim($_POST['return_time'] ?? '');
    $borrow_date = date('Y-m-d H:i:s');
    $full_return_datetime = $return_date . ' ' . $return_time;

    if (empty($equipment_name) || empty($return_date) || empty($return_time)) {
        $error_message = "Mangyaring punan ang lahat ng kinakailangang field.";
    } else {
        try {
            // Sinubukan munang i-save gamit ang mga karaniwang columns (student_id, equipment_name, borrow_date, status)
            $stmt = $pdo->prepare("INSERT INTO requests (student_id, equipment_name, borrow_date, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->execute([$studentId, $equipment_name, $borrow_date]);
            $success_message = "Matagumpay na naisumite ang kahilingan sa paghiram!";
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
    <title>Borrow an Item - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <!-- Mobile Header with Hamburger Menu -->
    <header class="bg-[#001f12] text-white p-4 flex justify-between items-center md:hidden shadow-md">
        <div class="flex items-center gap-3">
            <img src="logo.jpg" alt="Logo" class="w-9 h-9 rounded-full border border-white object-cover bg-white">
            <span class="text-xs font-bold tracking-wider">MQCQC EMS</span>
        </div>
        <button onclick="toggleMobileMenu()" class="p-2 focus:outline-none text-emerald-200 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </header>

    <!-- Mobile Sidebar Drawer -->
    <div id="mobile-menu" class="fixed inset-0 bg-black/60 z-50 hidden flex">
        <div class="w-64 bg-[#001f12] text-white flex flex-col justify-between p-6 h-full shadow-2xl relative">
            <button onclick="toggleMobileMenu()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg font-bold">&times;</button>
            <div>
                <div class="flex flex-col items-center text-center mb-8 pt-2">
                    <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white mb-3">
                    <h1 class="text-xs font-bold uppercase tracking-wider text-center leading-tight">MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
                </div>
                <nav class="space-y-2">
                    <a href="borrower.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-800/40 text-emerald-100 text-xs font-medium transition">Dashboard</a>
                    <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#008040] text-white text-xs font-semibold shadow-sm">Borrow an Item</a>
                    <a href="borrower_history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-800/40 text-emerald-100 text-xs font-medium transition">Returned Items / History</a>
                </nav>
            </div>
            <div class="pt-4 border-t border-emerald-900">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-600/20 text-red-400 text-xs font-medium transition">Logout</a>
            </div>
        </div>
        <div class="flex-grow" onclick="toggleMobileMenu()"></div>
    </div>

    <div class="flex flex-grow">
        <!-- Desktop Sidebar Navigation -->
        <aside class="w-64 bg-[#001f12] text-white flex flex-col justify-between p-6 hidden md:flex">
            <div>
                <div class="flex flex-col items-center text-center mb-8">
                    <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white mb-3" onerror="this.src='https://via.placeholder.com/64'">
                    <h1 class="text-xs font-bold uppercase tracking-wider text-center leading-tight">MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
                </div>

                <nav class="space-y-2">
                    <a href="borrower.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-800/40 text-emerald-100 text-xs font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#008040] text-white text-xs font-semibold shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Borrow an Item
                    </a>
                    <a href="borrower_history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-800/40 text-emerald-100 text-xs font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Returned Items / History
                    </a>
                </nav>
            </div>

            <div class="pt-4 border-t border-emerald-900">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-600/20 text-red-400 text-xs font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow p-4 sm:p-6 lg:p-10 space-y-6 overflow-x-auto">
            
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4 sm:gap-5 w-full">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-4 border-emerald-600 overflow-hidden shadow-md bg-white flex items-center justify-center flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Live Selfie" class="w-full h-full object-cover" onerror="this.src='logo.jpg'">
                    </div>
                    <div>
                        <p class="text-base sm:text-lg font-extrabold text-gray-800"><?php echo htmlspecialchars($studentName); ?></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Student ID: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($studentId); ?></span></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Course / Year: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($courseYear); ?></span></p>
                    </div>
                </div>

                <div class="border border-gray-200 p-3 rounded-xl bg-white text-center shadow-sm flex flex-col items-center cursor-pointer group" onclick="openQrModal()">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="Student QR Code" class="w-20 h-20 sm:w-24 sm:h-24 object-contain rounded mb-1 group-hover:scale-105 transition">
                    <span class="text-[10px] text-gray-400 group-hover:text-emerald-700 font-semibold">Click to Zoom</span>
                </div>
            </div>

            <!-- Borrow Form Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Borrow Equipment Request</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <form action="borrow_item.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Equipment Name / Item</label>
                            <input type="text" name="equipment_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="e.g. Projector, HDMI">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Return Date</label>
                            <input type="date" name="return_date" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Return Time</label>
                            <input type="time" name="return_time" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                        </div>
                    </div>

                    <!-- Terms & Conditions Section -->
                    <div class="bg-slate-50 border border-gray-200 p-4 rounded-xl text-xs text-gray-700 space-y-2 mt-4">
                        <h3 class="font-bold text-gray-900 uppercase tracking-wide">Terms & Conditions</h3>
                        <p>By submitting this request, you agree that if the borrowed equipment is damaged, broken, or lost while in your possession, you are strictly required to replace it with the exact same item, model, and brand.</p>
                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" id="terms" required class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <label for="terms" class="font-medium text-gray-800 cursor-pointer">I have read and agree to the Terms & Conditions.</label>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-[#008040] hover:bg-emerald-800 text-white px-6 py-3 rounded-xl text-sm font-semibold transition shadow-md">Submit Request</button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <!-- QR Zoom Modal -->
    <div id="qrModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4" onclick="closeQrModal()">
        <div class="bg-white p-6 rounded-2xl shadow-2xl text-center max-w-xs w-full flex flex-col items-center relative" onclick="event.stopPropagation()">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Student QR Code</h3>
            <p class="text-[11px] text-gray-500 mb-4">Show this to the Property Custodian to scan.</p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="Large QR Code" class="w-56 h-56 object-contain border-2 border-emerald-600 rounded-xl p-2 mb-4">
            <button onclick="closeQrModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-xl text-xs font-semibold transition">Close</button>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - MARY THE QUEEN COLLEGE OF QUEZON CITY
    </footer>

    <script>
        function openQrModal() { document.getElementById('qrModal').classList.remove('hidden'); }
        function closeQrModal() { document.getElementById('qrModal').classList.add('hidden'); }
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
