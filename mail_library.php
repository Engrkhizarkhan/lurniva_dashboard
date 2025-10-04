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
        $mail->Host       = 'smtp.zoho.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@lurniva.com';    // Your Zoho email
        $mail->Password   = 'Lurniva1290';         // Your Zoho app password
        $mail->SMTPSecure = 'ssl';                 // Use SSL for port 465
        $mail->Port       = 465;

        // Sender Info
        $mail->setFrom('info@lurniva.com', 'Lurniva Support');

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