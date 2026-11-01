<?php
header('Content-Type: application/json');

$response = ['success' => false];

$requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
$newStatus = isset($_POST['status']) ? trim((string) $_POST['status']) : '';

if ($requestId <= 0 || $newStatus === '') {
    echo json_encode($response);
    exit;
}

$allowedStatuses = ['Good', 'Damaged', 'Pending', 'Needs Replacement', 'Replaced', 'Waiting for Custodian Approval', 'Cleared', 'Active'];
if (!in_array($newStatus, $allowedStatuses, true)) {
    echo json_encode($response);
    exit;
}

require_once 'db_connection.php';
if (!isset($pdo) || !($pdo instanceof PDO)) {
    $response['error'] = 'Database connection unavailable';
    echo json_encode($response);
    exit;
}

$tables = ['borrow_requests', 'requests'];
$updated = false;
foreach ($tables as $table) {
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('status', $columns, true)) {
            continue;
        }
        $stmt = $pdo->prepare("UPDATE `$table` SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $requestId]);
        if ($stmt->rowCount() >= 0) {
            $updated = true;
        }
    } catch (Throwable $e) {
        // Try the other candidate table.
    }
}

// Also keep the matching requests table hidden flag consistent for custodian approval.
if ($newStatus === 'Good') {
    foreach (['requests'] as $table) {
        try {
            $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('hidden_for_custodian', $columns, true)) {
                $stmt = $pdo->prepare("UPDATE `$table` SET hidden_for_custodian = 1 WHERE id = ?");
                $stmt->execute([$requestId]);
            }
        } catch (Throwable $e) {
        }
    }
}

$response['success'] = $updated;
echo json_encode($response);
?>