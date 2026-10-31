<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'Borrower';
    $_SESSION['user_name'] = 'Ryan Jay Leoligao';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <div class="flex flex-grow">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-[#00381d] text-white flex flex-col p-6 hidden md:flex">
            <div class="flex flex-col items-center text-center mb-8">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white mb-3" onerror="this.src='https://via.placeholder.com/64'">
                <h1 class="text-xs font-bold uppercase tracking-wider">Mary the Queen College</h1>
                <p class="text-[10px] text-emerald-200">Quezon City</p>
            </div>

            <nav class="space-y-2 flex-grow">
                <a href="borrower.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-emerald-800 text-white text-xs font-semibold shadow-sm">
                    Dashboard
                </a>
                <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
                    Borrow an Item
                </a>
                <a href="returned_history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
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
            
            <!-- Student Profile & QR Card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4 w-full">
                    <div class="w-16 h-16 rounded-full bg-emerald-700 flex items-center justify-center text-white text-2xl font-bold shadow-inner">
                        👤
                    </div>
                    <div>
                        <p class="text-base font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-xs text-gray-500">Student ID: <span class="font-semibold text-gray-700">1</span></p>
                        <p class="text-xs text-gray-500">Course / Year: <span class="font-semibold text-gray-700">IT - 3rd Year</span></p>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="border border-gray-200 p-3 rounded-xl bg-white text-center shadow-sm flex flex-col items-center">
                    <div class="w-24 h-24 bg-gray-900 text-white flex items-center justify-center text-[10px] rounded mb-1">
                        [QR CODE]
                    </div>
                    <span class="text-[10px] text-gray-400">Click to Zoom</span>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="space-y-4">
                <div class="flex gap-2 border-b border-gray-200 pb-2">
                    <button class="px-4 py-2 rounded-xl text-xs font-bold bg-[#00381d] text-white shadow-sm">My Borrowed Equipment</button>
                    <a href="returned_history.php" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white text-gray-600 hover:bg-gray-100 transition border border-gray-200">Returned Items / History & Remarks</a>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="grid grid-cols-4 bg-[#00381d] text-white text-xs font-bold px-6 py-3.5">
                        <div>Student ID</div>
                        <div>Equipment Name</div>
                        <div>Borrow Date / Due Date</div>
                        <div>Custodian Remark / Status</div>
                    </div>

                    <div class="p-8 text-center text-xs text-gray-400">
                        No history or remarks from the custodian yet.
                    </div>
                </div>

                <!-- Action Button para sa Borrow an Item kung walang hiniram -->
                <div class="flex justify-end pt-2">
                    <a href="borrow_item.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition shadow-sm flex items-center gap-2">
                        + Borrow an Item
                    </a>
                </div>
            </div>

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - Mary the Queen College of Quezon City
    </footer>

</body>
</html>
