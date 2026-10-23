<?php
session_start();

// -------------------------------------------------------------
// 1. DATABASE CONNECTION
// -------------------------------------------------------------
$host = 'localhost'; // Inayos mula 172.17.0.1 para sa Coolify
$user = 'root';
$pass = 'YOUR_DATABASE_PASSWORD'; // Palitan ng totoong database password mo
$db   = 'qrcode';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

// -------------------------------------------------------------
// 2. LOGIN FORM SUBMISSION
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        // I-fetch ang user sa database
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user_data = $result->fetch_assoc()) {
            // I-verify ang password (gamit ang password_verify o direct check depende sa setup mo)
            if (password_verify($password, $user_data['password']) || $password === $user_data['password']) {
                
                // Mag-generate ng 6-digit OTP
                $otp = rand(100000, 999999);
                
                // I-save ang user info at OTP sa session
                $_SESSION['temp_user'] = [
                    'id'    => $user_data['id'],
                    'name'  => $user_data['name'],
                    'email' => $user_data['email'],
                    'role'  => strtolower($user_data['role']), // e.g. 'borrower', 'custodian', 'admin'
                    'otp'   => $otp
                ];

                // -------------------------------------------------------------
                // 3. BREVO API OTP EMAIL SENDING
                // -------------------------------------------------------------
                $brevo_api_key = 'YOUR_BREVO_API_KEY'; // Palitan ng Brevo API Key mo

                $data = [
                    "sender" => [
                        "name"  => "Equipment Monitoring System",
                        "email" => "no-reply@yourdomain.com"
                    ],
                    "to" => [
                        [
                            "email" => $user_data['email'],
                            "name"  => $user_data['name']
                        ]
                    ],
                    "subject" => "Your Login OTP Code",
                    "htmlContent" => "<h3>Your Verification Code is: <b>{$otp}</b></h3>"
                ];

                $ch = curl_init("https://api.brevo.com/v3/smtp/email");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "accept: application/json",
                    "api-key: {$brevo_api_key}",
                    "content-type: application/json"
                ]);

                $response = curl_exec($ch);
                curl_close($ch);

                // I-redirect sa OTP Verification Page
                header("Location: verify_otp.php");
                exit();
            } else {
                $error_message = "Maling password! Subukan ulit.";
            }
        } else {
            $error_message = "Walang nahanap na account gamit ang email na iyan.";
        }
        $stmt->close();
    } else {
        $error_message = "Paki-fill up ang email at password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Equipment Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 360px; }
        .login-card h2 { margin-bottom: 20px; text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .alert { color: red; margin-bottom: 15px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Login System</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter password">
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
