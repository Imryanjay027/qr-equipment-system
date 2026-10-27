<?php
// I-on ang error reporting para makita agad ang anumang isyu
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tawagin ang database connection (na may auto-create table)
require_once 'db_connection.php';

// Kunin ang email mula sa POST request (o para sa testing)
$email = $_POST['email'] ?? '';

if (empty($email)) {
    die("ryanjayleoligao64@gmail.com");
}

// Mag-generate ng 6-digit OTP
$otp = rand(100000, 999999);

try {
    // 1. I-save muna ang OTP sa database
    $stmt = $pdo->prepare("INSERT INTO otp_table (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$email, $otp]);

    // 2. Brevo API v3 Configuration
    // I-PASTE MO DITO SA LOOB NG QUOTES ANG IYONG BAGONG BREVO API KEY (yung xkeysib-...)
    $apiKey = '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';

    $url = 'https://api.brevo.com/v3/smtp/email';

    $data = [
        'sender' => [
            'name'  => 'MQCQC Equipment Monitoring',
            'email' => 'ang_registered_sender_email_mo@gmail.com' // Dapat verified sender email mo ito sa Brevo!
        ],
        'to' => [
            [
                'email' => $email
            ]
        ],
        'subject'     => 'Your OTP Code - MQCQC System',
        'htmlContent' => "<html><body><h2>MQCQC Equipment Monitoring System</h2><p>Ang iyong OTP code ay: <strong style='font-size: 20px; color: #007bff;'>$otp</strong></p><p>Mag-eexpire ito sa loob ng 5 minuto.</p></body></html>"
    ];

    // 3. Ipadala sa Brevo gamit ang cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo "<h3 style='color: red;'>cURL Error:</h3> " . $curlError;
    } else {
        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            // Matagumpay na naipadala - i-redirect agad sa verify_otp.php kasama ang email
            header("Location: verify_otp.php?email=" . urlencode($email));
            exit();
        } else {
            echo "<h3 style='color: red;'>Brevo API Error (HTTP Status $httpCode):</h3>";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        }
    }

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Database Error:</h3> " . $e->getMessage();
}
?>
