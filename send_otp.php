<?php
// send_otp.php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email = $_SESSION['email'] ?? ''; 
if (empty($email)) {
    header("Location: register.php");
    exit();
}

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // Ang iyong tamang Gmail account
    $mail->Username   = 'leoligaoryanjay.araosds@gmail.com';
    
    // Ang nabuo mong 16-digit App Password
    $mail->Password   = 'gdzodeuajljftewy';
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Pinalitan ang Sender Name para maging "MQCQC System"
    $mail->setFrom('leoligaoryanjay.araosds@gmail.com', 'MQCQC System');
    $mail->addAddress($email);

    // I-embed ang logo.jpg para lumabas bilang school logo sa email body
    // Siguraduhing may file na 'logo.jpg' sa parehong folder o itama ang path (hal. 'assets/logo.jpg')
    $mail->addEmbeddedImage('logo.jpg', 'school_logo');

    // Ayusin ang SSL verification error para sa localhost
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->isHTML(true);
    $mail->Subject = 'Your Login Verification OTP - MQCQC System';
    $mail->Body    = '
    <div style="background-color: #f4f6f9; padding: 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 500px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e0e0e0; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            
            <!-- Embedded School Logo -->
            <img src="cid:school_logo" alt="MQCQC Logo" style="width: 90px; height: auto; margin-bottom: 15px;">
            
            <h2 style="color: #1a365d; margin: 0; font-size: 20px;">Mary The Queen College of Quezon City</h2>
            <p style="color: #666666; font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 5px; margin-bottom: 20px;">Equipment Borrowing System</p>
            
            <hr style="border: none; border-top: 1px solid #eeeeee; margin: 20px 0;">
            
            <p style="text-align: left; color: #333333; font-size: 14px; margin-bottom: 5px;">Dear User,</p>
            <p style="text-align: left; color: #333333; font-size: 14px; margin-top: 0;">Here is your One-Time Password (OTP) required to complete your secure registration:</p>
            
            <!-- OTP Display Box -->
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin: 20px 0; display: inline-block; width: 80%; border: 1px solid #ddd;">
                <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #1a365d;">' . $otp . '</span>
            </div>
            
            <p style="color: #888888; font-size: 12px; margin-top: 30px;">Kung hindi ka humiling nito, mangyaring balewalain ang email na ito.</p>
        </div>
    </div>';

    $mail->AltBody = "Mary The Queen College of Quezon City - Equipment Borrowing System\n\nDear User,\n\nHere is your One-Time Password (OTP): " . $otp . "\n\nKung hindi ka humiling nito, balewalain ang email na ito.";

    $mail->send();
    header("Location: verify_otp.php");
    exit();

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>