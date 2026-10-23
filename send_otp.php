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
    $_SESSION['debug_otp'] = $otp; // Direktang gagamitin para sa mabilisang pagpasok

    // 3. I-redirect sa verify_otp.php
    header("Location: verify_otp.php");
    exit();
}
?>
