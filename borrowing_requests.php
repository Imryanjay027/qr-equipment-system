<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connection.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $actionId = $_GET['id'];
    $selectedAction = $_GET['action'];

    if ($selectedAction === 'Good') {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'Good', hidden_for_custodian = 1 WHERE id = ?");
        $stmt->execute([$actionId]);
    } elseif ($selectedAction === 'Replaced') {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'Needs Replacement', hidden_for_custodian = 1 WHERE id = ?");
        $stmt->execute([$actionId]);
    } elseif ($selectedAction === 'Damaged') {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'Damaged' WHERE id = ?");
        $stmt->execute([$actionId]);
    }
    
    header("Location: borrowing_requests.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM requests WHERE hidden_for_custodian = 0 ORDER BY id DESC");
$activeRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <header class="bg-[#1e293b] text-white px-4 sm:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-md">
        <h1 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-center sm:text-left">PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
        <div class="flex items-center gap-3">
            <a href="custodian_dashboard.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition whitespace-nowrap">Back to Dashboard</a>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition whitespace-nowrap">Logout</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow p-4 sm:p-8 space-y-6 max-w-7xl mx-auto w-full">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-800">Active Equipment Borrowing Requests & Monitoring</h2>
            
            <!-- Responsive Table Container -->
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-xs text-center border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-[#1e293b] text-white font-bold">
                            <th class="py-3.5 px-3">Selfie</th>
                            <th class="py-3.5 px-3">Student ID</th>
                            <th class="py-3.5 px-3">Full Name</th>
                            <th class="py-3.5 px-3">Course & Year</th>
                            <th class="py-3.5 px-3">Item to Borrow</th>
                            <th class="py-3.5 px-3">Return Due</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-4 text-center">Actions / Monitoring</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                        <?php if (!empty($activeRequests)): ?>
                            <?php foreach ($activeRequests as $req): ?>
                                <?php 
                                    $photo = htmlspecialchars($req['profile_photo'] ?? 'logo.jpg'); 
                                    $rawDate = $req['borrow_date'] ?? '';
                                    $dateParts = explode(' / ', $rawDate);
                                    $returnDue = $dateParts[1] ?? $rawDate;
                                ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3">
                                        <div class="flex justify-center">
                                            <img src="<?php echo $photo; ?>" alt="Selfie" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-600 cursor-pointer hover:scale-110 transition shadow-sm" onclick="openSelfieModal('<?php echo $photo; ?>')" onerror="this.src='https://via.placeholder.com/40'" title="Click to zoom">
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 font-semibold whitespace-nowrap"><?php echo htmlspecialchars($req['student_id'] ?? '1'); ?></td>
                                    <td class="py-3 px-3 whitespace-nowrap"><?php echo htmlspecialchars($req['full_name'] ?? 'Rjay Cabasag'); ?></td>
                                    <td class="py-3 px-3 whitespace-nowrap"><?php echo htmlspecialchars($req['course_year'] ?? 'IT - 3rd Year'); ?></td>
                                    <td class="py-3 px-3 font-medium text-emerald-800 whitespace-nowrap"><?php echo htmlspecialchars($req['equipment_name'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-3 whitespace-nowrap text-[11px] text-gray-600 font-medium"><?php echo htmlspecialchars($returnDue); ?></td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 shadow-sm inline-block">
                                            <?php echo htmlspecialchars($req['status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="borrowing_requests.php?action=Good&id=<?php echo $req['id']; ?>" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition shadow-sm">Good</a>
                                            <a href="borrowing_requests.php?action=Damaged&id=<?php echo $req['id']; ?>" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-semibold transition shadow-sm">Damaged</a>
                                            <a href="borrowing_requests.php?action=Replaced&id=<?php echo $req['id']; ?>" class="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-amber-950 rounded text-xs font-semibold transition shadow-sm">Replaced</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-xs text-gray-400">
                                    No active borrowing requests at the moment.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Selfie Zoom Modal -->
    <div id="selfieModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4" onclick="closeSelfieModal()">
        <div class="bg-white p-6 rounded-2xl shadow-2xl text-center max-w-sm w-full flex flex-col items-center relative" onclick="event.stopPropagation()">
            <h3 class="text-sm font-bold text-gray-800 mb-3">Borrower Selfie</h3>
            <img id="modalImage" src="" alt="Zoomed Selfie" class="w-64 h-64 object-cover border-2 border-emerald-600 rounded-2xl shadow-inner mb-4">
            <button onclick="closeSelfieModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-xl text-xs font-semibold transition">Isara (Close)</button>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - MARY THE QUEEN COLLEGE OF QUEZON CITY
    </footer>

    <script>
        function openSelfieModal(photoSrc) {
            document.getElementById('modalImage').src = photoSrc;
            document.getElementById('selfieModal').classList.remove('hidden');
        }
        function closeSelfieModal() {
            document.getElementById('selfieModal').classList.add('hidden');
        }
    </script>
</body>
</html>
