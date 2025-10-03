<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output (optional, for testing)
    // 0 = off (production), 2 = debug
    $mail->SMTPDebug = 2;                     
    $mail->isSMTP();
    $mail->Host       = 'smtp.zoho.com';     
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@lurniva.com';   // Your Zoho email
    $mail->Password   = 'Lurniva1290';        // Your Zoho password or app password
    $mail->SMTPSecure = 'ssl';                // ssl for 465, tls for 587
    $mail->Port       = 465;                  // 465 = SSL, 587 = TLS

    // Sender
    $mail->setFrom('info@lurniva.com', 'Lurniva Support');

    // Recipient
    $mail->addAddress('shayans1215225@gmail.com', 'Shayan Khan');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Lurniva';
    $mail->Body    = 'Hello Shayan,<br><br>This is a <b>test email</b> sent from <b>Lurniva</b> using Zoho SMTP.';
    $mail->AltBody = 'Hello Shayan, This is a test email sent from Lurniva using Zoho SMTP.';

    $mail->send();
    echo '✅ Email has been sent successfully to shayans1215225@gmail.com';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}