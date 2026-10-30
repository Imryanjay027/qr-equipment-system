<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Siguraduhing naka-login ang user at hindi custodian
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) === 'custodian') {
    header("Location: login.php");
    exit();
}

// Session-based storage para sa equipment requests para iwas 'Permission Denied' error sa cloud
if (!isset($_SESSION['equipment_requests'])) {
    $_SESSION['equipment_requests'] = [];
}

$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'])) {
    $new_request = [
        'borrower' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest Borrower',
        'item_name' => trim($_POST['item_name']),
        'return_due' => trim($_POST['return_due']),
        'date' => date('Y-m-d H:i:s'),
        'status' => 'Pending'
    ];
    
    // Idagdag sa unahan ng listahan
    array_unshift($_SESSION['equipment_requests'], $new_request);
    $success_msg = "Matagumpay na naisumite ang iyong request sa kagamitan!";
}

$requests = $_SESSION['equipment_requests'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - QR Equipment Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <!-- Header Navigation -->
    <header class="bg-[#00381d] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 rounded-full border border-white object-cover bg-white" onerror="this.src='https://via.placeholder.com/40'">
                <div>
                    <h1 class="text-sm sm:text-base font-bold tracking-tight">BORROWER PORTAL</h1>
                    <p class="text-[11px] text-emerald-200">Mary the Queen College of Quezon City</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-semibold"><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'); ?></p>
                    <p class="text-[10px] text-emerald-300 uppercase"><?php echo htmlspecialchars(isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Borrower'); ?></p>
                </div>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-sm">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow space-y-6">

        <?php if (!empty($success_msg)): ?>
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-sm">
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Borrow Request Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
            <h2 class="text-base font-bold text-gray-800 mb-1">Mag-request ng Kagamitan (Borrow Equipment)</h2>
            <p class="text-xs text-gray-500 mb-6">Punan ang detalye sa ibaba para humiram ng kagamitan sa paaralan.</p>

            <form action="borrower.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pangalan ng Kagamitan / Item</label>
                    <input type="text" name="item_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white" placeholder="Hal. Projector, HDMI, Extension Wire">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Petsa at Oras ng Pagbabalik (Return Due)</label>
                    <input type="datetime-local" name="return_due" required class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:border-emerald-600 bg-white">
                </div>
                <div class="md:col-span-2 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#005a2b] hover:bg-[#004822] text-white px-6 py-2.5 rounded-lg text-xs font-semibold transition shadow-md">
                        Isumite ang Request
                    </button>
                </div>
            </form>
        </div>

        <!-- Requests History Table -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 overflow-hidden">
            <h3 class="text-base font-bold text-gray-800 mb-4">Kasaysayan ng mga Request (My Borrow Requests)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                            <th class="p-3">Pangalan ng Humiram</th>
                            <th class="p-3">Kagamitan</th>
                            <th class="p-3">Petsa ng Request</th>
                            <th class="p-3">Return Due</th>
                            <th class="p-3">Katayuan (Status)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-400 italic">Wala pang naisumiteng request sa ngayon.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td class="p-3 font-medium"><?php echo htmlspecialchars($req['borrower']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($req['item_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($req['date']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($req['return_due']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <?php echo htmlspecialchars($req['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500">
        QR-Based Equipment Monitoring System &copy; 2026 - Mary the Queen College of Quezon City
    </footer>

</body>
</html>
