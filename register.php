<?php
// register.php
session_start();
require_once 'db_connection.php'; // Siguraduhing tama ang path ng database connection mo

// PHPMailer dependencies (kung ginagamit sa email verification)
require_once 'PHPMailer.php';
require_once 'SMTP.php';
require_once 'Exception.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // PANPASAMANTALANG LIMIT CHECK (Ginawang 5 muna para sa testing, ibalik sa 2 pagkatapos)
    if ($role === 'Custodian') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_users WHERE role = 'Custodian'");
        $stmt->execute();
        $custodianCount = $stmt->fetchColumn();

        if ($custodianCount >= 5) { // Binago muna sa 5 para makapag-register
            $error = "Maximum limit of 5 Custodian accounts reached.";
        }
    }

    if (empty($error)) {
        // I-check kung nag-eexist na ang email o username
        $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $error = "Email or Username already exists.";
        } else {
            // Hash password para sa seguridad
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // I-insert ang bagong user sa database
            $insert = $pdo->prepare("INSERT INTO tbl_users (username, fullname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($insert->execute([$username, $fullname, $email, $hashedPassword, $role])) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title>Account Registration - QR-Based Equipment Monitoring System</title>
    <!-- Ilagay ang CSS links o styles mo dito -->
</head>
<body>
    <div class="container">
        <h2>"QR-BASED EQUIPMENT MONITORING SYSTEM"</h2>
        <p>Account Registration</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="color: red; background: #f8d7da; padding: 10px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="color: green; background: #d4edda; padding: 10px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label>Account Role</label>
            <select name="role" required>
                <option value="Student">Student</option>
                <option value="Custodian">Custodian</option>
            </select>

            <label>ID Number / Username</label>
            <input type="text" name="username" placeholder="Enter ID Number" required>

            <label>Full Name</label>
            <input type="text" name="fullname" placeholder="Enter your Full name" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
