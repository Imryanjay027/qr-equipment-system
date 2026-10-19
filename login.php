<?php
session_start();
require_once 'db_connection.php'; // Sinisiguradong konektado sa database

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // 1. I-check kung rehistrado ang email sa database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); // Palitan ang 'users' kung iba ang pangalan ng table mo
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // 2. Gumawa ng OTP at i-save sa session o database
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['login_email'] = $email;

            // (Opsyonal) Dito ilalagay ang code para magpadala ng email (Mail/PHPMailer)
            // mail($email, "Your OTP Code", "Your OTP is: $otp");

            // 3. I-redirect papuntang verify_otp.php
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
