<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$adminId = $_SESSION['admin_id'];
$schoolId = $_SESSION['school_id'];

$q = $conn->prepare("SELECT id, title, is_read, module, created_at 
                     FROM notifications 
                     WHERE user_id = ? AND user_type='admin' AND school_id = ?
                     ORDER BY created_at DESC 
                     LIMIT 10");
$q->bind_param("ii", $adminId, $schoolId);
$q->execute();
$res = $q->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $link = "#"; // default

    // map module -> page
    switch (strtolower($row['module'])) {
        case "notice":     $link = "admin_notice_board.php"; break;
        case "meeting":    $link = "admin_meetings.php"; break;
        case "task":       $link = "admin_tasks.php"; break;
        case "student":    $link = "manage_students.php"; break;
        case "teacher":    $link = "manage_teachers.php"; break;
    }

    $data[] = [
        "id" => $row['id'],
        "title" => $row['title'],
        "is_read" => $row['is_read'],
        "created_at" => $row['created_at'],
        "link" => $link
    ];
}

echo json_encode(["status"=>"success","data"=>$data]);