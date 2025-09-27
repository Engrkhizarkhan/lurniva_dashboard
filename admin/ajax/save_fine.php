<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);
$transaction_id = (int)($_POST['transaction_id'] ?? 0);
$fine_amount = floatval($_POST['fine_amount'] ?? 0);

if ($transaction_id <= 0 || $fine_amount <= 0) {
    echo json_encode(['status'=>'error','message'=>'Invalid transaction or fine amount']); 
    exit;
}

// Fetch transaction info
$q = mysqli_query($conn, "
    SELECT t.book_id, t.student_id, t.faculty_id, b.title AS book_title 
    FROM library_transactions t
    LEFT JOIN books b ON t.book_id = b.id
    WHERE t.id=$transaction_id AND t.school_id=$school_id
");

if (!$q || mysqli_num_rows($q) == 0) {
    echo json_encode(['status'=>'error','message'=>'Transaction not found']); 
    exit;
}

$row = mysqli_fetch_assoc($q);
$book_id    = $row['book_id'];
$student_id = $row['student_id'];
$faculty_id = $row['faculty_id'];
$book_title = $row['book_title'] ?? 'Book';

// Update transaction
mysqli_query($conn, "UPDATE library_transactions 
                     SET return_date=CURDATE(), status='Returned' 
                     WHERE id=$transaction_id AND school_id=$school_id");

// Increase book availability
mysqli_query($conn, "UPDATE books SET available=available+1 WHERE id=$book_id AND school_id=$school_id");

// Insert fine record
mysqli_query($conn, "INSERT INTO library_fines (transaction_id,fine_amount,paid_status, school_id) 
                     VALUES ($transaction_id,$fine_amount,'Paid',$school_id)");

// Insert notification
$user_type = $student_id ? 'student' : 'faculty';
$user_id   = $student_id ? $student_id : $faculty_id;
$type      = 'library';
$title     = "Book {$book_title} returned late. Fine: Rs {$fine_amount}";

mysqli_query($conn, "INSERT INTO notifications (user_type,user_id,type,title,is_read,created_at,school_id) 
                     VALUES ('$user_type',$user_id,'$type','$title',0,NOW(),$school_id)");

// Fetch recipient info
if ($student_id) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM students WHERE id=$student_id AND school_id=$school_id"));
} elseif ($faculty_id) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM faculty WHERE id=$faculty_id AND campus_id=$school_id"));
} else {
    $u = ['full_name'=>'User','email'=>''];
}

$recipient_name  = $u['full_name'];
$recipient_email = $u['email'] ?? '';

// Email template
$subject = "📚 Book Returned Late - Fine Applied";

$msg = '
<!DOCTYPE html>
<html>
<head>
<style>
body { margin:0; padding:0; font-family: "Segoe UI", sans-serif; background-color:#f4f4f4; }
.email-wrapper { max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.05);}
.header { background-color:#b30000; color:white; text-align:center; padding:20px;}
.header img { max-height:60px; margin-bottom:10px;}
.content { padding:30px; color:#333;}
.footer { background-color:#eeeeee; text-align:center; padding:15px; font-size:12px; color:#777;}
</style>
</head>
<body>
<div class="email-wrapper">
  <div class="header">
    <h2>Lurniva School System</h2>
  </div>
  <div class="content">
    <p>Dear '.$recipient_name.',</p>
    <p>Your book <strong>'.$book_title.'</strong> was returned <span style="color:red;">after the due date</span>.</p>
    <p>A fine of <strong>Rs '.$fine_amount.'</strong> has been recorded against this transaction.</p>
    <p>Please ensure timely returns in the future to avoid fines.</p>
    <p>Warm regards,<br><strong>Lurniva Administration</strong></p>
  </div>
  <div class="footer">
    &copy; 2025 Lurniva School System. All rights reserved.
  </div>
</div>
</body>
</html>
';

// Send email
function sendMail($to, $subject, $messageHtml) {
    $from = "shayans1215225@gmail.com";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Lurniva School System <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $messageHtml, $headers);
}

if ($recipient_email) {
    sendMail($recipient_email, $subject, $msg);
}

echo json_encode(['status'=>'success','message'=>'Book returned, fine saved, notification & email sent successfully']);
