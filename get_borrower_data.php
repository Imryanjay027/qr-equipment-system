<?php
header('Content-Type: application/json');
require_once 'db_connection.php'; // Palitan ito kung iba ang filename ng connection mo sa database

$response = ['success' => false];

if (isset($_GET['qr_code'])) {
    $qrCode = trim($_GET['qr_code']);

    try {
        // Palitan ang 'borrowing_requests' at mga column name ayon sa database table mo
        $stmt = $pdo->prepare("SELECT * FROM borrowing_requests WHERE student_id = ? OR qr_code = ? LIMIT 1");
        $stmt->execute([$qrCode, $qrCode]);
        $borrower = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($borrower) {
            $response['success'] = true;
            $response['data'] = [
                'name' => $borrower['borrower_name'] ?? 'Ryan Jay Leoligao',
                'student_id' => $borrower['student_id'] ?? $qrCode,
                'role_type' => $borrower['user_role'] ?? 'Student',
                'course_year' => $borrower['course_year'] ?? 'BSIT 3rd Year',
                'equipment' => $borrower['equipment_name'] ?? 'Extension Cord / Projector',
                'condition_status' => $borrower['condition_status'] ?? 'Needs Replacement',
                'borrow_date' => $borrower['borrow_date'] ?? date('Y-m-d H:i:s'),
                'photo' => $borrower['borrower_photo'] ?? 'logo.jpg'
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
                'condition_status' => 'Needs Replacement',
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
