<?php
session_start();

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'custodian' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$custodian_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'System Custodian';

if (!isset($_SESSION['borrow_requests'])) {
    $_SESSION['borrow_requests'] = [
        [
            'id' => 1,
            'student_id' => 'admin',
            'full_name' => 'Ryan Jay Leoligao',
            'course_year' => 'BSIT 3rd Year',
            'item' => 'Extension',
            'return_due' => '2026-10-20 12:30:00',
            'status' => 'Pending'
        ]
    ];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = $_GET['id'];
    $action_type = $_GET['action'];

    foreach ($_SESSION['borrow_requests'] as $key => $req) {
        if ($req['id'] == $action_id) {
            if ($action_type === 'good' || $action_type === 'replaced') {
                unset($_SESSION['borrow_requests'][$key]);
            } elseif ($action_type === 'damaged') {
                $_SESSION['borrow_requests'][$key]['status'] = 'Damaged / Needs Replacement';
            }
        }
    }
    $_SESSION['borrow_requests'] = array_values($_SESSION['borrow_requests']);
    header("Location: borrowing_requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Custodian Dashboard - Mary the Queen College of Quezon City</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <div class="bg-[#0f172a] text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-sm md:text-base font-bold tracking-wide">PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
        <div class="flex items-center space-x-3">
            <a href="custodian_dashboard.php" class="bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded text-xs font-semibold transition">Back to Dashboard</a>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-xs font-semibold transition">Logout (<?php echo htmlspecialchars($custodian_name); ?>)</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-6 md:p-10 max-w-7xl mx-auto w-full">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
            <h2 class="text-base md:text-lg font-semibold text-slate-800 mb-6">Active Equipment Borrowing Requests & Monitoring</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[950px]">
                    <thead>
                        <tr class="bg-[#0f172a] text-white text-xs uppercase tracking-wider">
                            <th class="p-3.5 rounded-l-lg">Selfie</th>
                            <th class="p-3.5">Student ID</th>
                            <th class="p-3.5">Full Name</th>
                            <th class="p-3.5">Course & Year</th>
                            <th class="p-3.5">Item to Borrow</th>
                            <th class="p-3.5">Return Due</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 rounded-r-lg text-right">Actions / Condition Monitoring</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php if (empty($_SESSION['borrow_requests'])): ?>
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-500 italic">Walang aktibong borrowing requests sa ngayon.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($_SESSION['borrow_requests'] as $row): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5">
                                    <img src="logo.jpg" alt="Selfie" class="w-10 h-10 rounded-full object-cover border border-slate-300" onerror="this.src='https://via.placeholder.com/40'">
                                </td>
                                <td class="p-3.5 font-medium text-slate-700"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td class="p-3.5 font-medium text-slate-800"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="p-3.5 text-slate-600"><?php echo htmlspecialchars($row['course_year']); ?></td>
                                <td class="p-3.5 text-slate-600"><?php echo htmlspecialchars($row['item']); ?></td>
                                <td class="p-3.5 text-slate-600"><?php echo htmlspecialchars($row['return_due']); ?></td>
                                <td class="p-3.5">
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-md text-xs font-bold">Pending</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-md text-xs font-bold"><?php echo htmlspecialchars($row['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3.5 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="borrowing_requests.php?action=good&id=<?php echo $row['id']; ?>" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition shadow-sm whitespace-nowrap">Mark Good</a>
                                        <a href="borrowing_requests.php?action=damaged&id=<?php echo $row['id']; ?>" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold transition shadow-sm whitespace-nowrap">Mark Damaged</a>
                                        <a href="borrowing_requests.php?action=replaced&id=<?php echo $row['id']; ?>" class="px-2.5 py-1 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded text-xs font-bold transition shadow-sm whitespace-nowrap">Mark Replaced</a>
                                    </div>
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
