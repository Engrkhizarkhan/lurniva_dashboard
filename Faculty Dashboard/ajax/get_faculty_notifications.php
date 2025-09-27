<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

// Show PHP errors while debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$facultyId = $_SESSION['admin_id'];
$campusId  = $_SESSION['campus_id']; // check if this should be school_id instead

$q = $conn->prepare("SELECT id, type, title, is_read, module, created_at 
                     FROM notifications 
                     WHERE user_id = ? 
                       AND user_type = 'faculty' 
                       AND school_id = ?
                     ORDER BY created_at DESC 
                     LIMIT 10");
$q->bind_param("ii", $facultyId, $campusId);
$q->execute();
$res = $q->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $link = "#";

    switch (strtolower($row['type'])) {
        case "meeting":    $link = "teacher_meetings.php"; break;
        case "attendance": $link = "faculty_attendance.php"; break;
        case "notice":     $link = "faculty_notice_board.php"; break;
        case "exam":       $link = "exam_results.php"; break;
        case "leaved":     $link = "leaved.php"; break;
        case "library":    $link = "teacher_library.php"; break;
    }

    $data[] = [
        "id"        => $row['id'],
        "title"     => $row['title'],
        "is_read"   => $row['is_read'],
        "created_at"=> $row['created_at'],
        "link"      => $link
    ];
}

echo json_encode(["status"=>"success","data"=>$data]);