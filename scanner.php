<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Portal - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <header class="bg-[#00381d] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 rounded-full border border-white object-cover bg-white" onerror="this.src='https://via.placeholder.com/40'">
                <div>
                    <h1 class="text-sm sm:text-base font-bold tracking-tight">QR SCANNER PORTAL</h1>
                    <p class="text-[11px] text-emerald-200">Mary the Queen College of Quezon City</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="custodian_dashboard.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-2 rounded-lg text-xs font-semibold transition shadow-sm">&larr; Dashboard</a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold transition shadow-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 flex flex-col items-center">
            <h2 class="text-base font-bold text-gray-800 w-full text-left mb-1">Live Camera Scanner</h2>
            <p class="text-xs text-gray-500 w-full text-left mb-3">Ituro ang camera sa QR Code ng Borrower para i-scan.</p>
            
            <div id="audio-unlock-banner" class="w-full mb-3 p-2.5 bg-amber-50 border border-amber-300 rounded-xl text-amber-800 text-xs flex justify-between items-center shadow-sm">
                <span>🔊 I-click para i-activate ang Audio Beep</span>
                <button id="unlock-btn" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg font-semibold transition">I-activate</button>
            </div>

            <div class="relative w-full bg-black rounded-xl overflow-hidden border-2 border-emerald-600 mb-4 max-w-md">
                <div id="reader" class="w-full"></div>
            </div>
            <p class="text-xs text-gray-500 italic">Naka-activate ang instant ultra-fast scanner...</p>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-1">Upload QR Image</h2>
                <input type="file" id="qr-file-input" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-lg p-2 bg-slate-50">
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4">Scan Result</h3>
                <div id="result-placeholder" class="p-6 bg-slate-50 border border-dashed border-gray-300 rounded-xl text-center text-xs text-gray-400">Wala pang na-scan o na-upload.</div>
                <div id="result-content" class="hidden space-y-4">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-bold">
                        Scan Successful: <span id="decoded-text-label" class="font-normal"></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <audio id="beep-audio" src="beep.mp3" preload="auto"></audio>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const html5QrCode = new Html5Qrcode("reader");
            const beepAudio = document.getElementById('beep-audio');
            const unlockBtn = document.getElementById('unlock-btn');
            const unlockBanner = document.getElementById('audio-unlock-banner');
            let audioCtx = null;

            unlockBtn.addEventListener('click', () => {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();
                beepAudio.play().then(() => {
                    beepAudio.pause();
                    beepAudio.currentTime = 0;
                    unlockBanner.style.display = 'none';
                }).catch(e => {});
            });

            const playBeep = () => {
                beepAudio.currentTime = 0;
                beepAudio.play().catch(e => {});
                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') audioCtx.resume();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.15);
                } catch(err) {}
            };

            const handleScanSuccess = (decodedText) => {
                playBeep();
                document.getElementById('result-placeholder').classList.add('hidden');
                document.getElementById('result-content').classList.remove('hidden');
                document.getElementById('decoded-text-label').innerText = decodedText;
                try { html5QrCode.stop().catch(err => {}); } catch(e) {}
            };

            // Itinakda sa 10 FPS at mas maliit na qrbox para maging instant at walang-lag sa cellphone
            const config = { 
                fps: 10, 
                qrbox: { width: 200, height: 200 }, 
                aspectRatio: 1.0,
                experimentalFeatures: { useBarCodeDetectorIfSupported: true }
            };

            html5QrCode.start({ facingMode: "environment" }, config, handleScanSuccess).catch(err => {
                html5QrCode.start({ facingMode: "user" }, config, handleScanSuccess).catch(e => {});
            });

            const fileInput = document.getElementById('qr-file-input');
            fileInput.addEventListener('change', e => {
                if (e.target.files.length === 0) return;
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);
                        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const code = jsQR(imgData.data, imgData.width, imgData.height);
                        if (code) {
                            handleScanSuccess(code.data);
                        } else {
                            alert("Hindi mabasa ang QR code.");
                        }
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            });
        });
    </script>
</body>
</html>
