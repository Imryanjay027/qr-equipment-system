// Kunin ang mga active borrow requests ng estudyante (para makita ang hiniram, selfie, at status)
    if ($borrower_data) {
        $b_name = $borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? '');
        $b_sid = $borrower_data['student_id'] ?? $student_id;
        
        $req_stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE (student_id = ? OR full_name = ?) AND status != 'Cleared' AND status != 'Replaced' ORDER BY id DESC");
        if ($req_stmt) {
            $req_stmt->bind_param("ss", $b_sid, $b_name);
            $req_stmt->execute();
            $borrowed_items = $req_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $req_stmt->close();
        }
    }
