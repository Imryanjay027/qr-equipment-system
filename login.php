<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// I-include ang iyong database connection
require_once 'db.php'; // Siguraduhing tama ang pangalan ng file ng database connection mo

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_id = trim($_POST['email_or_id'] ?? '');

    if (!empty($email_or_id)) {
        // Hanapin ang user sa database (halimbawa ay sa table na 'users' o kung saan nakalagay ang accounts)
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        $stmt->bind_param("ss", $email_or_id, $email_or_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // 1. Mag-generate ng 6-digit OTP
            $otp = rand(100000, 999999);
            
            // 2. I-save sa Session (5 minutes expiration)
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; 
            $_SESSION['temp_email'] = $email_or_id;

            // Kunin ang totoong data mula sa database galing sa nahanap na user
            $_SESSION['temp_user_id'] = $user['id']; // o kung ano ang column name ng user ID mo
            $_SESSION['temp_role'] = strtolower(trim($user['role'])); // 'student' o 'custodian'
            $_SESSION['temp_full_name'] = $user['full_name'];
            $_SESSION['temp_student_id'] = $user['student_id'] ?? '';
            $_SESSION['temp_course_year'] = $user['course_year'] ?? '';

            // 3. Pagpapadala ng OTP gamit ang Brevo API (cURL)
            $brevo_api_key = "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"; 
            
            $email_data = [
                "sender" => [
                    "name" => "MQCQC Equipment System",
                    "email" => "ryanjayleoligao64@gmail.com"
                ],
                "to" => [
                    [
                        "email" => $email_or_id // O kunin ang saktong email mula sa $user['email'] kung ID ang inilagay
                    ]
                ],
                "subject" => "Your Login OTP Code",
                "htmlContent" => "<p>Hello,</p><p>Your 6-digit OTP code for system login is: <strong>{$otp}</strong></p><p>This code will expire in 5 minutes.</p>"
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
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($http_code >= 200 && $http_code < 300) {
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Brevo Error (HTTP $http_code): " . ($curl_error ? $curl_error : $response);
            }
        } else {
            $error = "Email or Student ID not found in the system.";
        }
    } else {
        $error = "Please enter your email or Student ID.";
    }
}
?>
