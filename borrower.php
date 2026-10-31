<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection (Coolify Dynamic Config)
$host     = getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$port     = getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';
$db       = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'qr_equipment_db';
$user     = getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
$pass     = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    try {
        $dsnFallback = "mysql:host=127.0.0.1;port=$port;dbname=$db;charset=$charset";
        $pdo = new PDO($dsnFallback, $user, $pass, $options);
    } catch (\PDOException $e2) {
        $db_error = $e2->getMessage();
    }
}

// Session Defaults
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'Borrower';
    $_SESSION['user_name'] = 'Ryan Jay Leoligao';
    $_SESSION['student_id'] = '1';
    $_SESSION['course_year'] = 'IT - 3rd Year';
}

$studentId   = $_SESSION['student_id'] ?? '1';
$studentName = $_SESSION['user_name'] ?? 'Ryan Jay Leoligao';
$courseYear  = $_SESSION['course_year'] ?? 'IT - 3rd Year';
$qrCodeUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($studentId);
$profilePhoto = $_SESSION['profile_photo'] ?? $_SESSION['borrower_photo'] ?? 'logo.jpg';

// Dynamic Table Fetching para masigurong mahahanap ang data
$borrowedRecords = [];
if (isset($pdo)) {
    $tablesToTry = ['borrowing_requests', 'borrowed_items', 'transactions', 'requests'];
    foreach ($tablesToTry as $tbl) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `$tbl` ORDER BY id DESC");
            $stmt->execute();
            $fetched = $stmt->fetchAll();
            if (!empty($fetched)) {
                $borrowedRecords = $fetched;
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <div class="flex flex-grow">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-[#00381d] text-white flex flex-col p-6 hidden md:flex">
            <div class="flex flex-col items-center text-center mb-8">
                <img src="logo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white object-cover bg-white mb-3" onerror="this.src='https://via.placeholder.com/64'">
                <h1 class="text-xs font-bold uppercase tracking-wider">Mary the Queen College</h1>
                <p class="text-[10px] text-emerald-200">Quezon City</p>
            </div>

            <nav class="space-y-2 flex-grow">
                <a href="borrower.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-emerald-800 text-white text-xs font-semibold shadow-sm">
                    Dashboard
                </a>
                <a href="borrow_item.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
                    Borrow an Item
                </a>
                <a href="borrower_history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-emerald-800/50 text-emerald-100 text-xs font-medium transition">
                    Returned Items / History
                </a>
            </nav>

            <div class="pt-4 border-t border-emerald-800/60">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-600/20 text-red-300 text-xs font-medium transition">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow p-6 lg:p-10 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4 w-full">
                    <div class="w-16 h-16 rounded-full border-2 border-emerald-600 overflow-hidden shadow-inner bg-white flex items-center justify-center flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Live Selfie" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/64'">
                    </div>
                    <div>
                        <p class="text-base font-bold text-gray-800"><?php echo htmlspecialchars($studentName); ?></p>
                        <p class="text-xs text-gray-500">Student ID: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($studentId); ?></span></p>
                        <p class="text-xs text-gray-500">Course / Year: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($courseYear); ?></span></p>
                    </div>
                </div>

                <div class="border border-gray-200 p-3 rounded-xl bg-white text-center shadow-sm flex flex-col items-center cursor-pointer group" onclick="openQrModal()">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="Student QR Code" class="w-24 h-24 object-contain rounded mb-1 group-hover:scale-105 transition">
                    <span class="text-[10px] text-gray-400 group-hover:text-emerald-700 font-semibold">Click to Zoom</span>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="space-y-4">
                <div class="flex gap-2 border-b border-gray-200 pb-2 justify-between items-center">
                    <div class="flex gap-2">
                        <button class="px-4 py-2 rounded-xl text-xs font-bold bg-[#00381d] text-white shadow-sm">My Borrowed Equipment</button>
                        <a href="borrower_history.php" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white text-gray-600 hover:bg-gray-100 transition border border-gray-200">Returned Items / History & Remarks</a>
                    </div>
                    <a href="borrow_item.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-sm flex items-center gap-2">
                        + Borrow an Item
                    </a>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="grid grid-cols-4 bg-[#00381d] text-white text-xs font-bold px-6 py-3.5">
                        <div>Student ID</div>
                        <div>Equipment Name</div>
                        <div>Borrow Date / Due Date</div>
                        <div>Custodian Remark / Status</div>
                    </div>

                    <?php if (!empty($borrowedRecords)): ?>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($borrowedRecords as $row): ?>
                                <div class="grid grid-cols-4 px-6 py-4 text-xs text-gray-700 items-center">
                                    <div class="font-semibold"><?php echo htmlspecialchars($row['student_id'] ?? $row['user_id'] ?? $studentId); ?></div>
                                    <div><?php echo htmlspecialchars($row['equipment_name'] ?? $row['item_name'] ?? 'N/A'); ?></div>
                                    <div><?php echo htmlspecialchars($row['borrow_date'] ?? $row['created_at'] ?? 'N/A'); ?></div>
                                    <div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <?php echo htmlspecialchars($row['condition_status'] ?? $row['status'] ?? 'Needs Replacement'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-xs text-gray-400">
                            No history or remarks from the custodian yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- QR Zoom Modal -->
    <div id="qrModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4" onclick="closeQrModal()">
        <div class="bg-white p-6 rounded-2xl shadow-2xl text-center max-w-xs w-full flex flex-col items-center relative" onclick="event.stopPropagation()">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Student QR Code</h3>
            <p class="text-[11px] text-gray-500 mb-4">Ipakita ito sa Property Custodian para ma-scan.</p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="Large QR Code" class="w-56 h-56 object-contain border-2 border-emerald-600 rounded-xl p-2 mb-4">
            <button onclick="closeQrModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-xl text-xs font-semibold transition">Isara (Close)</button>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - Mary the Queen College of Quezon City
    </footer>

    <script>
        function openQrModal() {
            document.getElementById('qrModal').classList.remove('hidden');
        }
        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }
    </script>
</body>
</html>
