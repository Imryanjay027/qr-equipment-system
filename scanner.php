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
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .scanner-card { max-width: 500px; margin: 30px auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 20px; }
        #reader { width: 100%; border-radius: 8px; overflow: hidden; background-color: #000; display: none; }
        #reader video { object-fit: cover !important; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="scanner-card text-center">
        <h4 class="fw-bold mb-1 text-success"><i class="fas fa-qrcode me-2"></i>Custodian QR Scanner</h4>
        <p class="text-muted small mb-3">I-align ang QR code sa loob ng frame para sa mabilisang pag-scan</p>
        
        <!-- Button para buksan ang Camera Scanner -->
        <div class="d-grid gap-2 mb-3">
            <button id="toggle-scanner" class="btn btn-success btn-sm fw-bold" onclick="startCameraScanner()">
                <i class="fas fa-camera me-2"></i>Buksan ang Camera Scanner
            </button>
        </div>

        <!-- Area para sa QR Reader (Camera View) -->
        <div id="reader" class="mb-3"></div>

        <!-- Pwede ring mag-upload ng QR Image File -->
        <div class="mb-3">
            <label for="qr-file-input" class="btn btn-outline-success btn-sm w-100 fw-bold">
                <i class="fas fa-upload me-2"></i>O kaya ay Mag-upload ng QR Code Image File
            </label>
            <input type="file" id="qr-file-input" accept="image/*" class="d-none" onchange="scanQrFromFile(event)">
        </div>

        <!-- Simulate Scan Button para sa pag-test -->
        <div class="d-grid gap-2 mb-2">
            <a href="registration.php?qr=456789" class="btn btn-warning btn-sm text-dark fw-bold" onclick="playBeep()">
                <i class="fas fa-bolt me-2"></i>Simulate Scan (Test Student: 456789)
            </a>
        </div>
        
        <div id="scan-result" class="mt-3 alert alert-info d-none" role="alert">
            <span id="result-text">Scanning...</span>
        </div>
    </div>
</div>

<script>
    // Web Audio API para sa awtomatiko at garantisadong tunog ng beep sa mobile
    function playBeep() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // Tono ng beep
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.15); // Haba ng tunog
        } catch (e) {
            console.log("Audio error:", e);
        }
    }

    let html5QrCode = null;

    function startCameraScanner() {
        const readerDiv = document.getElementById('reader');
        readerDiv.style.display = 'block';

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const qrConfig = {
            fps: 25, // Mabilis na frame rate para sa mobile
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            videoConstraints: {
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            qrConfig, 
            (decodedText) => {
                playBeep(); // Tutunog agad dito kapag nakabasa sa camera
                html5QrCode.stop().then(() => {
                    document.getElementById('scan-result').classList.remove('d-none');
                    document.getElementById('result-text').innerText = "Scanned: " + decodedText;
                    setTimeout(() => {
                        window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
                    }, 300);
                }).catch(() => {
                    window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
                });
            },
            (error) => {}
        ).catch(err => {
            alert("Hindi mabuksan ang camera. Subukan mag-upload ng image file.");
            console.error(err);
        });
    }

    // Function para sa pag-upload ng QR Code Image File
    function scanQrFromFile(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        html5QrCode.scanFile(file, true)
            .then(decodedText => {
                playBeep(); // Tutunog agad dito kapag matagumpay na nabasa mula sa in-upload na file
                document.getElementById('scan-result').classList.remove('d-none');
                document.getElementById('result-text').innerText = "Scanned File: " + decodedText;
                setTimeout(() => {
                    window.location.href = "registration.php?qr=" + encodeURIComponent(decodedText);
                }, 400);
            })
            .catch(err => {
                alert("Hindi mabasa ang QR code mula sa in-upload na larawan. Subukan ang iba.");
                console.error(err);
            });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
