<?php
// Simulan ang session para makuha o maimbak ang OTP at user info
session_start();

// I-enable ang error reporting para makita agad sa logs kung sakaling magkaaberya
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kunin ang mga environment variables na sinet natin sa Coolify
$db_host = getenv('DB_HOST') ?: 'vps.araosds.cloud';
$db_port = getenv('DB_PORT') ?: '3000';
$db_user = getenv('DB_USER') ?: 'mariadb';
$db_pass = getenv('DB_PASS') ?: getenv('DB_PASSWORD');
$db_name = getenv('DB_NAME') ?: 'default';

// Gumamit ng PDO para sa mabilis at secure na koneksyon sa MariaDB gamit ang tamang port
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5 // 5 seconds database connection timeout para iwas 504
    ]);
} catch (PDOException $e) {
    // Kung sakaling mag-timeout o pumalya ang DB, sabihin agad sa halip na mag-hang
    http_response_code(500);
    die("Database Connection Error: " . $e->getMessage());
}

// Suriin kung POST request ang ginawa mula sa login page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        die("Please provide a valid email address.");
    }

    // 1. I-verify muna kung rehistrado ang email sa database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Email address not found in the system.");
    }

    // 2. Gumawa ng 6-digit OTP code
    $otp = rand(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // 3. I-save o i-update ang OTP sa database para sa user na ito
    $updateStmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
    $updateStmt->execute([$otp, $expires_at, $email]);

    // 4. Pagpapadala ng Email (Gamit ang Brevo SMTP o mail function na may timeout)
    // Ilagay dito ang iyong email sending logic (Halimbawa: PHPMailer o mail function)
    $subject = "Your MQCQC Equipment Monitoring OTP Code";
    $message = "Your verification code is: $otp. It will expire in 10 minutes.";
    $headers = "From: no-reply@mqcqc.edu.ph\r\n";

    // Sinusubukan itong ipadala nang mabilis
    $mail_sent = @mail($email, $subject, $message, $headers);

    if ($mail_sent) {
        // I-save ang email sa session para magamit sa verification page
        $_SESSION['otp_email'] = $email;
        
        // I-redirect sa OTP verification input page
        header("Location: verify_otp.php");
        exit();
    } else {
        http_response_code(500);
        echo "Failed to send OTP email. Please try again later.";
    }
}
else {
    // Kung nilagyan ng diretso sa URL
    header("Location: login.php");
    exit();
}
?>
