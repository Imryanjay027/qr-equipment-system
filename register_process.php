<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['full_name'] ?? $_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? 'borrower');
    $course_year = trim($_POST['course_year'] ?? $_POST['course_year_section'] ?? '');
    $profile_picture_base64 = $_POST['selfie_base64'] ?? $_POST['profile_picture'] ?? '';

    if (empty($student_id) || empty($name) || empty($email) || empty($password) || empty($profile_picture_base64)) {
        echo "<script>alert('Lahat ng field at ang live selfie ay kinakailangan!'); window.history.back();</script>";
        exit();
    }

    try {
        // I-check kung may kaparehong student_id o email na
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE student_id = ? OR email = ?");
        $checkStmt->execute([$student_id, $email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('Ang Student ID o Email na ito ay nakarehistro na!'); window.history.back();</script>";
            exit();
        }

        // I-convert ang base64 selfie patungong `.jpg` file at i-save sa uploads/ folder
        $selfie_path = 'logo.jpg';
        if (!empty($profile_picture_base64)) {
            $data = $profile_picture_base64;
            $data = str_replace('data:image/jpeg;base64,', '', $data);
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded_data = base64_decode($data);
            
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $student_id) . '_selfie.jpg';
            $selfie_path = 'uploads/' . $file_name;
            @file_put_contents(__DIR__ . '/' . $selfie_path, $decoded_data);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // I-save sa database ang mga impormasyon ng user kasama ang tamang file path ng selfie
        $stmt = $pdo->prepare("INSERT INTO users (student_id, name, email, password, role, course_year_section, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $name, $email, $hashedPassword, $role, $course_year, $selfie_path]);

        echo "<script>alert('Matagumpay ang pag-rehistro ng account!'); window.location.href='login.php';</script>";

    } catch (PDOException $e) {
        echo "<script>alert('Database Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>
