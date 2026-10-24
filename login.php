function sendOtpEmail($toEmail, $otpCode) {
    $smtpHost = 'ssl://smtp.gmail.com';
    $smtpPort = 465;
    $username = 'ryanjayleoligao64@gmail.com';
    $password = 'rtse abkj wqbz oxcg'; // Iyong Gmail App Password

    $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }

    $getResponse = function($socket) {
        $response = "";
        while ($str = fgets($socket, 512)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    };

    $getResponse($socket);

    fputs($socket, "EHLO localhost\r\n");
    $getResponse($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    $getResponse($socket);

    fputs($socket, base64_encode($username) . "\r\n");
    $getResponse($socket);

    fputs($socket, base64_encode($password) . "\r\n");
    $getResponse($socket);

    fputs($socket, "MAIL FROM: <$username>\r\n");
    $getResponse($socket);

    fputs($socket, "RCPT TO: <$toEmail>\r\n");
    $getResponse($socket);

    fputs($socket, "DATA\r\n");
    $getResponse($socket);

    $headers  = "From: Equipment Monitoring System <$username>\r\n";
    $headers .= "To: <$toEmail>\r\n";
    $headers .= "Subject: Your Login OTP Code\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message  = "<h2>Equipment Monitoring System</h2>";
    $message .= "<p>Ang iyong OTP Verification Code para makapag-login ay:</p>";
    $message .= "<h1 style='color: #2b6cb0; font-size: 32px;'>$otpCode</h1>";
    $message .= "<p>Valid ang code na ito sa loob ng 5 minuto. Huwag ipamahagi kanino man.</p>";

    $emailContent = $headers . "\r\n" . $message . "\r\n.\r\n";
    fputs($socket, $emailContent);
    $getResponse($socket);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}
