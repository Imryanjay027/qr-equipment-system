<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $education_level = trim($_POST['education_level'] ?? '');
    $course_year_section = trim($_POST['course_year_section'] ?? '');
    $profile_picture = $_POST['profile_picture'] ?? ''; // Base64 image data

    if (empty($name) || empty($email) || empty($password) || empty($profile_picture)) {
        echo "<script>alert('Lahat ng field at ang live selfie ay kinakailangan!'); window.history.back();</script>";
        exit();
    }

    try {
        // I-check kung may kaparehong email na
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('Ang email na ito ay nakarehistro na!'); window.history.back();</script>";
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'borrower'; // Naka-set bilang borrower

        // I-save sa database kabilang ang mga bagong impormasyon
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, education_level, course_year_section, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role, $education_level, $course_year_section, $profile_picture]);

        echo "<script>alert('Matagumpay ang pag-rehistro ng borrower!'); window.location.href='login.php';</script>";

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>
