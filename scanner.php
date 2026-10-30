<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast QR Scanner - MQCQC EMS</title>
    <!-- Tailwind CSS para sa mabilis at malinis na design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-slate-900 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-6 text-center">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Scan Equipment QR Code</h2>
        <p class="text-xs text-gray-500 mb-4">Itutok ang camera sa QR code ng kagamitan.</p>

        <!-- QR Reader Container -->
        <div class="relative w-full bg-black rounded-xl overflow-hidden border-2 border-emerald-600 mb-4">
            <div id="reader" class="w-full"></div>
        </div>

        <!-- Form para sa pagpasa ng Na-scan na Code patungo sa processing file -->
        <form id="qr-form" action="process_borrow.php" method="POST" class="space-y-3">
            <input type="hidden" name="qr_code" id="qr_input">
            <div id="scan-result-container" class="hidden p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-semibold">
                Successfully scanned: <span id="scanned-text" class="font-bold"></span>
            </div>
            <button type="submit" id="submit-btn" class="hidden w-full bg-[#005a2b] hover:bg-[#004822] text-white py-2.5 rounded-lg text-xs font-semibold transition shadow-md">
                Proceed with Scanned Equipment
            </button>
        </form>

        <div class="mt-4">
            <a href="borrower.php" class="text-xs text-gray-500 hover:text-emerald-700 underline">&larr; Back to Dashboard</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const html5QrCode = new Html5Qrcode("reader");

            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                // Agad na tutugtog ang beep.mp3 sa sandaling ma-scan ang QR code
                const audio = new Audio('beep.mp3');
                audio.play().catch(e => console.log("Audio play blocked by browser policy:", e));

                // Ilalagay ang na-scan na text sa hidden input at ipapakita ang UI result
                document.getElementById('qr_input').value = decodedText;
                document.getElementById('scanned-text').innerText = decodedText;
                document.getElementById('scan-result-container').classList.remove('hidden');
                document.getElementById('submit-btn').classList.remove('hidden');

                // Kusang hihinto ang camera scanner para maiwasan ang double scan o looping
                html5QrCode.stop().then(() => {
                    console.log("QR Code scanning stopped successfully.");
                }).catch(err => {
                    console.error("Failed to stop scanning.", err);
                });
            };

            // Pinabilis at naka-optimize na configuration para sa mobile at desktop
            const config = { 
                fps: 30, // 30 Frames Per Second para sa super smooth at instant detection
                qrbox: { width: 250, height: 250 }, // Nakatutok sa gitnang kahon
                aspectRatio: 1.0,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true // Gagamitin ang native hardware acceleration ng device kung available
                }
            };

            // Pagkuha ng available cameras (Priiority ang back camera / environment para sa phones)
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    const cameraId = devices[devices.length - 1].id;
                    
                    html5QrCode.start(
                        cameraId, 
                        config, 
                        qrCodeSuccessCallback
                    ).catch(err => {
                        console.error("Unable to start scanning.", err);
                    });
                }
            }).catch(err => {
                console.error("No camera detected or permission denied.", err);
            });
        });
    </script>
</body>
</html>
