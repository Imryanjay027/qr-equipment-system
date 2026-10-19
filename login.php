<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Isama ang PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} elseif (file_exists('PHPMailer/src/Exception.php')) {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
}

if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    die("Error: db_connection.php file not found.");
}

// 1. Siguraduhing nandiyan ang app_users table at may role column
$conn->query("CREATE TABLE IF NOT EXISTS app_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(50) DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$check_column =$conn->query("SHOW COLUMNS FROM app_users LIKE 'role'");
if ($check_column && $check_column->num_rows == 0) {$conn->query("ALTER TABLE app_users ADD COLUMN role VARCHAR(50) DEFAULT 'student'");
}

// 2. Mahigpit na Whitelist para sa 2 Custodian lamang (Ikaw at si Ma'am Prudence)
$allowed_custodians = [
    'ryanjayleoligao64@gmail.com',
    'banaresprudence13@gmail.com'
];

// I-reset muna ang ibang lumang custodians para kayong dalawa lang ang may ganitong access
$conn->query("UPDATE app_users SET role = 'student' WHERE role = 'custodian' AND email NOT IN ('ryanjayleoligao64@gmail.com', 'banaresprudence13@gmail.com')");

// I-register o i-update ang dalawang opisyal na custodian
foreach ($allowed_custodians as$c_email) {
    $check_user =$conn->prepare("SELECT id FROM app_users WHERE email = ?");
    $check_user->bind_param("s", $c_email);$check_user->execute();
    $check_result =$check_user->get_result();

    if ($check_result->num_rows === 0) {
        $insert_user =$conn->prepare("INSERT INTO app_users (email, role) VALUES (?, 'custodian')");
        $insert_user->bind_param("s", $c_email);
        $insert_user->execute();$insert_user->close();
    } else {
        $update_role =$conn->prepare("UPDATE app_users SET role = 'custodian' WHERE email = ?");
        $update_role->bind_param("s", $c_email);
        $update_role->execute();$update_role->close();
    }
    $check_user->close();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt =$conn->prepare("SELECT * FROM app_users WHERE email = ?");
        
        if ($stmt) {$stmt->bind_param("s", $email);$stmt->execute();
            $result =$stmt->get_result();

            if ($result->num_rows > 0) {
                $user =$result->fetch_assoc();
                
                // Siguraduhing custodian lamang ang makakapag-login sa pahinang ito
                if ($user['role'] !== 'custodian') {$error = "Access denied. This login page is strictly for Property Custodians only.";
                } else {
                    // Generate 6-digit OTP
                    $otp = rand(100000, 999999);
                    $_SESSION['otp'] =$otp;
                    $_SESSION['login_email'] =$email;
                    $_SESSION['user_role'] =$user['role'];

                    // --- CONFIGURATION NG GMAIL SMTP ---
                    $sender_email = 'ryanjayleoligao64@gmail.com';
                    $sender_app_password = 'jusc txaa dikf grvz'; // Bagong App Password mo

                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';$mail->SMTPAuth   = true;
                        $mail->Username   =$sender_email;
                        $mail->Password   =$sender_app_password;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Recipients
                        $mail->setFrom($sender_email, 'Equipment Monitoring System');
                        $mail->addAddress($email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Login OTP - Equipment Monitoring System';$mail->Body    = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                                <h2 style='color: #2e7d32;'>Equipment Monitoring System</h2>
                                <p>Hello,</p>
                                <p>Your One-Time Password (OTP) for logging in is:</p>
                                <h1 style='color: #2e7d32; letter-spacing: 5px;'>{$otp}</h1>
                                <p>This code is valid for single use only. Do not share this code with anyone.</p>
                            </div>
                        ";

                        $mail->send();

                        // Redirect sa verify_otp.php kapag naipadala na ang email
                        header("Location: verify_otp.php");
                        exit();

                    } catch (Exception $e) {$error = "Failed to send OTP email. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            } else {
                $error = "This email address is not registered in the system.";
            }
            $stmt->close();
        } else {
            $error = "Database query error: " . $conn->error;
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Based Equipment Monitoring System - Login</title>
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
        .login-card {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .
