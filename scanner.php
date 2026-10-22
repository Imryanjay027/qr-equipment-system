<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1');
$port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$username = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root');$password = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$database = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'qrcode');

$conn = @new mysqli($host, $username,$password, $database, (int)$port);
if ($conn->connect_error) {$conn = @new mysqli('mysql', $username,$password, $database, (int)$port);
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

<!-- Audio element para sa beep sound -->
<audio id="beep-sound" src="beep.mp3" preload="auto"></audio>

<div class="container">
    <div class="scanner-card text-center">
        <h4 class="fw-bold mb-1 text-success"><i class="fas fa-qrcode me-2"></i>Custodian QR Scanner</h4>
        <p class="text-muted small mb-3">I-align ang QR code sa loob ng frame o mag-upload ng imahe</p>
        
        <!-- Area para sa QR Reader -->
        <div id="reader" style="min-height: 280px;"></div>

        <!-- Optional File Upload para sa QR Code image -->
        <div class="mt-3">
            <label for="qr-file-input" class="btn btn-outline-success btn-sm w-100">
                <i class="fas fa-upload me-2"></i>Mag-upload ng QR Code Image
            </label>
            <input type="file" id="qr-file-input" accept="image/*" class="d-none" onchange="scanQrFromFile(event)">
        </div>
        
        <div id="scan-result" class="mt-3 alert alert-info d-none" role="alert">
            <span id="result-text">Scanning...</span>
        </div>
    </div>
</div>

<script>
    function playBeep() {
        var beep = document.getElementById('beep-sound');
        if (beep) {
            beep.currentTime = 0;
            beep.play().catch(e => console.log("Audio play blocked:", e));
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        playBeep();

        html5QrCode.stop().then(() => {
            document.getElementById('scan-result').classList.remove('d-none');
            document.getElementById('result-text').innerText = "Scanned: " + decodedText;

            setTimeout(() => {
                window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
            }, 300);
        }).catch(err => {
            console.error("Failed to stop scanner.", err);
            window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
        });
    }

    function onScanFailure(error) {
        // Tahimik habang naghahanap ng frame para sa mabilis na performance
    }

    const html5QrCode = new Html5Qrcode("reader");
    
    const qrConfig = {
        fps: 25, 
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
        videoConstraints: {
            facingMode: "environment",
            width: { ideal: 1280 },
            height: { ideal: 720
