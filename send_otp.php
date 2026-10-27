<?php
// Simulan ang session para maimbak ang email at OTP
session_start();

// I-enable ang error reporting para sa debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration para sa Database Connection (Nakase-set sa qr_equipment_db)
$db_host = getenv('DB_HOST') ?: 'scvzklpphi95efixy0m7hpyg';
$db_port = getenv('DB_PORT') ?: '3306';
$db_user = getenv('DB_USER') ?: 'mariadb';
$db_pass = getenv('DB_PASS') ?: 'pPjqOQG7NRXy20MSRfcuNVhtLTbiAR75w9CLeSceBwp06CQLGrUjgeLolgHCBzJH';
$db_name = getenv('DB_NAME') ?: 'qr_equipment_db';

// 1. Kumonekta sa MariaDB gamit ang PDO (may 3-second timeout limit)
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3 
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("Database Connection Error: " . $e->getMessage());
}

// Suriin kung POST request ang isinagawa mula sa login o OTP form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        die("Pakilagay ang isang valid na email address.");
    }

    // 2. I-verify kung ang email ay umiiral sa database ng users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Ang email na ito ay hindi rehistrado sa sistema.");
    }

    // 3. Bumuo ng 6-digit OTP code at magtakda ng expiration (10 minutes)
    $otp = rand(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // 4. I-save o i-update ang OTP sa database
    $updateStmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
    $updateStmt->execute([$otp, $expires_at, $email]);

    // 5. Brevo HTTP API Configuration (Port 443 - HTTPS)
    $apiKey = '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';

    $postData = [
        'sender' => [
            'name' => 'MQCQC Equipment Monitoring',
            'email' => 'no-reply@mqcqc.edu.ph'
        ],
        'to' => [
            [
                'email' => $email
            ]
        ],
        'subject' => 'Your MQCQC Verification OTP Code',
        'htmlContent' => '
            <div style="font-family: Arial, sans-serif; padding: 20px; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #2c3e50; text-align: center;">MQCQC Equipment Monitoring System</h2>
                <hr style="border: 0; border-top: 1px solid #eee;">
                <p>Hello,</p>
                <p>Your verification code for logging in is:</p>
                <div style="text-align: center; margin: 20px 0;">
                    <span style="background: #f4f4f4; padding: 12px 24px; font-size: 28px; font-weight: bold; letter-spacing: 5px; color: #e74c3c; border-radius: 5px; display: inline-block;">' . $otp . '</span>
                </div>
                <p>This code will expire in <strong>10 minutes</strong>.</p>
                <p style="font-size: 12px; color: #777; margin-top: 30px;">If you did not request this code, please ignore this email.</p>
            </div>
        '
    ];

    // Magsagawa ng cURL Request patungo sa Brevo API
    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Max 5 seconds timeout limit

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Suriin kung natanggap nang maayos ng Brevo ang request (201 Created o 200 OK)
    if ($httpCode === 201 || $httpCode === 200) {
        $_SESSION['otp_email'] = $email;
        header("Location: verify_otp.php");
        exit();
    } else {
        http_response_code(500);
        echo "Hindi naipadala ang email via Brevo API. Response Code: $httpCode | Details: $response";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
