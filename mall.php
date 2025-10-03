<?php
// Change these values
$to      = "shayans1215225@gmail.com";   // 👉 Replace with your email
$subject = "Test Email from Kurtlar System";
$message = "Hello!\n\nThis is a test email from Kurtlar project (kurtlar2.2.3).\nIf you received this, email sending works fine.\n\n- Kurtlar Team";
$from    = "support@lurniva.com"; // 👉 must be a real domain email
$headers  = "From: $from\r\n";
$headers .= "Reply-To: $from\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Try to send
if (mail($to, $subject, $message, $headers)) {
    echo "✅ Test email sent successfully to $to";
} else {
    echo "❌ Failed to send test email. Check server mail settings.";
}