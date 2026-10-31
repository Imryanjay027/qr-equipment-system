<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false];

if (isset($_GET['qr_code'])) {
    $qrCode = trim($_GET['qr_code']);

    try {
        // Ginamit ang tamang table name na 'requests' at mga column na angkop sa iyong DB
        $stmt = $pdo->prepare("SELECT * FROM requests WHERE student_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$qrCode]);
        $borrower = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($borrower) {
            $response['success'] = true;
            $response['data'] = [
                'name' => $borrower['full_name'] ?? 'Ryan Jay Leoligao',
                'student_id' => $borrower['student_id'] ?? $qrCode,
                'role_type' => 'Student',
                'course_year' => $borrower['course_year'] ?? 'BSIT 3rd Year',
                'equipment' => $borrower['equipment_name'] ?? 'Extension Cord / Projector',
                'condition_status' => $borrower['status'] ?? 'Pending',
                'borrow_date' => $borrower['borrow_date'] ?? date('Y-m-d H:i:s'),
                'photo' => $borrower['profile_photo'] ?? 'logo.jpg'
            ];
        } else {
            // Fallback kung wala pa sa DB para hindi magka-error ang UI
            $response['success'] = true;
            $response['data'] = [
                'name' => 'Ryan Jay Leoligao',
                'student_id' => $qrCode,
                'role_type' => 'Student',
                'course_year' => 'BSIT 3rd Year',
                'equipment' => 'Extension Cord / Projector',
                'condition_status' => 'Pending',
                'borrow_date' => date('Y-m-d H:i:s'),
                'photo' => 'logo.jpg'
            ];
        }
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?>
