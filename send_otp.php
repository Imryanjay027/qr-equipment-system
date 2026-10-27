<?php
// Tawagin ang koneksyon mula sa db_connection.php
require_once 'db_connection.php';

// Simulan ang session kung kinakailangan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Halimbawa ng pagkuha ng email ng user (mula sa POST o Session)
$email = $_POST['email'] ?? '';

if (!empty($email)) {
    // Mag-generate ng 6-digit OTP
    $otp = rand(100000, 999999);
    
    // I-save ang OTP sa database gamit ang $pdo natin
    try {
        $stmt = $pdo->prepare("INSERT INTO otp_table (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
        $stmt->execute([$email, $otp]);
        
        // Dito ilalagay ang PHPMailer logic mo para maipadala sa Gmail...
        
        echo "OTP sent successfully to your Gmail!";
    } catch (Exception $e) {
        echo "Error saving OTP: " . $e->getMessage();
    }
}
?>
