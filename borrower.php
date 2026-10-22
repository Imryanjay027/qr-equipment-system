<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Equipment Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .scanner-card { max-width: 500px; margin: 30px auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 20px; }
        #reader { width: 100%; border-radius: 8px; overflow: hidden; background-color: #000; }
        #reader video { object-fit: cover !important; }
    </style>
</head>
<body>

<div class="container">
    <div class="scanner-card text-center">
        <h4 class="fw-bold mb-1 text-success"><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h4>
        <p class="text-muted small mb-3">I-align ang QR code sa loob ng frame para mag-scan</p>
        
        <!-- Area para sa QR Reader -->
        <div id="reader" style="min-height: 280px;"></div>
        
        <div id="scan-result" class="mt-3 alert alert-info d-none" role="alert">
            <span id="result-text">Scanning...</span>
        </div>
    </div>
</div>

<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Kapag matagumpay na na-scan ang QR code
        document.getElementById('scan-result').classList.remove('d-none');
        document.getElementById('result-text').innerText = "Scanned: " + decodedText;

        // Halimbawa: I-redirect papuntang registration o borrower page gamit ang scanned QR value
        setTimeout(() => {
            window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
        }, 1000);
    }

    function onScanFailure(error) {
        // Karaniwang walang ginagawa rito habang patuloy na naghahanap ng QR code
    }

    // Optimization para sa Mobile Phones
    const html5QrCode = new Html5Qrcode("reader");
    
    // Configurations para mabilis at hindi mag-lag sa mobile
    const qrConfig = {
        fps: 15, // Ibaba sa 15 fps para mabilis mag-process ang browser at camera ng phone
        qrbox: { width: 220, height: 220 }, // Sukat ng scan box
        aspectRatio: 1.0
    };

    // Pwersahin ang paggamit ng back camera (environment facing mode)
    html5QrCode.start(
        { facingMode: "environment" }, 
        qrConfig, 
        onScanSuccess, 
        onScanFailure
    ).catch(err => {
        console.warn("Back camera fail, fallback to default camera:", err);
        // Fallback kung sakaling hindi mahanap ang environment camera
        html5QrCode.start(
            { facingMode: "user" }, 
            qrConfig, 
            onScanSuccess, 
            onScanFailure
        );
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
