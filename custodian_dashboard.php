<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'custodian') {
    header("Location: login.php");
    exit();
}

$custodian_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Custodian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard - QR Equipment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-cover bg-center flex items-center justify-center relative" style="background-image: url('school_bg.jpg');">
    
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Main Wrapper -->
    <div class="relative z-10 w-full max-w-6xl mx-auto p-3 md:p-6">
        
        <!-- Mobile Top Navbar with Hamburger -->
        <div class="md:hidden bg-gray-900 text-white p-4 rounded-xl flex justify-between items-center mb-4 shadow-lg">
            <span class="font-bold text-sm tracking-wider">CUSTODIAN PORTAL</span>
            <button id="menu-btn" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Collapsible Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-gray-900 text-white p-4 rounded-xl mb-4 space-y-2 shadow-xl">
            <a href="custodian_dashboard.php" class="block py-2 px-3 rounded-lg bg-blue-600 text-white text-sm font-medium">Dashboard</a>
            <a href="#" class="block py-2 px-3 rounded-lg hover:bg-gray-800 text-gray-300 text-sm">Manage Equipment</a>
            <a href="#" class="block py-2 px-3 rounded-lg hover:bg-gray-800 text-gray-300 text-sm">Scan QR Code</a>
            <a href="#" class="block py-2 px-3 rounded-lg hover:bg-gray-800 text-gray-300 text-sm">Transaction Logs</a>
            <a href="logout.php" class="block py-2 px-3 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm text-center font-medium mt-2">Logout</a>
        </div>

        <!-- Desktop & Main Layout Container -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-4 min-h-[550px]">
            
            <!-- Desktop Sidebar (Hidden on Mobile) -->
            <div class="hidden md:flex bg-gray-900 text-white p-6 flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-8">
                        <span class="text-xl font-bold tracking-wider">CUSTODIAN PORTAL</span>
                    </div>
                    <nav class="space-y-3">
                        <a href="custodian_dashboard.php" class="block py-2.5 px-4 rounded-lg bg-blue-600 text-white font-medium transition">Dashboard</a>
                        <a href="#" class="block py-2.5 px-4 rounded-lg hover:bg-gray-800 text-gray-300 transition">Manage Equipment</a>
                        <a href="#" class="block py-2.5 px-4 rounded-lg hover:bg-gray-800 text-gray-300 transition">Scan QR Code</a>
                        <a href="#" class="block py-2.5 px-4 rounded-lg hover:bg-gray-800 text-gray-300 transition">Transaction Logs</a>
                    </nav>
                </div>
                <div>
                    <a href="logout.php" class="block text-center py-2.5 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition">Logout</a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="md:col-span-3 p-5 md:p-10 flex flex-col justify-between">
                <div>
                    <header class="flex justify-between items-center border-b pb-4 mb-6">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Equipment Monitoring</h1>
                            <p class="text-xs md:text-sm text-gray-500">Welcome back, <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($custodian_name); ?></span>!</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active Session</span>
                    </header>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl shadow-sm">
                            <p class="text-xs md:text-sm text-blue-600 font-medium">Total Equipment</p>
                            <h3 class="text-2xl font-bold text-blue-900 mt-1">--</h3>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl shadow-sm">
                            <p class="text-xs md:text-sm text-amber-600 font-medium">Currently Borrowed</p>
                            <h3 class="text-2xl font-bold text-amber-900 mt-1">--</h3>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl shadow-sm">
                            <p class="text-xs md:text-sm text-emerald-600 font-medium">Available</p>
                            <h3 class="text-2xl font-bold text-emerald-900 mt-1">--</h3>
                        </div>
                    </div>

                    <!-- Main Panel Notice -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 md:p-6 text-center">
                        <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-2">QR Code Scanning System Ready</h3>
                        <p class="text-xs md:text-sm text-gray-500 mb-4">Gamitin ang scanner o i-monitor ang mga hiniram at isinasauling kagamitan ng paaralan.</p>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition shadow">Simulan ang Pag-scan</button>
                    </div>
                </div>

                <footer class="text-center text-xs text-gray-400 mt-6 pt-4 border-t">
                    Mary the Queen College of Quezon City &copy; 2026 - Equipment Monitoring System
                </footer>
            </div>

        </div>
    </div>

    <!-- Toggle Script para sa Hamburger Menu -->
    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
