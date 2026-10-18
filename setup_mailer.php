<?php
// setup_mailer.php
if (!is_dir('phpmailer')) {
    mkdir('phpmailer', 0777, true);
}

$files = ['PHPMailer.php', 'SMTP.php', 'Exception.php'];
$baseUrl = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/';

foreach ($files as $file) {
    $content = @file_get_contents($baseUrl . $file);
    if ($content !== false) {
        file_put_contents('phpmailer/' . $file, $content);
        echo "Tagumpay na nai-download: <b>$file</b><br>";
    } else {
        echo "May error sa pag-download ng: <b>$file</b><br>";
    }
}
echo "<br><b>Tapos na!</b> Naka-setup na ang PHPMailer files sa 'phpmailer/' folder. Pwede mo nang subukan ang login.php.";
?>
