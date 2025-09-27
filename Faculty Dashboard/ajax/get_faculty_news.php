<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

// show errors while debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['campus_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$facultyId = $_SESSION['admin_id'];   // faculty id
$campusId  = $_SESSION['campus_id']; // school_id / campus id
$today     = date("Y-m-d");

$news = [];

/* 1️⃣ Meeting Announcements */
$q1 = $conn->prepare("
    SELECT id, title, meeting_date 
    FROM meeting_announcements
    WHERE school_id = ?
      AND (
            (meeting_person = 'teacher' AND person_id_one = ?)
         OR (meeting_person2 = 'teacher' AND person_id_two = ?)
      )
      AND meeting_date >= ?
    ORDER BY meeting_date ASC
    LIMIT 5
");
$q1->bind_param("iiis", $campusId, $facultyId, $facultyId, $today);
$q1->execute();
$res1 = $q1->get_result();
while ($row = $res1->fetch_assoc()) {
    $news[] = [
        "title" => "📅 Meeting: " . $row['title'] . " on " . $row['meeting_date'],
        "link"  => "teacher_meetings.php",
        "date"  => $row['meeting_date']
    ];
}

/* 2️⃣ Notices (faculty or everyone) */
$q2 = $conn->prepare("
    SELECT id, title, expiry_date 
    FROM digital_notices
    WHERE school_id = ?
      AND expiry_date >= ?
      AND (audience = 'Faculty' OR audience = 'Everyone')
    ORDER BY expiry_date ASC
    LIMIT 5
");
$q2->bind_param("is", $campusId, $today);
$q2->execute();
$res2 = $q2->get_result();
while ($row = $res2->fetch_assoc()) {
    $news[] = [
        "title" => "📢 Notice: " . $row['title'] . " (till " . $row['expiry_date'] . ")",
        "link"  => "faculty_notice_board.php",
        "date"  => $row['expiry_date']
    ];
}


/* 3️⃣ Missing Student Attendance Today */
$q3 = $conn->prepare("
    SELECT id 
    FROM student_attendance
    WHERE school_id = ?
      AND teacher_id = ?
      AND date = ?
      AND status IS NULL
    LIMIT 1
");
$q3->bind_param("iis", $campusId, $facultyId, $today);
$q3->execute();
$res3 = $q3->get_result();
if ($res3->num_rows == 0) {
    $news[] = [
        "title" => "⚠ Attendance is missing for today ($today)",
        "link"  => "Attendance.php",
        "date"  => $today
    ];
}

/* 4️⃣ Student Leaves (under this teacher’s school) */
$q4 = $conn->prepare("
    SELECT id, student_id, start_date, end_date
    FROM student_leaves
    WHERE school_id = ?
      AND start_date <= ? AND end_date >= ?
    ORDER BY created_at DESC
    LIMIT 5
");
$q4->bind_param("iss", $campusId, $today, $today);
$q4->execute();
$res4 = $q4->get_result();
while ($row = $res4->fetch_assoc()) {
    $news[] = [
        "title" => "📝 Student Leave from " . $row['start_date'] . " to " . $row['end_date'],
        "link"  => "#", // maybe later detail page
        "date"  => $row['start_date']
    ];
}

echo json_encode(["status" => "success", "data" => $news]);