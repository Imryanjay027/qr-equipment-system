<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h1>POST Request Succeeded!</h1>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Login Form</title>
</head>
<body>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Enter your Email" required>
        <button type="submit">Test Send OTP</button>
    </form>
</body>
</html>
