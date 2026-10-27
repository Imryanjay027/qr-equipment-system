<script>
    const video = document.getElementById('webcam');

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 } });
            video.srcObject = stream;
            await video.play();
            console.log("Camera started successfully");
        } catch (err) {
            console.error("Camera error details: ", err);
            alert("Hindi ma-access ang camera: " + err.message + "\nSubukan i-refresh o i-check ang browser permissions.");
        }
    }

    // Tawagin agad pagka-load
    window.addEventListener('DOMContentLoaded', () => {
        startCamera();
    });

    function toggleRoleFields() {
        const role = document.getElementById('roleSelect').value;
        const studentFields = document.getElementById('studentFields');
        const staffFields = document.getElementById('staffFields');
        const profileInput = document.getElementById('profilePictureInput');

        if (role === 'student') {
            studentFields.style.display = 'block';
            staffFields.style.display = 'none';
            profileInput.required = true;
            startCamera();
        } else {
            studentFields.style.display = 'none';
            staffFields.style.display = 'block';
            profileInput.required = false;
            // I-stop ang camera stream kung hindi student para hindi kumain ng resource
            if (video.srcObject) {
                let tracks = video.srcObject.getTracks();
                tracks.forEach(track => track.stop());
            }
        }
    }

    function captureSnapshot() {
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 260, 180);
        const imageData = canvas.toDataURL('image/png');
        document.getElementById('profilePictureInput').value = imageData;
        alert("Live selfie captured successfully!");
    }

    function updateEduFields() {
        const level = document.getElementById('eduLevel').value;
        const infoField = document.getElementById('infoField');
        if (level === 'College') {
            infoField.innerHTML = '<label>Course & Year</label><input type="text" name="course_year_section" placeholder="BSIT 3-A">';
        } else {
            infoField.innerHTML = '<label>Grade & Section</label><input type="text" name="course_year_section" placeholder="Grade 11 - St. Joseph">';
        }
    }
</script>
