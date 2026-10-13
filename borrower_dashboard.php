<?php
session_start();

// Dito ilalagay ang koneksyon sa database kung mayroon na kayo
// Halimbawa: $conn = mysqli_connect("localhost", "root", "", "pangalan_ng_db");

$student_id = "";
$has_searched = false;

// Kung galing sa URL parameter (GET) o sinubmite mula sa search bar (POST)
if (isset($_GET['student_id'])) {
    $student_id = htmlspecialchars($_GET['student_id']);
    $has_searched = true;
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = htmlspecialchars($_POST['student_id']);
    $has_searched = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mary The Queen College - Borrower Dashboard</title>
    <style>
        .borrower-navbar {
            background-color: #28a745;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .borrower-navbar .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: bold;
            font-size: 16px;
        }
        .nav-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: white;
            border: 2px solid white;
        }
        .borrower-navbar .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .borrower-navbar a {
            color: white !important;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
        }
        .borrower-navbar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 30px;
            text-align: center;
        }
        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 20px auto;
            text-align: left;
        }
        .portal-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .portal-header h3 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .portal-header p {
            margin: 5px 0 15px 0;
            color: #666;
            font-size: 13px;
        }
        .search-box {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 25px;
        }
        .search-box input {
            width: 60%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-box button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .search-box button:hover {
            background-color: #0056b3;
        }
        .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-left: 5px solid #007bff;
        }
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ccc;
        }
        .profile-info h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 16px;
        }
        .profile-info p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #1f3a60;
            color: white;
        }
        .status-badge {
            background-color: #e0a800;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="borrower-navbar">
        <div class="nav-brand">
            <img src="logo.jpg" alt="MTQC Logo" class="nav-logo">
            <span>Mary The Queen College - Borrower Portal</span>
        </div>
        <div class="nav-links">
            <a href="registration.php">🏠 Register</a>
            <a href="welcome.php">🚪 Back to Home</a>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container">
        <div class="dashboard-card">
            <div class="portal-header">
                <h3>MARY THE QUEEN COLLEGE OF QUEZON CITY</h3>
                <p>Borrower's Portal - Equipment Status & History</p>
            </div>

            <!-- Search Form para sa Student ID -->
            <form method="GET" action="" class="search-box">
                <input type="text" name="student_id" placeholder="Ilagay ang Student ID (Hal. 26529784)" value="<?php echo htmlspecialchars($student_id); ?>" required>
                <button type="submit">Search Dashboard</button>
            </form>

            <?php if ($has_searched && !empty($student_id)): ?>
                <!-- Profile ng Estudyante -->
                <div class="profile-section">
                    <!-- Dito mo pwedeng ipalit ang nakuha mong selfie image galing sa database -->
                    <img src="logo.jpg" alt="Student Selfie" class="profile-avatar">
                    <div class="profile-info">
                        <h4>Pangalan ng Estudyante (Mula sa DB)</h4>
                        <p>ID Number: <strong><?php echo htmlspecialchars($student_id); ?></strong></p>
                    </div>
                </div>

                <!-- Talahanayan ng mga Hiniram na Kagamitan -->
                <h4>Your Borrowed Equipment History</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Equipment Name</th>
                            <th>Borrow Date & Time</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Custodian Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- HALIMBAWA NG NILALAMAN (Palitan na lang ng query mula sa database) -->
                        <tr>
                            <td>Projector / Mouse</td>
                            <td>2026-09-18 03:45:14</td>
                            <td>2026-09-19 04:00 PM</td>
                            <td><span class="status-badge">Borrowed / Active</span></td>
                            <td>Wala pang notes</td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="back-link">
                <a href="registration.php">← Back to QR Scanner / Registration</a>
            </div>
        </div>
    </div>

</body>
</html>