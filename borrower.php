<?php
session_start();
require_once 'db_connection.php';

// Siguraduhing naka-login ang user
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Kunin ang impormasyon ng user mula sa database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrower's Dashboard - Mary the Queen College of Quezon City</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; background-color: #f4f7f6; height: 100vh; overflow: hidden; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background-color: #0b2514; color: white; display: flex; flex-direction: column; justify-content: space-between; padding: 20px; }
        .sidebar-top .logo-area { text-align: center; margin-bottom: 30px; }
        .sidebar-top .logo-area img { width: 70px; height: 70px; border-radius: 50%; margin-bottom: 10px; }
        .sidebar-top .logo-area h3 { font-size: 13px; line-height: 1.4; color: #fff; }
        
        .nav-links { list-style: none; }
        .nav-links li { margin-bottom: 10px; }
        .nav-links a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px 15px; border-radius: 6px; font-size: 14px; transition: background 0.2s; }
        .nav-links a:hover, .nav-links li.active a { background-color: #1e4620; }
        
        .sidebar-bottom { font-size: 11px; color: #a3c1ad; text-style: italic; text-align: center; }

        /* Main Content Styling */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        
        /* Top Navbar */
        .top-navbar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; }
        .top-navbar .title h2 { font-size: 18px; color: #222; text-transform: uppercase; }
        .top-navbar .title p { font-size: 12px; color: #777; }
        .user-nav-info { display: flex; align-items: center; gap: 15px; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .logout-btn:hover { background-color: #c82333; }

        /* Content Area */
        .content-body { padding: 30px; }
        
        /* Profile Card */
        .profile-card { background: white; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .profile-left { display: flex; align-items: center; gap: 20px; }
        .profile-left img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #1e4620; }
        .profile-info h3 { font-size: 18px; color: #333; }
        .profile-info p { font-size: 13px; color: #666; margin-top: 3px; }
        .qr-box { text-align: center; border: 1px dashed #ccc; padding: 10px; border-radius: 6px; background: #fafafa; }
        .qr-box p { font-size: 10px; color: #555; margin-top: 5px; }

        /* Table Section */
        .section-box { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 25px; }
        .section-header { background-color: #1e4620; color: white; padding: 12px 20px; font-size: 15px; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th, td { padding: 12px 20px; border-bottom: 1px solid #eee; }
        th { background-color: #265c29; color: white; }
        .empty-row { text-align: center; padding: 25px; color: #777; font-style: italic; }

        /* Action Banner */
        .action-banner { background: white; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .action-banner-text h4 { font-size: 15px; color: #333; }
        .action-banner-text p { font-size: 12px; color: #666; margin-top: 3px; }
        .borrow-btn { background-color: #265c29; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .borrow-btn:hover { background-color: #1e4620; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-top">
            <div class="logo-area">
                <!-- Palitan ang logo.png kung kinakailangan -->
                <img src="logo.png" alt="School Logo" onerror="this.src='https://via.placeholder.com/70'">
                <h3>MARY THE QUEEN COLLEGE OF QUEZON CITY</h3>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="borrower.php">🏠 Dashboard</a></li>
                <li><a href="borrow_item.php">➕ Borrow an Item</a></li>
                <li><a href="history.php">📋 Returned Items / History</a></li>
            </ul>
        </div>
        <div class="sidebar-bottom">
            <p>"Discipline today, excellence tomorrow."</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="title">
                <h2>Borrower's Dashboard</h2>
                <p>Equipment Borrowing, Return & Monitoring Portal</p>
            </div>
            <div class="user-nav-info">
                <span>👤 <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Content Body -->
        <div class="content-body">
            
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-left">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Live Selfie">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/80" alt="Default Avatar">
                    <?php endif; ?>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p><strong>Student ID / User ID:</strong> MQC-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Course / Year / Section:</strong> <?php echo htmlspecialchars($user['course_year_section'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                <div class="qr-box">
                    <!-- Maaari mong palitan ito ng dynamic QR code kung kinakailangan -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=USER-<?php echo $user['id']; ?>" alt="QR Code">
                    <p>Your QR Code</p>
                </div>
            </div>

            <!-- My Borrowed Equipment Table -->
            <div class="section-box">
                <div class="section-header">
                    <span>📦</span> My Borrowed Equipment
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Equipment Name</th>
                            <th>Borrow Date / Due Date</th>
                            <th>Status / Action</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dito mo ikokonekta ang query para sa mga hiniram na gamit -->
                        <tr>
                            <td colspan="5" class="empty-row">Walang kasalukuyang hiniram na aktibong kagamitan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Need to borrow an item banner -->
            <div class="action-banner">
                <div class="action-banner-text">
                    <h4>Need to borrow an item?</h4>
                    <p>Submit a new borrowing request from the inventory.</p>
                </div>
                <a href="borrow_item.php" class="borrow-btn">+ Borrow an Item</a>
            </div>

        </div>
    </div>

</body>
</html>
