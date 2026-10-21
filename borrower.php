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

// AUTOMATIKONG GAGAWA NG EQUIPMENT TABLE KUNG WALA PA
$conn->query("CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Available'
)");

// Magdagdag ng sample equipment kung blangko pa ang table para may makita ka agad
$check_empty = $conn->query("SELECT COUNT(*) as count FROM equipment");
if ($check_empty) {
    $row = $check_empty->fetch_assoc();
    if ($row['count'] == 0) {
        $conn->query("INSERT INTO equipment (name, category, status) VALUES 
            ('Projector Epson', 'Multimedia', 'Available'),
            ('HDMI Cable 10m', 'Accessories', 'Available'),
            ('Extension Cord', 'Accessories', 'Available'),
            ('Laptop HP Pavilion', 'Computers', 'Available')");
    }
}

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

// Kunin ang mga available equipment mula sa database
$equipments = $conn->query("SELECT * FROM equipment");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard - MQCQC Equipment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- QR Code Generator JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-qrcode me-2"></i>MQCQC Borrower Dashboard
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small">
                    <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($current_user['full_name']); ?> 
                    <span class="badge bg-light text-success ms-1 text-uppercase"><?= htmlspecialchars($current_user['role']); ?></span>
                </span>
                <a href="login.php" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm p-4 bg-white rounded-4">
                    <h3 class="fw-bold text-success mb-1">Welcome back, <?= htmlspecialchars($current_user['full_name']); ?>!</h3>
                    <p class="text-muted mb-0">Maaari ka nang mag-borrow at mag-monitor ng mga school equipment gamit ang system.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-success fs-1">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h5 class="fw-bold">Available Equipment</h5>
                        <p class="text-muted small">Tignan ang mga available na kagamitan na maaari mong hiramin.</p>
                        <button class="btn btn-success btn-sm w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#equipmentModal">Tignan ang Listahan</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-primary fs-1">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="fw-bold">My Borrowing History</h5>
                        <p class="text-muted small">I-check ang katayuan ng iyong mga hiniram na kagamitan.</p>
                        <button class="btn btn-outline-primary btn-sm w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#historyModal">Tignan ang History</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-3 rounded-4">
                    <div class="card-body text-center">
                        <div class="mb-3 text-warning fs-1">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="fw-bold">My QR Code</h5>
                        <p class="text-muted small">I-display ang iyong QR code para sa mabilisang transaksiyon.</p>
                        <button class="btn btn-outline-warning btn-sm w-100 fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#qrModal">Tignan ang QR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para sa Equipment List -->
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($equipments && $equipments->num_rows > 0): ?>
                                    <?php while($row = $equipments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name'] ?? 'Equipment'); ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? 'General'); ?></td>
                                            <td><span class="badge bg-success"><?= htmlspecialchars($row['status'] ?? 'Available'); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Wala pang nakarehistrong equipment sa database.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para sa History -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-history me-2"></i>My Borrowing History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4 text-muted">
                    Wala ka pang naitalang mga hiniram na kagamitan sa kasaysayan.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para sa QR Code -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark fw-bold"><i class="fas fa-id-card me-2"></i>My Digital QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Ipakita ang QR code na ito sa Custodian para sa pag-borrow ng item.</p>
                    
                    <div id="qrcode-container" class="d-flex justify-content-center mb-3">
                        <div id="qrcode"></div>
                    </div>

                    <h5 class="fw-bold text-success mb-1"><?= htmlspecialchars($current_user['full_name']); ?></h5>
                    <p class="small text-muted mb-0">Student ID / UID: <?= $current_user['id']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const qrModal = document.getElementById('qrModal');
        qrModal.addEventListener('shown.bs.modal', function () {
            const qrDiv = document.getElementById('qrcode');
            qrDiv.innerHTML = "";
            
            new QRCode(qrDiv, {
                text: "MQCQC-USER-ID: <?= $current_user['id']; ?> | Name: <?= htmlspecialchars($current_user['full_name']); ?>",
                width: 180,
                height: 180,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
