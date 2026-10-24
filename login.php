<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($email)) {
        // 1. Gumawa ng 6-digit OTP
        $otp = rand(100000, 999999);
        
        // 2. Database logic
        $db_file = '/tmp/qrcode_database.sqlite';
        try {
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

            // 3. Resend API call
            $resend_api_key = 're_9moupy88_3ZfHSK7N8PdU1uLBavhH2dnD';
            
            $data = [
                'from' => 'QR Equipment System <onboarding@resend.dev>',
                'to' => [$email],
                'subject' => 'Your Login OTP Code - QR Equipment System',
                'html' => '
                    <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;">
                        <div style="max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px;">
                            <h3 style="color: #28a745; text-align: center;">QR EQUIPMENT SYSTEM</h3>
                            <p>Hello,</p>
                            <p>Here is your One-Time Password (OTP) to complete your login attempt:</p>
                            <h1 style="text-align: center; font-size: 36px; color: #007bff; background: #e9ecef; padding: 12px; border-radius: 5px;">' . $otp . '</h1>
                        </div>
                    </div>'
            ];

            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $resend_api_key,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            // PAG MAY ERROR SA EMAIL, TITINGNAN NATIN SA SCREEN
            if ($http_code !== 200 && $http_code !== 201) {
                echo "<div style='padding: 30px; font-family: Arial; background: #fff3cd; color: #856404; border: 1px solid #ffeba2; border-radius: 8px; margin: 50px auto; max-width: 600px;'>";
                echo "<h2>Email Delivery Debug Output</h2>";
                echo "<b>HTTP Status Code:</b> " . $http_code . "<br>";
                echo "<b>cURL Error:</b> " . ($curl_error ?: 'None') . "<br>";
                echo "<b>Resend Response:</b> <pre>" . htmlspecialchars($response) . "</pre>";
                echo "<hr>";
                echo "<p><b>Generated OTP for testing:</b> <span style='font-size: 20px; color: #d9534f;'>" . $otp . "</span></p>";
                echo "<a href='verify_otp.php' style='display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Proceed to Verify OTP Screen</a>";
                echo "</div>";
                exit();
            }

            header("Location: verify_otp.php");
            exit();

        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Equipment System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 90px; height: 90px; object-fit: contain; margin-bottom: 5px; border-radius: 50%; }
        .system-title { color: #28a745; font-size: 15px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
        .login-container h2 { margin-bottom: 5px; font-size: 20px; color: #333; }
        p { color: #666; font-size: 13px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background-color: #28a745; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.jpg" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="system-title">QR-BASED EQUIPMENT MONITORING SYSTEM</div>
        
        <h2>System Login</h2>
        <p>Enter your registered email address to receive an OTP.</p>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>
            </div>
            <button type="submit" class="btn-login">Send OTP Code</button>
        </form>
    </div>
</body>
</html>
