<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        die("Email is required.");
    }
    
    // 1. Gumawa ng random 6-digit OTP
    $otp = rand(100000, 999999);
    
    // 2. I-save sa SQLite database
    $db_file = '/tmp/qrcode_database.sqlite';
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE,
        otp TEXT,
        role TEXT DEFAULT 'borrower'
    )");
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'borrower'");
    } catch (PDOException $e) {}
    
    $stmt = $pdo->prepare("SELECT role FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $role = $existing_user['role'] ?? 'borrower';
    
    $stmt = $pdo->prepare("INSERT INTO users (email, otp, role) VALUES (:email, :otp, :role) 
                           ON CONFLICT(email) DO UPDATE SET otp=:otp");
    $stmt->execute(['email' => $email, 'otp' => $otp, 'role' => $role]);
    
    $_SESSION['login_email'] = $email;

    // 3. Pagpapadala ng OTP sa Gmail gamit ang Brevo API
    $brevo_api_key = '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';
    
    $data = [
        // Pinalitan natin ng iyong Gmail ang sender email para hindi i-block ng Brevo
        'sender' => ['name' => 'QR Equipment System', 'email' => 'rayanjayleoligao64@gmail.com'],
        'to' => [['email' => $email]],
        'subject' => 'Your Login OTP Code',
        'htmlContent' => '<p>Your OTP code is: <strong>' . $otp . '</strong></p>'
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $brevo_api_key,
        'content-type: application/json'
    ]);
    
    $response = curl_exec($ch);
    
    // Suriin kung nagka-error ang cURL
    if (curl_errno($ch)) {
        die('Curl error: ' . curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Kung hindi 200 o 201 ang status, ipapakita ang eksaktong error mula sa Brevo
    if ($http_code !== 201 && $http_code !== 200) {
        echo "<h3>Brevo API Error (HTTP Code: {$http_code})</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        exit();
    }

    // 4. I-redirect sa verify_otp.php kapag matagumpay na naipadala
    header("Location: verify_otp.php");
    exit();
}
?>
