<?php
// mail_library.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Adjust path if needed

function sendMail($to, $subject, $body, $toName = '') {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.zeptomail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emailapikey';    // Your Zoho email
        $mail->Password   = 'wSsVR60k80H2DqsunDf+celrzQhTD1n3Exh+3VT0uHOvHfrLpsdtwxLOAAH2GPFJQjZtHTEVor8hykgCgWYOjIwoy1lSWSiF9mqRe1U4J3x17qnvhDzKXWpUkBGILI8KwgpvkmlnEs4r+g==';         // Your Zoho app password
        $mail->SMTPSecure = 'TLS';                 // Use SSL for port 465
        $mail->Port       = 587;

        // Sender Info
        $mail->setFrom('verify@lurniva.com', 'Lurniva Support');

        // Recipient
        $mail->addAddress($to, $toName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        // Send
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed to send: {$mail->ErrorInfo}");
        return false;
    }
}