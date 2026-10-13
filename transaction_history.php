<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "qrcode");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$search_input = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
$borrower_data = null;
$completed_history = [];

if (!empty($search_input)) {
    $search_clean = mysqli_real_escape_string($conn, $search_input);
    
    $query = "SELECT * FROM borrowers WHERE student_id = '$search_clean' OR fullname LIKE '%$search_clean%' OR name LIKE '%$search_clean%' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $borrower_data = mysqli_fetch_assoc($result);
    } else {
        $tables_to_check = ['borrowers', 'users', 'students', 'accounts', 'registered_users'];
        foreach ($tables_to_check as $tbl) {
            $check_tbl = mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");
            if ($check_tbl && mysqli_num_rows($check_tbl) > 0) {
                $alt_q = mysqli_query($conn, "SELECT * FROM $tbl WHERE student_id = '$search_clean' OR fullname LIKE '%$search_clean%' OR name LIKE '%$search_clean%' LIMIT 1");
                if ($alt_q && mysqli_num_rows($alt_q) > 0) {
                    $borrower_data = mysqli_fetch_assoc($alt_q);
                    break;
                }
            }
        }
    }

    if (!$borrower_data) {
        $fallback_q = mysqli_query($conn, "SELECT student_id, fullname FROM borrowed_equipment WHERE student_id = '$search_clean' OR fullname LIKE '%$search_clean%' LIMIT 1");
        if ($fallback_q && mysqli_num_rows($fallback_q) > 0) {
            $borrower_data = mysqli_fetch_assoc($fallback_q);
        }
    }

    if ($borrower_data) {
        $db_student_id = $borrower_data['student_id'] ?? $search_input;
        $db_name = $borrower_data['fullname'] ?? $borrower_data['name'] ?? $borrower_data['student_name'] ?? $search_input;

        // Returned & Completed items lamang
        $completed_sql = "SELECT * FROM borrowed_equipment 
                          WHERE (student_id = '$db_student_id' OR fullname LIKE '%$db_name%') 
                          AND (status LIKE '%Returned%' OR status LIKE '%Completed%') 
                          ORDER BY id DESC";
                          
        $completed_res = mysqli_query($conn, $completed_sql);
        if ($completed_res && mysqli_num_rows($completed_res) > 0) {
            while ($c_row = mysqli_fetch_assoc($completed_res)) {
                $completed_history[] = $c_row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - MTQC</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; box-sizing: border-box; }
        .navbar { background: #28a745; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 10px; }
        .navbar-brand { display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .navbar .nav-links { display: flex; gap: 10px; align-items: center; }
        .navbar .nav-links a { color: white; text-decoration: none; font-size: 12px; font-weight: bold; background: rgba(0,0,0,0.15); padding: 6px 12px; border-radius: 4px; transition: background 0.2s; }
        .navbar .nav-links a:hover { background: rgba(0,0,0,0.3); }

        .main-content { padding: 30px 20px; max-width: 950px; margin: 0 auto; }
        .portal-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        
        .portal-header { text-align: center; margin-bottom: 25px; }
        .portal-header h2 { color: #1a365d; font-size: 18px; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .portal-header p { color: #555; font-size: 12px; margin: 0; }

        .history-title { font-size: 14px; font-weight: bold; color: #1a365d; margin-top: 15px; margin-bottom: 12px; text-transform: uppercase; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; margin-bottom: 15px; }
        th, td { padding: 11px 12px; border-bottom: 1px solid #e1e4e8; }
        th { background-color: #1a365d; color: white; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand">
            Mary The Queen College - Transaction History
        </div>
        <div class="nav-links">
            <a href="borrower.php?student_id=<?php echo urlencode($search_input); ?>" style="background: #1a365d;">Borrowers Portal</a>
            <a href="registration.php">Register an Account</a>
            <a href="welcome.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="portal-card">
            
            <div class="portal-header">
                <h2>MARY THE QUEEN COLLEGE OF QUEZON CITY</h2>
                <p>Transaction History - Returned & Completed Equipment</p>
            </div>

            <?php if (!empty($search_input) && $borrower_data): ?>
                <div class="history-title">Transaction History (Returned & Completed Items)</div>

                <?php if (count($completed_history) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Equipment Name</th>
                                <th>Return Date / Time</th>
                                <th>Status</th>
                                <th>Custodian Remarks / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_history as $crow): ?>
                                <?php 
                                    $c_item_name = $crow['item_to_borrow'] ?? $crow['equipment_name'] ?? $crow['item'] ?? 'N/A';
                                    $c_return_datetime = $crow['return_date'] ?? 'N/A';
                                    if(!empty($crow['return_time'])) {
                                        $c_return_datetime .= " " . $crow['return_time'];
                                    }
                                    $c_item_status = $crow['status'] ?? 'Returned';
                                    $c_custodian_remarks = $crow['remarks'] ?? $crow['condition_notes'] ?? 'No remarks';
                                ?>
                                <tr>
                                    <td><b><?php echo htmlspecialchars($c_item_name); ?></b></td>
                                    <td><?php echo htmlspecialchars($c_return_datetime); ?></td>
                                    <td>
                                        <span style="background: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; display: inline-block;">
                                            <?php echo htmlspecialchars($c_item_status); ?>
                                        </span>
                                    </td>
                                    <td style="color: #555; font-style: italic; font-size: 12px;"><?php echo htmlspecialchars($c_custodian_remarks); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #777; font-style: italic; padding: 25px; font-size: 13px;">No completed transaction history found for this account.</p>
                <?php endif; ?>

            <?php else: ?>
                <p style="text-align: center; color: #d9534f; font-style: italic; padding: 25px;">Walang nakitang aktibong sesyon o student ID. Mangyaring bumalik sa <a href="borrower.php" style="color: #28a745; font-weight: bold;">Borrowers Portal</a> at mag-search muli.</p>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>