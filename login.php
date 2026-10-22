<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    $conn = @new mysqli('mysql', $username, $password, $database, (int)$port);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Siguraduhing may OTP columns sa tbl_users o users
try { @$conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE users ADD COLUMN otp_expiry DATETIME DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE tbl_users ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL"); } catch (Exception $e) {}
try { @$conn->query("ALTER TABLE tbl_users ADD COLUMN otp_expiry DATETIME DEFAULT NULL"); } catch (Exception $e) {}

$error = "";
$redirect_url = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_or_id'])) {
    $email_or_id = trim($_POST['email_or_id'] ?? '');

    if (!empty($email_or_id)) {
        // Kunin ang mga tunay na columns sa tbl_users para sigurado sa pag-query
        $table_columns = [];
        $res = $conn->query("SHOW COLUMNS FROM tbl_users");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $table_columns[] = $row['Field'];
            }
        }

        // Bumuo ng dynamic query base sa kung anong columns ang mayroon sa tbl_users
        $where_clauses = ["TRIM(email) = TRIM(?)"];
        $bind_types = "s";
        $bind_values = [$email_or_id];

        if (in_array('student_id', $table_columns)) {
            $where_clauses[] = "TRIM(student_id) = TRIM(?)";
            $bind_types .= "s";
            $bind_values[] = $email_or_id;
        }

        $sql = "SELECT * FROM tbl_users WHERE " . implode(" OR ", $where_clauses) . " LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($bind_types, ...$bind_values);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $user_id = $user['id'];

                $otp = sprintf("%06d", rand(0, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Update OTP sa tbl_users gamit ang id
                $update_stmt = $conn->prepare("UPDATE tbl_users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $otp, $expiry, $user_id);
                $update_stmt->execute();

                $target_email = trim($user['email']);
                $brevo_api_key = "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"; 

                // Hanapin kung full_name o fullname ang column ng pangalan
                $name_key = isset($user['full_name']) ? 'full_name' : (isset($user['fullname']) ? 'fullname' : 'full_name');
                $user_name = $user[$name_key] ?? 'User';

                $email_data = [
                    "sender" => [
                        "name" => "MQCQC Equipment System",
                        "email" => "ryanjayleoligao64@gmail.com"
                    ],
                    "to" => [ ["email" => $target_email] ],
                    "subject" => "Your Login OTP Code",
                    "htmlContent" => "<p>Hello {$user_name},</p><p>Your 6-digit OTP code is: <strong>{$otp}</strong></p><p>This code will expire in 15 minutes.</p>"
                ];

                $ch = curl_init('https://api.brevo.com/v3/smtp/email');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'accept: application/json',
                    'api-key: ' . $brevo_api_key,
                    'content-type: application/json'
                ]);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code >= 200 && $http_code < 300) {
                    // I-save ang user ID at i-store ang role sa session para magamit sa pag-verify at redirect
                    $_SESSION['pending_user_id'] = $user_id;
                    $_SESSION['pending_role'] = $user['role'] ?? 'borrower';
                    $redirect_url = "verify_otp.php";
                } else {
                    $error = "Failed to send OTP via email.";
                }
            } else {
                $error = "No account found with that email or student ID.";
            }
            $stmt->close();
        }
    } else {
        $error = "Please enter your email or student ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MQCQC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .login-card { max-width: 420px; margin: 50px auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; }
        .logo-img { width: 80px; height: 80px; object-fit: contain; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="login-card text-center">
        <!-- School Logo with Fallback -->
        <div class="mb-3">
            <img src="logo.jpg" 
                 onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/en/8/82/Mary_the_Queen_College_of_Quezon_City_logo.png';" 
                 alt="MQCQC Logo" 
                 class="logo-img">
        </div>
        <h4 class="fw-bold mb-1 text-success">Equipment Monitoring</h4>
        <p class="text-muted small mb-4">Enter your Email or Student ID to receive OTP</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label for="email_or_id" class="form-label small fw-bold">Email o Student ID</label>
                <input type="text" class="form-control" id="email_or_id" name="email_or_id" placeholder="Ilagay ang email o ID" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold py-2">
                    <i class="fas fa-paper-plane me-2"></i>Send OTP Code
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($redirect_url)): ?>
<script>
    window.location.href = "<?= $redirect_url; ?>";
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
