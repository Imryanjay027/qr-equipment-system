<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false];

// Whitelisted candidate tables and the columns we may search on / read from.
$borrowTables = ['borrow_requests', 'requests', 'borrowed_equipment', 'borrowers'];

function tableColumns(PDO $pdo, string $table): array
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable $e) {
        return [];
    }
}

function firstValue(array $row, array $candidates): ?string
{
    foreach ($candidates as $c) {
        if (isset($row[$c]) && $row[$c] !== '' && $row[$c] !== null) {
            return (string) $row[$c];
        }
    }
    return null;
}

function searchRecord(PDO $pdo, array $tables, string $needle): ?array
{
    foreach ($tables as $table) {
        $cols = tableColumns($pdo, $table);
        if (!$cols) {
            continue;
        }

        $where = [];
        $params = [];
        foreach (['student_id', 'id_number', 'id', 'user_id', 'email', 'fullname', 'full_name', 'name', 'student_name'] as $col) {
            if (!in_array($col, $cols, true)) {
                continue;
            }
            // Avoid comparing a non-numeric needle to an integer id/user_id column when possible.
            if (in_array($col, ['id', 'user_id'], true) && !ctype_digit($needle)) {
                continue;
            }
            $where[] = "`$col` = ?";
            $params[] = in_array($col, ['id', 'user_id'], true) ? (int) $needle : $needle;
        }
        if (!$where) {
            continue;
        }

        $sql = "SELECT * FROM `$table` WHERE (" . implode(' OR ', $where) . ") ORDER BY id DESC LIMIT 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                $row['_table'] = $table;
                return $row;
            }
        } catch (Throwable $e) {
            // Try the next candidate table.
        }
    }
    return null;
}

if (!isset($_GET['qr_code'])) {
    echo json_encode($response);
    exit;
}

$qrCode = trim((string) $_GET['qr_code']);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    $response['error'] = 'Database connection unavailable';
    echo json_encode($response);
    exit;
}

$userRecord = null;
try {
    $userRecord = searchRecord($pdo, ['users'], $qrCode);
} catch (Throwable $e) {
    $userRecord = null;
}

$borrowRecord = searchRecord($pdo, $borrowTables, $qrCode);

// borrow_requests links to users via user_id; fetch that user so names/id numbers stay correct.
if ($borrowRecord !== null) {
    $linkedUserId = firstValue($borrowRecord, ['user_id']);
    if ($linkedUserId !== null && ctype_digit($linkedUserId)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(int) $linkedUserId]);
            $linkedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($linkedUser)) {
                $userRecord = $linkedUser;
            }
        } catch (Throwable $e) {
            // Keep whatever user record we already have.
        }
    }
}

// Fall back to searching by the full name found in the user record,
// in case the QR contained an id but the borrow row was keyed by name/email.
if ($borrowRecord === null && $userRecord !== null) {
    $name = firstValue($userRecord, ['full_name', 'name', 'fullname', 'student_name']);
    if ($name !== null && $name !== $qrCode) {
        $borrowRecord = searchRecord($pdo, $borrowTables, $name);
    }
}

if ($userRecord === null && $borrowRecord === null) {
    $response['error'] = 'Borrower not found in database';
    echo json_encode($response);
    exit;
}

$name = firstValue($borrowRecord, ['full_name', 'fullname', 'name', 'student_name'])
    ?? firstValue($userRecord, ['full_name', 'name', 'fullname', 'student_name'])
    ?? $qrCode;

$studentId = firstValue($userRecord, ['id_number', 'student_id', 'user_id', 'id'])
    ?? firstValue($borrowRecord, ['student_id', 'user_id'])
    ?? $qrCode;

$course = firstValue($borrowRecord, ['course_year', 'course_year_section', 'course'])
    ?? firstValue($userRecord, ['course_year', 'course_year_section', 'course'])
    ?? '--';

$equipment = firstValue($borrowRecord, ['item_to_borrow', 'equipment_name', 'equipment', 'item'])
    ?? 'No active borrowed equipment on record';

$status = firstValue($borrowRecord, ['status', 'condition_status'])
    ?? 'Pending';

// Prefer the return due date + time (when the equipment must be returned).
$returnDate = firstValue($borrowRecord, ['return_date', 'due_date', 'return_datetime']);
$returnTime = firstValue($borrowRecord, ['return_time', 'due_time', 'return_hour']);
if ($returnDate !== null && $returnTime !== null) {
    $borrowDate = trim($returnDate . ' ' . $returnTime);
} elseif ($returnDate !== null) {
    $borrowDate = $returnDate;
} else {
    $borrowDate = firstValue($borrowRecord, ['borrow_date', 'created_at', 'date_borrowed', 'borrowed_at'])
        ?? date('Y-m-d H:i:s');
}

$photo = firstValue($borrowRecord, ['selfie_image', 'profile_photo', 'student_selfie', 'selfie'])
    ?? firstValue($userRecord, ['selfie', 'profile_picture', 'profile_pic', 'photo'])
    ?? 'logo.jpg';

// Make sure relative image paths remain usable from the web root.
if ($photo !== null && strpos($photo, '/') === false && $photo !== 'logo.jpg') {
    $photo = 'uploads/' . $photo;
}

$response['success'] = true;
$response['data'] = [
    'id' => isset($borrowRecord['id']) ? (int) $borrowRecord['id'] : (isset($userRecord['id']) ? (int) $userRecord['id'] : null),
    'name' => $name,
    'student_id' => $studentId,
    'role_type' => 'Student',
    'course_year' => $course,
    'equipment' => $equipment,
    'status' => $status,
    'borrow_date' => $borrowDate,
    'photo' => $photo,
];

echo json_encode($response);
?>