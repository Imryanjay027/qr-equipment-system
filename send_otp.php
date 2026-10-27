<?php
// 1. I-on ang error reporting para makita natin kapag may sablay sa execution
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Tawagin ang database connection
require_once 'db_connection.php';

// Kunin ang email mula sa POST request (o para sa testing)
$email = $_POST['email'] ?? '';

if (empty($email)) {
    die("Pakilagay ang iyong email address.");
}

// 3. Mag-generate ng 6-digit OTP
$otp = rand(100000, 999999);

try {
    // 4. I-save muna ang OTP sa database
    $stmt = $pdo->prepare("INSERT INTO otp_table (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$email, $otp]);

    // 5. Brevo API v3 Configuration
    // I-PASTE MO DITO ANG IYONG BREVO API KEY (yung xkeysib-...)
    $apiKey = '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';

    $url = 'https://api.brevo.com/v3/smtp/email';

    // Ihanda ang payload/data para sa Brevo API
    $data = [
        'sender' => [
            'name'  => 'MQCQC Equipment Monitoring',
            'email' => 'ryanjayleoligao64@gmail.com' // DAPAT verified sender email mo ito sa Brevo!
        ],
        'to' => [
            [
                'email' => $email
            ]
        ],
        'subject'     => 'Your OTP Code - MQCQC System',
        'htmlContent' => "<html><body><h2>MQCQC Equipment Monitoring System</h2><p>Ang iyong OTP code ay: <strong style='font-size: 20px; color: #007bff;'>$otp</strong></p><p>Mag-eexpire ito sa loob ng 5 minuto.</p></body></html>"
    ];

    // 6. Ipadala sa Brevo gamit ang cURL
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
            echo "<h3 style='color: green;'>Success: OTP sent successfully to $email!</h3>";
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
