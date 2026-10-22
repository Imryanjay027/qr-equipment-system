// Kunin ang mga active borrow requests ng estudyante nang mas malawak ang paghahanap para matiyak na lilitaw ang mga item
    if ($borrower_data) {
        $b_name = $borrower_data['full_name'] ?? ($borrower_data['fullname'] ?? '');
        $b_sid = $borrower_data['student_id'] ?? $student_id;
        $b_id = $borrower_data['id'] ?? '';
        
        $req_stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE (student_id = ? OR full_name = ? OR student_id = ? OR student_id = ?) AND status != 'Cleared' AND status != 'Replaced' ORDER BY id DESC");
        if ($req_stmt) {
            $req_stmt->bind_param("ssss", $b_sid, $b_name, $b_id, $student_id);
            $req_stmt->execute();
            $borrowed_items = $req_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $req_stmt->close();
        }
    }
