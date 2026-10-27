<?php
session_start();

// Siguraduhing naka-login ang custodian at tama ang role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'custodian') {
    header("Location: login.php");
    exit();
}

$custodian_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Custodian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard - QR Equipment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-cover bg-center flex items-center justify-center" style="background-image: url('school_bg.jpg');">
    
    <!-- Overlay para luminaw ang basahin -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-4 min-h-[550px]">
            
            <!-- Sidebar Navigation -->
            <div class="bg-gray-900 text-white p-6 flex flex-col justify-between">
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
            <div class="md:col-span-3 p-6 md:p-10 flex flex-col justify-between">
                <div>
                    <header class="flex justify-between items-center border-b pb-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Equipment Monitoring</h1>
                            <p class="text-sm text-gray-500">Welcome back, <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($custodian_name); ?></span>!</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active Session</span>
                    </header>

                    <!-- Quick Stats / Overview Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl shadow-sm">
                            <p class="text-sm text-blue-600 font-medium">Total Equipment</p>
                            <h3 class="text-2xl font-bold text-blue-900 mt-1">--</h3>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl shadow-sm">
                            <p class="text-sm text-amber-600 font-medium">Currently Borrowed</p>
                            <h3 class="text-2xl font-bold text-amber-900 mt-1">--</h3>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl shadow-sm">
                            <p class="text-sm text-emerald-600 font-medium">Available</p>
                            <h3 class="text-2xl font-bold text-emerald-900 mt-1">--</h3>
                        </div>
                    </div>

                    <!-- Main Panel Notice -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">QR Code Scanning System Ready</h3>
                        <p class="text-sm text-gray-500 mb-4">Gamitin ang scanner o i-manok ang pag-monitor ng mga hiniram at isinasauling kagamitan ng paaralan.</p>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition shadow">Simulan ang Pag-scan</button>
                    </div>
                </div>

                <footer class="text-center text-xs text-gray-400 mt-6 pt-4 border-t">
                    Mary the Queen College of Quezon City &copy; 2026 - Equipment Monitoring System
                </footer>
            </div>

        </div>
    </div>

</body>
</html>
