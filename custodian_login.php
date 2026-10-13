<?php
session_start();

// Kung naka-login na ang custodian, huwag na siyang pag-log inin, diretsong ibato sa dashboard
if (isset($_SESSION['custodian_logged_in']) && $_SESSION['custodian_logged_in'] === true) {
    header("Location: custodian.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "qrcode"; 
$port = 3307; // Palitan ng 3306 kung port 3306 ang XAMPP MySQL mo, o panatilihin kung 3307

// Pagkonekta sa database na may kasamang port
$conn = mysqli_init();
@$conn->real_connect($host, $username, $password, $database, $port);

// Fallback kung sakaling 3306 ang default ng XAMPP mo at nag-fail ang 3307
if ($conn->connect_error) {
    $port = 3306;
    $conn = new mysqli($host, $username, $password, $database, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    // Hanapin ang custodian gamit ang email
    $stmt = $conn->prepare("SELECT id, fullname, password FROM custodians WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // I-verify ang password (kung naka-hash o plain text, para safe pwede nating i-check pareho)
        if (password_verify($pass, $row['password']) || $pass === $row['password']) {
            // Success ang login, i-save sa session
            $_SESSION['custodian_logged_in'] = true;
            $_SESSION['custodian_id'] = $row['id'];
            $_SESSION['custodian_name'] = $row['fullname'];
            
            // Diretso sa custodian page / dashboard
            header("Location: custodian.php");
            exit();
        } else {
            $error_msg = "Maling password. Subukan muli.";
        }
    } else {
        $error_msg = "Walang rehistradong custodian na may ganitong email.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Login - Mary The Queen College</title>
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
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            text-align: center;
            color: #1f3a60;
            margin-bottom: 25px;
            font-size: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .btn-login {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #218838;
        }
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 13px;
            border: 1px solid #f5c6cb;
        }
        .back-home {
            text-align: center;
            margin-top: 20px;
        }
        .back-home a {
            color: #007bff;
            text-decoration: none;
            font-size: 13px;
        }
        .back-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Property Custodian Portal</h2>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Custodian Email Address:</label>
                <input type="email" id="email" name="email" required placeholder="hal. custodian@mtqc.edu.ph">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Maglagay ng password">
            </div>

            <button type="submit" class="btn-login">Mag-log In sa Portal</button>
        </form>

        <div class="back-home">
            <a href="welcome.php">← Bumalik sa Main Home / Portal</a>
        </div>
    </div>

</body>
</html>