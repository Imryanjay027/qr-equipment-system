<?php
session_start();

// Kung walang temporary email sa session, ibalik sa login
if (!isset($_SESSION['temp_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['temp_email'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');

    // Database Connection - Gamitin ang 127.0.0.1 para sa TCP connection sa Coolify container
    $host = '127.0.0.1';
    $db   = 'qr_equipment_db';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Suriin ang OTP at expiration sa database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            $current_time = date("Y-m-d H:i:s");
            
            if ($user_data['otp_code'] === $entered_otp) {
                if ($current_time <= $user_data['otp_expires_at']) {
                    // Tama ang OTP at hindi pa expired! Mag-login na nang tuluyan.
                    $_SESSION['user_id'] = $user_data['id'];
                    $_SESSION['role'] = $user_data['role'] ?? 'custodian';
                    $_SESSION['email'] = $user_data['email'];
                    
                    // Linisin ang temp session
                    unset($_SESSION['temp_email']);

                    // I-redirect base sa role (Custodian Dashboard o Admin Dashboard)
                    $role = $user_data['role'] ?? 'custodian';
                    if ($role === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: custodian_dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Nag-expire na ang iyong OTP code. Mangyaring humingi muli ng bago.";
                }
            } else {
                $error = "Mali ang OTP code na iyong inilagay. Subukan muli.";
            }
        } else {
            $error = "May naganap na error. Hindi mahanap ang account.";
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - QR Equipment Monitoring System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .otp-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 { color: #333; margin-bottom: 10px; }
        p { color: #666; font-size: 14px; margin-bottom: 20px; }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 3px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
        .error { color: #dc3545; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="otp-container">
    <h2>Enter Verification Code</h2>
    <p>Ipinadala namin ang 6-digit OTP code sa iyong email: <b><?php echo htmlspecialchars($email); ?></b></p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="verify_otp.php" method="POST">
        <input type="text" name="otp" placeholder="------" maxlength="6" pattern="\d{6}" required autofocus>
        <button type="submit">Verify OTP</button>
    </form>
</div>

</body>
</html>
