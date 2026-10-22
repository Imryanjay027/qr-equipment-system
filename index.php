// Handle scanned student_id if passed via URL
$borrower_info = null;
$borrowed_items = [];
if (isset($_GET['student_id'])) {
    $raw_student_id = trim($_GET['student_id']);
    
    // Kung ang QR ay may format na "MQCQC-ID: 123456 | Name: ...", kinuha natin ang ID
    $student_id = $raw_student_id;
    if (strpos($raw_student_id, 'MQCQC-ID:') !== false) {
        preg_match('/MQCQC-ID:\s*([^|]+)/i', $raw_student_id, $matches);
        if (isset($matches[1])) {
            $student_id = trim($matches[1]);
        }
    }

    // Kunin ang details ng estudyante gamit ang nalinis na Student ID
    $stmt = $conn->prepare("SELECT * FROM borrowers WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $borrower_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // Kunin ang hiniram na kagamitan at status (Good/Damaged/Replaced)
    $stmt_eq = $conn->prepare("SELECT * FROM borrowed_equipment WHERE student_id = ? ORDER BY id DESC");
    if ($stmt_eq) {
        $stmt_eq->bind_param("s", $student_id);
        $stmt_eq->execute();
        $borrowed_items = $stmt_eq->get_result();
        $stmt_eq->close();
    }
}
