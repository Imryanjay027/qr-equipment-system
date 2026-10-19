<?php
// I-on ang error reporting para maiwasan ang blind white screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Siguraduhing 'users' ang pangalan ng table mo, o palitan ito kung iba
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['login_email'] = $email;

            // I-redirect papuntang verify_otp.php
            header("Location: verify_otp.php");
            exit();
        } else {
            $error = "Ang email na ito ay hindi rehistrado.";
        }
        $stmt->close();
    } else {
        $error = "Mangyaring ilagay ang iyong email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - QR-Based Equipment Monitoring System</title>
    <!-- Ilagay ang iyong CSS styles dito o i-link ang stylesheet -->
</head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; font-family: sans-serif;">
        <h2>Account Login (OTP Authentication)</h2>
        
        <?php if (!empty($error)): ?>
            <div style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="email">Email Address</label><br>
            <input type="email" name="email" id="email" placeholder="Enter your Email" required style="width: 100%; padding: 8px; margin: 10px 0;">
            <button type="submit" style="width: 100%; padding: 10px; background: #1b5e20; color: white; border: none; cursor: pointer;">Send OTP</button>
        </form>
    </div>
</body>
</html>
