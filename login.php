<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// I-check kung nakapag-login at kung student ang role
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'student') {
    header("Location: login.php");
    exit();
}

// Alamin kung aling tab ang pinili (active, history, o borrow)
$tab = $_GET['tab'] ?? 'active';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mary The Queen College - Borrower's Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .navbar-custom { background-color: #198754; }
        .profile-card { background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 25px; margin-top: 30px; border-left: 6px solid #198754; }
        .table-custom th { background-color: #198754; color: white; }
    </style>
</head>
<body>

    <!-- Green Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-6 text-white" href="#">MARY THE QUEEN COLLEGE - BORROWER'S PORTAL</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-light btn-sm fw-bold text-success me-2">Back to your dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Profile Card Section with QR Code -->
        <div class="profile-card d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex align-items-center mb-3 mb-md-0">
                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-4" style="width: 75px; height: 75px; font-size: 32px;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1" style="color: #1a365d;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Ryan Jay Leoligao'); ?></h4>
                    <p class="text-muted mb-0 small">Student ID: <?= htmlspecialchars($_SESSION['student_id'] ?? '903324454'); ?></p>
                    <p class="text-muted mb-0 small">Course / Year: <?= htmlspecialchars($_SESSION['course_year'] ?? 'BSIT-3B'); ?></p>
                </div>
            </div>
            <div class="text-center">
                <span class="text-muted small d-block mb-1 fw-bold">Your QR Code</span>
                <div class="p-1 bg-white border rounded shadow-sm d-inline-block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?= urlencode($_SESSION['student_id'] ?? '903324454'); ?>" alt="Student QR Code" style="width: 90px; height: 90px;">
                </div>
            </div>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="mt-4 mb-3">
            <a href="borrower.php?tab=active" class="btn <?= $tab === 'active' ? 'btn-success' : 'btn-secondary'; ?> btn-sm fw-bold px-3 text-decoration-none">My Borrowed Equipment</a>
            
            <a href="borrower.php?tab=history" class="btn <?= $tab === 'history' ? 'btn-success' : 'btn-secondary'; ?> btn-sm fw-bold px-3 text-white text-decoration-none" <?= $tab !== 'history' ? 'style="background-color: #adb5bd; border: none;"' : ''; ?>>Returned Items History / Remarks</a>
        </div>

        <?php if ($tab === 'active'): ?>
            <!-- TAB 1: Active Borrowed Equipment -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-custom text-center align-middle">
                        <thead>
                            <tr>
                                <th class="py-3">Student ID</th>
                                <th class="py-3">Equipment Name</th>
                                <th class="py-3">Borrow Date / Due Date</th>
                                <th class="py-3">Status / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="py-4 text-muted">Walang kasalukuyang hiniram na aktibong kagamitan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Borrow Action Section -->
            <div class="card shadow-sm border-0 p-4 text-center bg-white">
                <p class="text-muted mb-2 small fw-bold">Need to borrow an item?</p>
                <div>
                    <!-- Naka-link na ngayon sa loob ng parehong file sa pamamagitan ng tab parameter -->
                    <a href="borrower.php?tab=borrow" class="btn btn-success px-4 py-2 fw-bold shadow-sm text-decoration-none">Borrow an Item</a>
                </div>
            </div>

        <?php elseif ($tab === 'history'): ?>
            <!-- TAB 2: Returned Items History / Remarks -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-custom text-center align-middle">
                        <thead>
                            <tr>
                                <th class="py-3">Equipment Name</th>
                                <th class="py-3">Borrow Date</th>
                                <th class="py-3">Return Date</th>
                                <th class="py-3">Equipment Status (Good / Damaged / Replaced)</th>
                                <th class="py-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="py-4 text-muted">Wala pang naitalang kasaysayan ng mga ibinalik na kagamitan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab === 'borrow'): ?>
            <!-- TAB 3: Borrow Item Form Section (Nasa loob na ng borrower.php) -->
            <div class="card shadow-sm border-0 p-4 bg-white">
                <h4 class="text-success fw-bold mb-3">Borrow Equipment Form</h4>
                <form action="process_borrow.php" method="POST">
                    <div class="mb-3">
                        <label for="equipment" class="form-label fw-semibold">Select Equipment</label>
                        <select class="form-select" id="equipment" name="equipment_id" required>
                            <option value="">-- Piliin ang kagamitan na hiramin --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-semibold">Expected Return Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="borrower.php?tab=active" class="btn btn-secondary px-4 fw-bold">Back</a>
                        <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Submit Request</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
