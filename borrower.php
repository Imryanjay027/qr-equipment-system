<?php
session_start();

$json_file = 'requests.json';

// Initialize requests.json kung wala pa
if (!file_exists($json_file)) {
    $initial_data = [
        [
            'id' => 1,
            'student_id' => 'admin',
            'full_name' => 'Ryan Jay Leoligao',
            'course_year' => 'BSIT 3rd Year',
            'item' => 'Extension',
            'borrow_date' => date('Y-m-d H:i:s'),
            'return_due' => date('Y-m-d H:i:s', strtotime('+4 hours')),
            'status' => 'Pending'
        ]
    ];
    file_put_contents($json_file, json_encode($initial_data, JSON_PRETTY_PRINT));
}

$requests = json_decode(file_get_contents($json_file), true);
$success_msg = "";

// Handle New Borrow Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_item'])) {
    $new_request = [
        'id' => time(),
        'student_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'MQCQC-2026-001',
        'full_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Ryan Jay Leoligao',
        'course_year' => 'BSIT 3rd Year',
        'item' => trim($_POST['item']),
        'borrow_date' => date('Y-m-d H:i:s'),
        'return_due' => trim($_POST['return_due']),
        'status' => 'Pending'
    ];

    $requests[] = $new_request;
    file_put_contents($json_file, json_encode($requests, JSON_PRETTY_PRINT));
    $success_msg = "Matagumpay na naisumite ang borrowing request!";
    
    // Refresh data
    $requests = json_decode(file_get_contents($json_file), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - QR Equipment Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <div class="bg-[#005a2b] text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-sm md:text-base font-bold tracking-wider">BORROWER PORTAL - MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-xs font-semibold transition">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 md:p-10 max-w-6xl mx-auto w-full space-y-6">
        
        <?php if (!empty($success_msg)): ?>
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Form para humiram ng equipment -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-slate-800 mb-4">Mag-request ng Kagamitan (Borrow Equipment)</h2>
            <form action="borrower.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pangalan ng Kagamitan / Item</label>
                    <input type="text" name="item" required placeholder="Hal. Projector, HDMI, Extension" class="w-full text-sm border rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Petsa at Oras ng Pagbabalik (Return Due)</label>
                    <input type="datetime-local" name="return_due" required class="w-full text-sm border rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-600">
                </div>
                <div class="flex items-end">
                    <button type="submit" name="borrow_item" class="w-full bg-[#005a2b] hover:bg-[#004822] text-white py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                        Isumite ang Request
                    </button>
                </div>
            </form>
        </div>

        <!-- Listahan ng Active Requests ng Estudyante -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-slate-800 mb-4">Aking mga Hiniram at Status</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase">
                            <th class="p-3 rounded-l-lg">Item</th>
                            <th class="p-3">Borrow Date</th>
                            <th class="p-3">Return Due</th>
                            <th class="p-3 rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="4" class="p-4 text-center text-gray-500 italic">Walang kasalukuyang request.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-medium text-slate-800"><?php echo htmlspecialchars($req['item']); ?></td>
                                <td class="p-3 text-slate-600"><?php echo htmlspecialchars($req['borrow_date']); ?></td>
                                <td class="p-3 text-slate-600"><?php echo htmlspecialchars($req['return_due']); ?></td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded text-xs font-bold <?php echo ($req['status'] === 'Pending') ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'; ?>">
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

    </div>
</body>
</html>
