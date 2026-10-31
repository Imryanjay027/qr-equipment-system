<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$jsonFile = __DIR__ . '/requests.json';

function getRequests($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    return $_SESSION['global_borrow_requests'] ?? [];
}

function saveRequests($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    $_SESSION['global_borrow_requests'] = $data;
}

$requests = getRequests($jsonFile);

if (isset($_GET['action']) && isset($_GET['id'])) {
    $actionId = $_GET['id'];
    $selectedAction = $_GET['action'];

    foreach ($requests as $key => $req) {
        if (($req['id'] ?? '') == $actionId) {
            if ($selectedAction === 'Good') {
                $requests[$key]['status'] = 'Good';
                $requests[$key]['hidden_for_custodian'] = true;
            } elseif ($selectedAction === 'Replaced') {
                $requests[$key]['status'] = 'Needs Replacement';
                $requests[$key]['hidden_for_custodian'] = true;
            } elseif ($selectedAction === 'Damaged') {
                $requests[$key]['status'] = 'Damaged';
            }
            break;
        }
    }
    saveRequests($jsonFile, $requests);
    header("Location: borrowing_requests.php");
    exit;
}

$activeRequests = array_filter($requests, function($r) {
    return empty($r['hidden_for_custodian']);
});
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
                <div class="grid grid-cols-7 bg-[#1e293b] text-white text-xs font-bold px-6 py-3.5 text-center items-center">
                    <div>Selfie</div>
                    <div>Student ID</div>
                    <div>Full Name</div>
                    <div>Course & Year</div>
                    <div>Item to Borrow</div>
                    <div>Return Due</div>
                    <div>Status / Actions</div>
                </div>

                <?php if (!empty($activeRequests)): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($activeRequests as $req): ?>
                            <div class="grid grid-cols-7 px-6 py-4 text-xs text-gray-700 items-center text-center">
                                <div class="flex justify-center">
                                    <img src="<?php echo htmlspecialchars($req['profile_photo'] ?? 'logo.jpg'); ?>" alt="Selfie" class="w-10 h-10 rounded-full object-cover border border-gray-300" onerror="this.src='https://via.placeholder.com/40'">
                                </div>
                                <div class="font-semibold"><?php echo htmlspecialchars($req['student_id'] ?? '1'); ?></div>
                                <div><?php echo htmlspecialchars($req['full_name'] ?? 'Rjay Cabasag'); ?></div>
                                <div><?php echo htmlspecialchars($req['course_year'] ?? 'IT - 3rd Year'); ?></div>
                                <div class="font-medium text-emerald-800"><?php echo htmlspecialchars($req['equipment_name'] ?? 'N/A'); ?></div>
                                <div><?php echo htmlspecialchars($req['borrow_date'] ?? 'N/A'); ?></div>
                                
                                <!-- Inayos na flex container para maging maayos ang spacing at alignment ng status at mga action buttons -->
                                <div class="flex flex-col items-center justify-center gap-2.5 py-2 w-full">
                                    <span class="px-3 py-1 rounded-md text-[11px] font-bold bg-amber-400 text-amber-900 shadow-sm inline-block">
                                        <?php echo htmlspecialchars($req['status'] ?? 'Pending'); ?>
                                    </span>
                                    <div class="flex items-center justify-center gap-1.5 w-full">
                                        <a href="borrowing_requests.php?action=Good&id=<?php echo $req['id']; ?>" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold transition shadow-sm">Good</a>
                                        <a href="borrowing_requests.php?action=Damaged&id=<?php echo $req['id']; ?>" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-[10px] font-semibold transition shadow-sm">Damaged</a>
                                        <a href="borrowing_requests.php?action=Replaced&id=<?php echo $req['id']; ?>" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-[10px] font-semibold transition shadow-sm">Replaced</a>
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
