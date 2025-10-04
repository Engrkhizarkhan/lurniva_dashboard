<?php
// test_mail.php

require_once 'mail_library.php'; // Adjust path if needed

$to = 'shayans1215225@gmail.com';
$subject = 'Test Email from Lurniva System';
$body = '
    <h2>Test Email from Lurniva</h2>
    <p>This is a <strong>test message</strong> sent using <b>PHPMailer (Zoho SMTP)</b>.</p>
    <p>If you received this email, your configuration is working correctly ✅</p>
    <br>
    <p>— Lurniva Support</p>
';

// Send the email
if (sendMail($to, $subject, $body, 'Shayan')) {
    echo "✅ Test email successfully sent to <b>$to</b>";
} else {
    echo "❌ Failed to send email. Check your SMTP credentials or error logs.";
}
?>