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
    <header class="bg-[#1e293b] text-white px-8 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-sm font-bold uppercase tracking-wider">PROPERTY CUSTODIAN DASHBOARD - MARY THE QUEEN COLLEGE OF QUEZON CITY</h1>
        <div class="flex items-center gap-4">
            <a href="qr_scanner.php" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">QR Scanner Portal</a>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">Logout (<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Ryan Jay Leoligao'); ?>)</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow p-8 space-y-6 max-w-7xl mx-auto w-full">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-800">Active Equipment Borrowing Requests & Monitoring</h2>
            
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <div class="grid grid-cols-8 bg-[#1e293b] text-white text-xs font-bold px-6 py-3.5 text-center items-center">
                    <div>Selfie</div>
                    <div>Student ID</div>
                    <div>Full Name</div>
                    <div>Course & Year</div>
                    <div>Item to Borrow</div>
                    <div>Return Due</div>
                    <div>Status</div>
                    <div>Actions / Condition Monitoring</div>
                </div>

                <?php if (!empty($activeRequests)): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($activeRequests as $req): ?>
                            <?php $photo = htmlspecialchars($req['profile_photo'] ?? 'logo.jpg'); ?>
                            <div class="grid grid-cols-8 px-6 py-4 text-xs text-gray-700 items-center text-center">
                                <!-- Click to Zoom Selfie -->
                                <div class="flex justify-center">
                                    <img src="<?php echo $photo; ?>" alt="Selfie" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-600 cursor-pointer hover:scale-110 transition shadow-sm" onclick="openSelfieModal('<?php echo $photo; ?>')" onerror="this.src='https://via.placeholder.com/40'" title="Click to zoom">
                                </div>
                                <div class="font-semibold"><?php echo htmlspecialchars($req['student_id'] ?? '1'); ?></div>
                                <div><?php echo htmlspecialchars($req['full_name'] ?? 'Rjay Cabasag'); ?></div>
                                <div><?php echo htmlspecialchars($req['course_year'] ?? 'IT - 3rd Year'); ?></div>
                                <div class="font-medium text-emerald-800"><?php echo htmlspecialchars($req['equipment_name'] ?? 'N/A'); ?></div>
                                <div class="text-[11px]"><?php echo htmlspecialchars($req['borrow_date'] ?? 'N/A'); ?></div>
                                
                                <!-- Status Column -->
                                <div class="flex justify-center">
                                    <span class="px-3 py-1 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 shadow-sm inline-block">
                                        <?php echo htmlspecialchars($req['status'] ?? 'Pending'); ?>
                                    </span>
                                </div>

                                <!-- Actions Column (Pahalang na nakahanay) -->
                                <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                    <a href="borrowing_requests.php?action=Good&id=<?php echo $req['id']; ?>" class="px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold transition shadow-sm whitespace-nowrap">Mark Good</a>
                                    <a href="borrowing_requests.php?action=Damaged&id=<?php echo $req['id']; ?>" class="px-2 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-[10px] font-semibold transition shadow-sm whitespace-nowrap">Mark Damaged</a>
                                    <a href="borrowing_requests.php?action=Replaced&id=<?php echo $req['id']; ?>" class="px-2 py-1.5 bg-amber-400 hover:bg-amber-500 text-amber-950 rounded text-[10px] font-semibold transition shadow-sm whitespace-nowrap">Mark Replaced</a>
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
