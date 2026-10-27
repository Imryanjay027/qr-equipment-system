<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrower Registration - MQCQC</title>
</head>
<body>
    <h2>Borrower Registration</h2>
    <form action="register_process.php" method="POST" id="regForm">
        <label>Full Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Education Level:</label><br>
        <select name="education_level" id="eduLevel" onchange="updateFields()">
            <option value="College">College</option>
            <option value="Elementary-SHS">Elementary to SHS</option>
        </select><br><br>

        <div id="infoField">
            <label>Course & Year:</label><br>
            <input type="text" name="course_year_section" required><br><br>
        </div>

        <!-- Live Selfie Section -->
        <label>Live Selfie Verification:</label><br>
        <video id="webcam" autoplay playsinline width="320" height="240" style="background:#ddd;"></video><br>
        <button type="button" onclick="captureSnapshot()">Capture Selfie</button><br><br>
        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
        <input type="hidden" name="profile_picture" id="profilePictureInput" required>

        <button type="submit">Register as Borrower</button>
    </form>

    <script>
        // Buksan ang Webcam
        const video = document.getElementById('webcam');
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => { alert("Hindi ma-access ang camera: " + err); });

        // Kuhaan ng litrato ang user
        function captureSnapshot() {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);
            const imageData = canvas.toDataURL('image/png');
            document.getElementById('profilePictureInput').value = imageData;
            alert("Na-capture na ang iyong live selfie!");
        }

        // Palitan ang label depende kung College o Elem-SHS
        function updateFields() {
            const level = document.getElementById('eduLevel').value;
            const infoField = document.getElementById('infoField');
            if (level === 'College') {
                infoField.innerHTML = '<label>Course & Year:</label><br><input type="text" name="course_year_section" required><br><br>';
            } else {
                infoField.innerHTML = '<label>Grade & Section:</label><br><input type="text" name="course_year_section" required><br><br>';
            }
        }
    </script>
</body>
</html>
