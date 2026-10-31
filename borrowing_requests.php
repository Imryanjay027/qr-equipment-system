<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basahin ang parehong shared JSON file para makita ng Custodian ang isinumite ng Borrower
$dbFile = __DIR__ . '/shared_requests.json';
$requests = [];
if (file_exists($dbFile)) {
    $requests = json_decode(file_get_contents($dbFile), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <!-- Top Header -->
    <header class="bg-[#1e293b] text-white px-8 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-sm font-bold uppercase tracking-wider">PROPERTY CUSTODIAN DASHBOARD</h1>
        <div class="flex items-center gap-4">
            <a href="custodian_dashboard.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">Back to Dashboard</a>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">Logout</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow p-8 space-y-6 max-w-7xl mx-auto w-full">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-800">Active Equipment Borrowing Requests & Monitoring</h2>
            
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <div class="grid grid-cols-7 bg-[#1e293b] text-white text-xs font-bold px-6 py-3.5 text-center">
                    <div>Selfie</div>
                    <div>Student ID</div>
                    <div>Full Name</div>
                    <div>Course & Year</div>
                    <div>Item to Borrow</div>
                    <div>Return Due</div>
                    <div>Status / Actions</div>
                </div>

                <?php if (!empty($requests)): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($requests as $req): ?>
                            <div class="grid grid-cols-7 px-6 py-4 text-xs text-gray-700 items-center text-center">
                                <div class="flex justify-center">
                                    <img src="<?php echo htmlspecialchars($req['profile_photo'] ?? 'logo.jpg'); ?>" alt="Selfie" class="w-10 h-10 rounded-full object-cover border border-gray-300" onerror="this.src='https://via.placeholder.com/40'">
                                </div>
                                <div class="font-semibold"><?php echo htmlspecialchars($req['student_id'] ?? '1'); ?></div>
                                <div><?php echo htmlspecialchars($req['full_name'] ?? 'Ryan Jay Leoligao'); ?></div>
                                <div><?php echo htmlspecialchars($req['course_year'] ?? 'BSIT 3rd Year'); ?></div>
                                <div class="font-medium text-emerald-800"><?php echo htmlspecialchars($req['equipment_name'] ?? 'N/A'); ?></div>
                                <div><?php echo htmlspecialchars($req['borrow_date'] ?? 'N/A'); ?></div>
                                <div class="flex flex-col gap-1 items-center justify-center">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-400 text-amber-900 shadow-sm">
                                        <?php echo htmlspecialchars($req['status'] ?? 'Pending'); ?>
                                    </span>
                                    <div class="flex gap-1 mt-1">
                                        <button onclick="alert('Marked as Good')" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold">Good</button>
                                        <button onclick="alert('Marked as Damaged')" class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white rounded text-[10px] font-semibold">Damaged</button>
                                        <button onclick="alert('Marked as Replaced')" class="px-2 py-0.5 bg-amber-600 hover:bg-amber-700 text-white rounded text-[10px] font-semibold">Replaced</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-8 text-center text-xs text-gray-400">
                        No active borrowing requests at the moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - MARY THE QUEEN COLLEGE OF QUEZON CITY
    </footer>
</body>
</html>
