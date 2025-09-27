<?php
header("Content-Type: application/json");
require "../sass/db_config.php";

$today = date("Y-m-d");
$data = [];

/** Assignments **/
$q = $conn->query("SELECT id, title, due_date FROM teacher_assignments 
                   WHERE due_date >= '$today' ORDER BY due_date ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "📘 Assignment: ".$row['title']." (Due ".$row['due_date'].")",
        "module" => "assignment",
        "date" => $row['due_date']
    ];
}

/** Meetings **/
$q = $conn->query("SELECT id, title, meeting_date FROM meeting_announcements 
                   WHERE meeting_date >= '$today' ORDER BY meeting_date ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "📅 Meeting: ".$row['title']." (".$row['meeting_date'].")",
        "module" => "meeting",
        "date" => $row['meeting_date']
    ];
}

/** Exams **/
$q = $conn->query("SELECT id, exam_name, exam_date FROM exam_schedule 
                   WHERE exam_date >= '$today' ORDER BY exam_date ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "📝 Exam: ".$row['exam_name']." (".$row['exam_date'].")",
        "module" => "exam",
        "date" => $row['exam_date']
    ];
}

/** Diary **/
$q = $conn->query("SELECT id, topic, deadline FROM diary_entries 
                   WHERE deadline >= '$today' ORDER BY deadline ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "📔 Diary: ".$row['topic']." (Due ".$row['deadline'].")",
        "module" => "dairy",
        "date" => $row['deadline']
    ];
}

/** Behavior **/
$q = $conn->query("SELECT id, topic, created_at FROM student_behavior 
                   WHERE created_at >= '$today' ORDER BY created_at ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "⚠️ Behavior: ".$row['topic']." (".$row['created_at'].")",
        "module" => "behavior",
        "date" => $row['created_at']
    ];
}

/** Notices **/
$q = $conn->query("SELECT id, title, notice_date FROM digital_notices 
                   WHERE expiry_date >= '$today' ORDER BY notice_date ASC LIMIT 5");
while($row = $q->fetch_assoc()){
    $data[] = [
        "title" => "📢 Notice: ".$row['title']." (".$row['notice_date'].")",
        "module" => "notice",
        "date" => $row['notice_date']
    ];
}

echo json_encode(["status"=>"success","data"=>$data]);
$conn->close();
