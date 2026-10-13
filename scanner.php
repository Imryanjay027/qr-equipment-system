<?php
// scanner.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_connection.php');

// Pinaluwag natin para hindi ka ma-log out kung sakaling iba ang role name sa session mo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$fullname =$_SESSION['fullname'] ?? 'Custodian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner & Condition Monitoring - MTQC Custodian Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; }
        
        /* Top Navigation Bar */
        .navbar { background-color: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 10px; }
        .navbar h1 { font-size: 16px; font-weight: 600; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .nav-links a { background-color: #2b4c7e; color: white; padding: 8px 14px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 500; transition: background 0.2s; }
        .nav-links a:hover { background-color: #3b629e; }
        .nav-links a.logout-btn { background-color: #d9534f; }
        .nav-links a.logout-btn:hover { background-color: #c9302c; }

        /* Main Container */
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; width: 100%; }

        /* Scanner Module Box */
        .module-box { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 30px; margin-bottom: 25px; text-align: center; }
        .module-box h2 { font-size: 20px; color: #1a365d; margin-bottom: 10px; }
        .module-box p { font-size: 13px; color: #666; margin-bottom: 20px; }

        /* Camera / Scanner Frame Area */
        .scanner-container { width: 100%; max-width: 500px; margin: 0 auto 20px auto; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fafafa; padding: 30px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 250px; }
        .scanner-container p { margin-top: 15px; color: #555; font-weight: 500; font-size: 14px; }

        /* Search / Manual Input Section */
        .manual-section { display: flex; gap: 10px; max-width: 600px; margin: 0 auto; flex-wrap: wrap; }
        .manual-section input { flex: 1; min-width: 200px; padding: 10px 15px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .manual-section button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 600; cursor: pointer; width: 100%; max-width: 150px; margin: 0 auto; }
        .manual-section button:hover { background: #218838; }

        /* Records Table Section */
        .table-section { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 25px; overflow-x: auto; }
        .table-section h3 { font-size: 16px; color: #1a365d; margin-bottom: 15px; }
        
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 6px; overflow: hidden; min-width: 600px; }
        th, td { padding: 12px 15px; text-align: left; font-size: 13px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        th { background-color: #f7fafc; color: #4a5568; font-weight: 600; }
        .no-record { text-align: center; color: #e53e3e; font-style: italic; padding: 25px; font-size: 13px; }

        /* Mobile Responsive Media Queries */
        @media (max-width: 768px) {
            .navbar { padding: 12px 15px; flex-direction: column; align-items: flex-start; }
            .nav-links { width: 100%; justify-content: space-between; }
            .container { margin: 15px auto; padding: 0 10px; }
            .module-box { padding: 15px; border-radius: 0; box-shadow: none; }
            .table-section { padding: 15px; border-radius: 0; box-shadow: none; }
            .manual-section { flex-direction: column; }
            .manual-section button { max-width: 100%; }
        }
    </style>
</head>
<body>

<!-- Top Navigation -->
<div class="navbar">
    <h1>MTQC Custodian Portal (Welcome, <?php echo htmlspecialchars($fullname); ?>)</h1>
    <div class="nav-links">
        <a href="custodian_dashboard.php">Custodian Dashboard</a>
        <a href="scanner.php">Scanner / Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">

    <!-- QR Code Scanner & Verification Module -->
    <div class="module-box">
        <h2>QR Code Scanner & Verification</h2>
        <p>I-scan ang QR code ng estudyante o kagamitan para sa pagbabalik (Return) at pag-check ng condition.</p>
        
        <div class="scanner-container">
            <svg width="50" height="50" fill="none" stroke="#666" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7V5a2 2 0 0 1 2-2h2m8 0h2a2 2 0 0 1 2 2v2m0 8v2a2 2 0 0 1-2 2h-2m-8 0H6a2 2 0 0 1-2-2v-2"/></svg>
            <p>Awtomatikong binubuksan ang Live