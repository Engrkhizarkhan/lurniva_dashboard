<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id  = (int)($_SESSION['admin_id'] ?? 0);

$book_id    = (int)($_POST['book_id'] ?? 0);
$student_id = !empty($_POST['student_id']) ? (int)$_POST['student_id'] : "NULL";
$faculty_id = !empty($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : "NULL";
$issue_date = $_POST['issue_date'] ?? '';
$due_date   = $_POST['due_date'] ?? '';

if ($book_id == 0) {
    echo json_encode(['status'=>'error','message'=>'Book required']); 
    exit;
}

// Check availability
$q = mysqli_query($conn, "SELECT title, available FROM books WHERE id=$book_id AND school_id=$school_id");
$row = mysqli_fetch_assoc($q);
if (!$row || $row['available'] <= 0) {
    echo json_encode(['status'=>'error','message'=>'Book not available']); 
    exit;
}
$book_title = $row['title'];

// Fetch recipient info
if ($student_id != "NULL") {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM students WHERE id=$student_id AND school_id=$school_id"));
} elseif ($faculty_id != "NULL") {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM faculty WHERE id=$faculty_id AND campus_id=$school_id"));
} else {
    echo json_encode(['status'=>'error','message'=>'No student or faculty selected']); 
    exit;
}

$recipient_name = $u['full_name'];
$recipient_email = $u['email'];

// Insert transaction
$sql = "INSERT INTO library_transactions (school_id, book_id, student_id, faculty_id, issue_date, due_date, status) 
        VALUES ($school_id, $book_id, $student_id, $faculty_id, '$issue_date', '$due_date', 'Issued')";

if (mysqli_query($conn, $sql)) {
    // reduce available
    mysqli_query($conn, "UPDATE books SET available=available-1 WHERE id=$book_id AND school_id=$school_id");

    $user_type = $student_id != "NULL" ? 'student' : 'faculty';
    $user_id   = $student_id != "NULL" ? $student_id : $faculty_id;
    $type      = 'library';
    $title     = "Book {$book_title} has been issued to you.";

    mysqli_query($conn, "INSERT INTO notifications (school_id, user_type, user_id, type, title, is_read, created_at) 
                         VALUES ($school_id, '$user_type', $user_id, '$type', '$title', 0, NOW())");

    // -------

    function sendMail($to, $subject, $messageHtml) {
        $from = "shayans1215225@gmail.com"; 
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Lurniva School System <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($to, $subject, $messageHtml, $headers);
    }

    $subject = "📖 Book Issued Notification";

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
        <p>Your book <strong>'.$book_title.'</strong> has been issued successfully.</p>
        <p>📅 <strong>Issue Date:</strong> '.$issue_date.'<br>
           📅 <strong>Due Date:</strong> '.$due_date.'</p>
        <p>Please return the book on or before the due date to avoid fines.</p>
        <p>Warm regards,<br><strong>Lurniva Administration</strong></p>
      </div>
      <div class="footer">
        &copy; 2025 Lurniva School System. All rights reserved.
      </div>
    </div>
    </body>
    </html>
    ';

    sendMail($recipient_email, $subject, $msg);

    echo json_encode(['status'=>'success','message'=>'Book issued successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
