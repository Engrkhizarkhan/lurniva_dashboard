<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require Composer's autoloader
require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';   // Gmail SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your-email@gmail.com';  // 👉 replace with your Gmail
    $mail->Password   = 'your-app-password';     // 👉 replace with your Gmail App Password
    $mail->SMTPSecure = 'tls';                   // Encryption (ssl or tls)
    $mail->Port       = 587;

    // Sender & Recipient
    $mail->setFrom('your-email@gmail.com', 'Your Name');
    $mail->addAddress('recipient@example.com');  // 👉 replace with recipient

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Hello from PHP';
    $mail->Body    = 'This is a <b>test email</b> using PHPMailer and Gmail SMTP!';
    $mail->AltBody = 'This is a test email using PHPMailer and Gmail SMTP!';

    // Send email
    $mail->send();
    echo '✅ Email has been sent successfully';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}