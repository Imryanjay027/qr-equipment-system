<?php
// Enable error reporting para sa debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Tiyaking tama ang table name (mga lumang tables: 'users' o 'accounts')
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Pagre-generate ng OTP at pag-store sa Session
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['login_email'] = $email;

                // Direct redirection papuntang verify_otp.php
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Hindi rehistrado ang email na ito.";
            }
            $stmt->close();
        } else {
            $error = "Database query error: " . $conn->error;
        }
    } else {
        $error = "Pakilagay ang iyong email address.";
    }
}
?>
