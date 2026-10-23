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

    // 3. Pagpapadala ng OTP sa pamamagitan ng Brevo API gamit ang awtorisadong system sender
    $brevo_api_key = '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';
    
    $data = [
        'sender' => ['name' => 'QR Equipment System', 'email' => 'contact@sendinblue.com'],
        'to' => [['email' => $email]],
        'subject' => 'Your Login OTP Code',
        'htmlContent' => '
            <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;">
                <div style="max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <h3 style="color: #28a745; text-align: center;">QR EQUIPMENT SYSTEM</h3>
                    <p>Hello,</p>
                    <p>Here is your One-Time Password (OTP) to complete your login:</p>
                    <h1 style="text-align: center; font-size: 32px; letter-spacing: 4px; color: #007bff; background: #e9ecef; padding: 10px; border-radius: 5px;">' . $otp . '</h1>
                    <p style="font-size: 12px; color: #666; text-align: center; margin-top: 20px;">If you did not request this, please ignore this email.</p>
                </div>
            </div>'
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
    curl_close($ch);

    // 4. I-redirect sa verify_otp.php
    header("Location: verify_otp.php");
    exit();
}
?>
