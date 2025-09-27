<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);
$id = (int)($_POST['id'] ?? 0);

// Get transaction info (also fetch due_date)
$q = mysqli_query($conn, "SELECT book_id, student_id, faculty_id, due_date 
                          FROM library_transactions 
                          WHERE id=$id AND status='Issued' AND school_id=$school_id");
if (!$q || mysqli_num_rows($q) == 0) {
    echo json_encode(['status'=>'error','message'=>'Transaction not found']); 
    exit;
}

$row = mysqli_fetch_assoc($q);
$book_id    = $row['book_id'];
$student_id = $row['student_id'];
$faculty_id = $row['faculty_id'];
$due_date   = $row['due_date'];
$return_date = date('Y-m-d');

// Check if overdue
if ($return_date > $due_date) {
    echo json_encode([
        'status' => 'late',
        'message' => 'Book is returned after due date. Please enter fine amount.',
        'transaction_id' => $id,
        'days_late' => (strtotime($return_date) - strtotime($due_date)) / 86400
    ]);
    exit;
}

// Process return
mysqli_query($conn, "UPDATE library_transactions 
                     SET return_date='$return_date', status='Returned' 
                     WHERE id=$id AND school_id=$school_id");

// Increase book availability
mysqli_query($conn, "UPDATE books SET available=available+1 WHERE id=$book_id AND school_id=$school_id");

// Insert notification
$user_type = $student_id ? 'student' : 'faculty';
$user_id   = $student_id ? $student_id : $faculty_id;
$type      = 'library';
$book_title_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM books WHERE id=$book_id AND school_id=$school_id"));
$book_title = $book_title_q['title'] ?? 'Book';
$title = "Book {$book_title} has been returned successfully.";

mysqli_query($conn, "INSERT INTO notifications (user_type, user_id, type, title, is_read, created_at, school_id) 
                     VALUES ('$user_type', $user_id, '$type', '$title', 0, NOW(), $school_id)");

// Fetch recipient info
if ($student_id) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM students WHERE id=$student_id AND school_id=$school_id"));
} elseif ($faculty_id) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM faculty WHERE id=$faculty_id AND campus_id=$school_id"));
} else {
    $u = ['full_name'=>'User','email'=>''];
}

$recipient_name = $u['full_name'];
$recipient_email = $u['email'] ?? '';

// Email template
$subject = "📚 Book Returned Notification";
$msg = '
<!DOCTYPE html>
<html>
<head>
<style>
body { margin:0; padding:0; font-family: "Segoe UI", sans-serif; background-color:#f4f4f4; }
.email-wrapper { max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.05);}
.header { background-color:#003366; color:white; text-align:center; padding:20px;}
.header img { max-height:60px; margin-bottom:10px;}
.content { padding:30px; color:#333;}
.footer { background-color:#eeeeee; text-align:center; padding:15px; font-size:12px; color:#777;}
</style>
</head>
<body>
<div class="email-wrapper">
  <div class="header">
    <img src="https://yourdomain.com/assets/img/Final Logo (1).jpg" alt="Lurniva Logo">
    <h2>Lurniva School System</h2>
  </div>
  <div class="content">
    <p>Dear '.$recipient_name.',</p>
    <p>Your book <strong>'.$book_title.'</strong> has been returned successfully.</p>
    <p>Thank you for using our library services.</p>
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
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Lurniva School System <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $messageHtml, $headers);
}

if ($recipient_email) {
    sendMail($recipient_email, $subject, $msg);
}

echo json_encode(['status'=>'success','message'=>'Book returned successfully']);
