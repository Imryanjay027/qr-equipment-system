<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Portal - MQCQC EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans">

    <!-- Header Navigation -->
    <header class="bg-[#00381d] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 rounded-full border border-white object-cover bg-white" onerror="this.src='https://via.placeholder.com/40'">
                <div>
                    <h1 class="text-sm sm:text-base font-bold tracking-tight">QR SCANNER PORTAL</h1>
                    <p class="text-[11px] text-emerald-200">Mary the Queen College of Quezon City</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-semibold">Property Custodian</p>
                    <p class="text-[10px] text-emerald-300 uppercase">Custodian Dashboard</p>
                </div>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-sm">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left Column: Live Camera Scanner -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 flex flex-col items-center">
            <h2 class="text-base font-bold text-gray-800 w-full text-left mb-1">Live Camera Scanner</h2>
            <p class="text-xs text-gray-500 w-full text-left mb-4">Ituro ang camera sa QR Code ng Borrower para i-scan.</p>

            <div class="relative w-full bg-black rounded-xl overflow-hidden border-2 border-emerald-600 mb-4 max-w-md">
                <div id="reader" class="w-full"></div>
            </div>
            <p id="scanner-status" class="text-xs text-gray-500 italic">Naka-activate ang camera scanner...</p>
        </div>

        <!-- Right Column: Upload QR Image & Result -->
        <div class="space-y-6">
            <!-- Upload QR Image Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-1">Upload QR Image</h2>
                <p class="text-xs text-gray-500 mb-4">May saved na QR code image ang borrower? I-upload ito dito para ma-scan.</p>
                
                <input type="file" id="qr-file-input" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-lg p-2 bg-slate-50">
            </div>

            <!-- Scan Result Container -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4">Scan Result / Borrower Data</h3>
                
                <div id="result-placeholder" class="p-6 bg-slate-50 border border-dashed border-gray-300 rounded-xl text-center text-xs text-gray-400">
                    Wala pang na-scan o na-upload na QR code.
                </div>

                <div id="result-content" class="hidden space-y-4">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-bold flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Scan Successful &nbsp;|&nbsp; <span id="decoded-text-label" class="font-normal"></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 items-center bg-slate-50 p-4 rounded-xl border border-gray-200">
                        <!-- Dito ipapakita ang Profile/Selfie ng Borrower (HINDI ang QR code image) -->
                        <img id="borrower-selfie" src="logo.jpg" alt="Borrower Photo" class="w-20 h-20 rounded-full border-2 border-emerald-600 object-cover shadow-md" onerror="this.src='https://via.placeholder.com/80'">
                        
                        <div class="text-xs space-y-1 w-full">
                            <p class="font-bold text-gray-800 text-sm" id="res-name">Pangalan: --</p>
                            <p class="text-gray-600" id="res-id">Student ID: --</p>
                            <p class="text-gray-600" id="res-course">Course & Year: --</p>
                            <p class="text-gray-600" id="res-item">Equipment to Borrow: --</p>
                            <p class="text-gray-500 text-[11px]" id="res-dates">Borrow Date: --</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500 mt-auto">
        QR-Based Equipment Monitoring System &copy; 2026 - Mary the Queen College of Quezon City
    </footer>

    <!-- Script para sa Live Camera at File Upload Scanning -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const html5QrCode = new Html5Qrcode("reader");

            // Function na magpo-process kapag nagtagumpay ang pag-scan (Live man o File Upload)
            const handleScanSuccess = (decodedText) => {
                // Patugtugin ang beep.mp3 agad
                const audio = new Audio('beep.mp3');
                audio.play().catch(e => console.log("Audio play blocked by browser policy:", e));

                // I-update ang UI para ipakita ang resulta at itago ang placeholder
                document.getElementById('result-placeholder').classList.add('hidden');
                document.getElementById('result-content').classList.remove('hidden');
                document.getElementById('decoded-text-label').innerText = decodedText;

                // Halimbawa ng pag-populate ng data mula sa na-scan na QR text
                document.getElementById('res-name').innerText = "Pangalan: Ryan Jay Leoligao";
                document.getElementById('res-id').innerText = "Student ID: " + decodedText;
                document.getElementById('res-course').innerText = "Course & Year: BSIT 3rd Year";
                document.getElementById('res-item').innerText = "Equipment to Borrow: Extension Cord / Projector";
                document.getElementById('res-dates').innerText = "Borrow Date: " + new Date().toLocaleString();

                // Ihinto ang live camera kung sakaling live scan ang ginamit
                try {
                    html5QrCode.stop().catch(err => {});
                } catch(e) {}
            };

            // 1. Live Camera Configuration (30 FPS at Back Camera Priority)
            const config = { 
                fps: 30, 
                qrbox: { width: 250, height: 250 }, 
                aspectRatio: 1.0,
                experimentalFeatures: { useBarCodeDetectorIfSupported: true }
            };

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    let selectedCameraId = devices[0].id;
                    for (let i = 0; i < devices.length; i++) {
                        const label = devices[i].label.toLowerCase();
                        if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
                            selectedCameraId = devices[i].id;
                            break;
                        }
                    }
                    html5QrCode.start(selectedCameraId, config, handleScanSuccess).catch(err => {
                        console.error("Unable to start live camera:", err);
                    });
                }
            }).catch(err => {
                console.error("No camera detected:", err);
            });

            // 2. File Upload QR Scanning (Hindi ipapakita ang QR image, kundi ang Borrower data lamang)
            const fileInput = document.getElementById('qr-file-input');
            fileInput.addEventListener('change', e => {
                if (e.target.files.length === 0) return;
                const imageFile = e.target.files[0];

                html5QrCode.scanFile(imageFile, true)
                    .then(decodedText => {
                        handleScanSuccess(decodedText);
                    })
                    .catch(err => {
                        alert("Hindi mabasa ang QR code mula sa larawan. Subukang mag-upload ng mas malinaw na QR image.");
                        console.error("Error scanning file:", err);
                    });
            });
        });
    </script>
</body>
</html>
