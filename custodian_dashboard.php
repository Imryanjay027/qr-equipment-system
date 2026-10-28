<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'custodian') {
    header("Location: login.php");
    exit();
}

$custodian_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'System Custodian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Panel - QR Equipment Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Top Navigation Bar with Hamburger -->
    <div class="md:hidden bg-[#005a2b] text-white p-4 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <img src="logo.jpg" alt="Logo" class="w-8 h-8 rounded-full border border-white object-cover bg-white" onerror="this.src='https://via.placeholder.com/30'">
            <span class="font-bold text-xs tracking-wider">CUSTODIAN PORTAL</span>
        </div>
        <button id="menu-btn" class="p-2 rounded-lg bg-[#004822] text-white focus:outline-none hover:bg-[#003b1c]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Collapsible Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-[#005a2b] text-white p-4 space-y-2 shadow-lg border-t border-emerald-800">
        <a href="custodian_dashboard.php" class="block py-2.5 px-4 rounded-lg bg-[#007a38] text-white font-medium text-sm">Dashboard</a>
        <a href="borrowing_requests.php" class="block py-2.5 px-4 rounded-lg hover:bg-[#004822] text-gray-200 text-sm transition">Borrowing Requests</a>
        <a href="scanner.php" class="block py-2.5 px-4 rounded-lg hover:bg-[#004822] text-gray-200 text-sm transition">QR Scanner Portal</a>
        <a href="logout.php" class="block py-2.5 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-sm text-center transition">Logout</a>
        <div class="pt-2 text-center border-t border-emerald-800">
            <p class="text-[10px] italic text-emerald-200">"Discipline today, excellence tomorrow."</p>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div class="hidden md:flex w-72 bg-[#005a2b] text-white flex-col justify-between p-6 shadow-lg min-h-screen">
        <div>
            <!-- School Logo & Header -->
            <div class="text-center mb-8">
                <img src="logo.jpg" alt="School Logo" class="w-20 h-20 mx-auto rounded-full border-2 border-white object-cover bg-white mb-3" onerror="this.src='https://via.placeholder.com/80'">
                <h2 class="text-xs font-bold tracking-wider leading-relaxed text-gray-100">MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-2">
                <a href="custodian_dashboard.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg bg-[#007a38] text-white font-medium shadow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>
                <a href="borrowing_requests.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-[#004822] text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Borrowing Requests</span>
                </a>
                <a href="scanner.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-[#004822] text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 16h1m-4-10V4m-4 8H4m12 0h4m-4 8h4m-8-4v4m0-8h4"></path></svg>
                    <span>QR Scanner Portal</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div>
            <div class="mb-6">
                <a href="logout.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-red-700 text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Logout</span>
                </a>
            </div>
            <div class="text-center border-t border-emerald-800 pt-4">
                <p class="text-[11px] italic text-emerald-200">"Discipline today, excellence tomorrow."</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-y-auto p-4 md:p-8">
        
        <!-- Top Bar Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">PROPERTY CUSTODIAN PANEL</h1>
                <p class="text-xs md:text-sm text-gray-500">Equipment Borrowing, Return & Monitoring System</p>
            </div>
            
            <div class="flex items-center space-x-3 w-full sm:w-auto justify-between sm:justify-start">
                <div class="flex items-center space-x-2 text-xs md:text-sm text-gray-700 bg-gray-50 px-3 py-1.5 rounded-lg border">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="font-medium"><?php echo htmlspecialchars($custodian_name); ?></span>
                </div>
                <span class="hidden sm:inline-block px-3 py-1 bg-emerald-600 text-white rounded-md text-xs font-semibold tracking-wider">CUSTODIAN</span>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition shadow-sm">Logout</a>
            </div>
        </div>

        <!-- Dashboard Card Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base md:text-lg font-bold text-gray-800 mb-4">Dashboard Overview</h2>
            
            <a href="borrowing_requests.php" class="block border border-gray-200 rounded-xl p-5 max-w-sm bg-white shadow-sm hover:border-emerald-600 hover:shadow-md transition">
                <p class="text-sm font-medium text-gray-500 mb-1">Total Active Requests</p>
                <h3 class="text-3xl font-bold text-emerald-700">1</h3>
                <span class="text-xs text-emerald-600 font-semibold mt-2 inline-block">View requests &rarr;</span>
            </a>
        </div>

    </div>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
