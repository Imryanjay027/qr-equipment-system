<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$successMsg = '';
$errorMsg = '';

if (!isset($_SESSION['mock_borrowed'])) {
    $_SESSION['mock_borrowed'] = [];
}

// Session Defaults
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'Borrower';
    $_SESSION['user_name'] = 'Ryan Jay Leoligao';
    $_SESSION['student_id'] = '1';
    $_SESSION['course_year'] = 'IT - 3rd Year';
}

$studentId   = $_SESSION['student_id'] ?? '1';
$studentName = $_SESSION['user_name'] ?? 'Ryan Jay Leoligao';
$courseYear  = $_SESSION['course_year'] ?? 'IT - 3rd Year';
$qrCodeUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($studentId);
$profilePhoto = $_SESSION['profile_photo'] ?? $_SESSION['borrower_photo'] ?? 'logo.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipmentName = trim($_POST['equipment_name'] ?? '');
    $returnDate    = trim($_POST['return_date'] ?? '');
    $returnTime    = trim($_POST['return_time'] ?? '');
    
    if (!empty($equipmentName) && !empty($returnDate) && !empty($returnTime)) {
        $returnDueCombined = $returnDate . ' ' . $returnTime;
        
        // Idagdag agad sa session array para lumitaw sa dashboard
        array_unshift($_SESSION['mock_borrowed'], [
            'student_id' => $studentId,
            'equipment_name' => $equipmentName,
            'borrow_date' => $returnDueCombined,
            'condition_status' => 'Needs Replacement'
        ]);
        
        $successMsg = "Matagumpay na naisumite ang request para sa kagamitan!";
    } else {
        $errorMsg = "Punan ang lahat ng kinakailangang impormasyon.";
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

    <div class="flex flex-grow">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-[#00381d] text-white flex flex-col p-6 hidden md:flex">
            <div class="flex flex-col items-center text-center mb-8">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white mb-3" onerror="this.src='https://via.placeholder.com/64'">
                <h1 class="text-xs font-bold uppercase tracking-wider">MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
            </div>

            <nav class="space-y-2 flex-grow">
                <a href="borrower.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
                    Dashboard
                </a>
                <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-emerald-800 text-white text-xs font-semibold shadow-sm">
                    Borrow an Item
                </a>
                <a href="borrower_history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
                    Returned Items / History
                </a>
            </nav>

            <div class="pt-4 border-t border-emerald-800/60">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-600/20 text-red-300 text-xs font-medium transition">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow p-6 lg:p-10 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4 w-full">
                    <div class="w-16 h-16 rounded-full border-2 border-emerald-600 overflow-hidden shadow-inner bg-white flex items-center justify-center flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Live Selfie" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/64'">
                    </div>
                    <div>
                        <p class="text-base font-bold text-gray-800"><?php echo htmlspecialchars($studentName); ?></p>
                        <p class="text-xs text-gray-500">Student ID: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($studentId); ?></span></p>
                        <p class="text-xs text-gray-500">Course / Year: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($courseYear); ?></span></p>
                    </div>
                </div>

                <div class="border border-gray-200 p-3 rounded-xl bg-white text-center shadow-sm flex flex-col items-center cursor-pointer group" onclick="openQrModal()">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="Student QR Code" class="w-24 h-24 object-contain rounded mb-1 group-hover:scale-105 transition">
                    <span class="text-[10px] text-gray-400 group-hover:text-emerald-700 font-semibold">Click to Zoom</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-sm font-bold text-gray-800 border-b pb-2">Mag-request ng Kagamitan (Borrow Equipment)</h2>
                
                <?php if (!empty($successMsg)): ?>
                    <div class="p-3 bg-emerald-50 text-emerald-700 text-xs rounded-xl font-medium"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                <?php if (!empty($errorMsg)): ?>
                    <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl font-medium border border-red-200"><?php echo $errorMsg; ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pangalan ng Kagamitan / Item</label>
                            <input type="text" name="equipment_name" required placeholder="Hal. Projector, HDMI" class="w-full px-4 py-2.5 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-emerald-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Petsa ng Pagbabalik (Return Date)</label>
                            <input type="date" name="return_date" required class="w-full px-4 py-2.5 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-emerald-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Oras ng Pagbabalik (Return Time)</label>
                            <input type="time" name="return_time" required class="w-full px-4 py-2.5 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-emerald-700">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition shadow-sm">
                            Isumite ang Request
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <!-- QR Zoom Modal -->
    <div id="qrModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4" onclick="closeQrModal()">
        <div class="bg-white p-6 rounded-2xl shadow-2xl text-center max-w-xs w-full flex flex-col items-center relative" onclick="event.stopPropagation()">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Student QR Code</h3>
            <p class="text-[11px] text-gray-500 mb-4">Ipakita ito sa Property Custodian para ma-scan.</p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="Large QR Code" class="w-56 h-56 object-contain border-2 border-emerald-600 rounded-xl p-2 mb-4">
            <button onclick="closeQrModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-xl text-xs font-semibold transition">Isara (Close)</button>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - MARY THE QUEEN COLLEGE OF QUEZON CITY
    </footer>

    <script>
        function openQrModal() {
            document.getElementById('qrModal').classList.remove('hidden');
        }
        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }
    </script>
</body>
</html>
