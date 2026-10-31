<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$historyRecords = $_SESSION['mock_borrowed'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returned Items & History - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <div class="flex flex-grow">
        <!-- Sidebar Navigation -->
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
                    <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-800/40 text-emerald-100 text-xs font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Borrow an Item
                    </a>
                    <a href="borrower_history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#008040] text-white text-xs font-semibold shadow-sm">
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

            <!-- Tabs Section -->
            <div class="space-y-4">
                <div class="flex gap-2 border-b border-gray-200 pb-2">
                    <a href="borrower.php" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white text-gray-600 hover:bg-gray-100 transition border border-gray-200">My Borrowed Equipment</a>
                    <button class="px-4 py-2 rounded-xl text-xs font-bold bg-[#008040] text-white shadow-sm">Returned Items / History & Remarks</button>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="grid grid-cols-4 bg-[#008040] text-white text-xs font-bold px-6 py-3.5">
                        <div>Student ID</div>
                        <div>Equipment Name</div>
                        <div>Borrow Date / Due Date</div>
                        <div>Custodian Remark / Status</div>
                    </div>

                    <?php if (!empty($historyRecords)): ?>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($historyRecords as $row): ?>
                                <div class="grid grid-cols-4 px-6 py-4 text-xs text-gray-700 items-center">
                                    <div class="font-semibold"><?php echo htmlspecialchars($row['student_id'] ?? $studentId); ?></div>
                                    <div><?php echo htmlspecialchars($row['equipment_name'] ?? 'N/A'); ?></div>
                                    <div><?php echo htmlspecialchars($row['borrow_date'] ?? 'N/A'); ?></div>
                                    <div>
                                        <span class="px-3 py-1 rounded-md text-[11px] font-bold bg-amber-400 text-amber-900 shadow-sm">
                                            <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-xs text-gray-400">
                            No history or remarks from the custodian yet.
                        </div>
                    <?php endif; ?>
                </div>
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
        function openQrModal() { document.getElementById('qrModal').classList.remove('hidden'); }
        function closeQrModal() { document.getElementById('qrModal').classList.add('hidden'); }
    </script>
</body>
</html>
