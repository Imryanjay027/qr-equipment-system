<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Siguraduhing may mga karagdagang columns ang users table
try { @$conn->query("ALTER TABLE users ADD COLUMN student_id VARCHAR(50) DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE users ADD COLUMN course_year VARCHAR(100) DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL"); } catch (Exception $e) {}

// Auto-create equipment table kung wala pa
$conn->query("CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Available'
)");

$user_id = $_SESSION['user_id'] ?? ($_GET['uid'] ?? '');

if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

if (!$current_user) {
    header("Location: login.php");
    exit();
}

$_SESSION['user_id'] = $current_user['id'];
$_SESSION['role']    = strtolower(trim($current_user['role']));
$_SESSION['full_name'] = $current_user['full_name'];

$equipments = $conn->query("SELECT * FROM equipment");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower's Portal - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
        }
        .top-navbar {
            background-color: #217835;
            color: #ffffff;
            padding: 12px 20px;
            font-weight: bold;
            font-size: 0.95rem;
        }
        .main-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background: #ffffff;
        }
        .profile-img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #217835;
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 600;
            border: none;
            background-color: #e9ecef;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .nav-tabs .nav-link.active {
            color: #ffffff;
            background-color: #217835;
        }
        .table-custom th {
            background-color: #217835 !important;
            color: #ffffff !important;
            font-size: 0.9rem;
            border: none;
        }
        .btn-green {
            background-color: #217835;
            color: #ffffff;
            font-weight: 600;
            border: none;
        }
        .btn-green:hover {
            background-color: #1a602a;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Top Header Bar -->
    <div class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
        <div>MARY THE QUEEN COLLEGE OF QUEZON CITY - BORROWER'S PORTAL</div>
        <div class="d-flex gap-2">
            <a href="borrower.php?uid=<?= $current_user['id']; ?>" class="btn btn-dark btn-sm px-3">Back to your dashboard</a>
            <a href="login.php" class="btn btn-danger btn-sm px-3">Logout</a>
        </div>
    </div>

    <div class="container my-4">
        <div class="card main-card p-4 mx-auto" style="max-width: 1000px;">
            
            <!-- Profile & QR Code Section -->
            <div class="row align-items-center border-bottom pb-4 mb-4">
                <div class="col-md-8 d-flex align-items-center gap-3">
                    <div>
                        <?php if (!empty($current_user['profile_pic'])): ?>
                            <img src="<?= htmlspecialchars($current_user['profile_pic']); ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center profile-img fs-3">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($current_user['full_name']); ?></h4>
                        <p class="text-muted mb-0 small">Student ID: <strong><?= htmlspecialchars($current_user['student_id'] ?? $current_user['id']); ?></strong></p>
                        <p class="text-muted mb-0 small">Course / Year: <strong><?= htmlspecialchars($current_user['course_year'] ?? 'N/A'); ?></strong></p>
                    </div>
                </div>
                
                <!-- QR Code Box sa Kanan -->
                <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                    <div class="d-inline-block p-2 bg-white border rounded shadow-sm">
                        <div id="qrcode" class="d-flex justify-content-center"></div>
                        <span class="text-muted" style="font-size: 0.75rem;">Your QR Code</span>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-3" id="portalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="borrowed-tab" data-bs-toggle="tab" data-bs-target="#borrowed-content" type="button" role="tab">My Borrowed Equipment</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button" role="tab">Returned Items History / Remarks</button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="portalTabsContent">
                <!-- My Borrowed Equipment Tab -->
                <div class="tab-pane fade show active" id="borrowed-content" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                            <thead>
                                <tr class="table-custom">
                                    <th>Student ID</th>
                                    <th>Equipment Name</th>
                                    <th>Borrow Date / Due Date</th>
                                    <th>Status / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-muted py-4">Walang kasalukuyang hiniram na aktibong kagamitan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Returned Items History Tab -->
                <div class="tab-pane fade" id="history-content" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                            <thead>
                                <tr class="table-custom">
                                    <th>Student ID</th>
                                    <th>Equipment Name</th>
                                    <th>Date Returned</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-muted py-4">Wala pang naitalang kasaysayan ng mga ibinalik na kagamitan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Need to Borrow an Item Section -->
            <div class="text-center mt-5 pt-3 border-top">
                <p class="text-muted small mb-2">Need to borrow an item?</p>
                <button class="btn btn-green px-4 py-2" data-bs-toggle="modal" data-bs-target="#equipmentModal">Borrow an Item</button>
            </div>

        </div>
    </div>

    <!-- Modal para sa Borrow an Item / Equipment List -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Available Equipment List</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Equipment Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($equipments && $equipments->num_rows > 0): ?>
                                    <?php while($row = $equipments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name'] ?? 'Equipment'); ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? 'General'); ?></td>
                                            <td><span class="badge bg-success"><?= htmlspecialchars($row['status'] ?? 'Available'); ?></span></td>
                                            <td><button class="btn btn-sm btn-success">Request Borrow</button></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Wala pang nakarehistrong equipment sa database.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatic na pag-generate ng QR code sa loob ng box sa kanan
        document.addEventListener("DOMContentLoaded", function () {
            const qrDiv = document.getElementById('qrcode');
            qrDiv.innerHTML = "";
            
            new QRCode(qrDiv, {
                text: "MQCQC-ID: <?= htmlspecialchars($current_user['student_id'] ?? $current_user['id']); ?> | Name: <?= htmlspecialchars($current_user['full_name']); ?>",
                width: 90,
                height: 90,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
