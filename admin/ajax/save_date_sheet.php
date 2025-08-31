<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$exam_name  = $_POST['exam_id'] ?? '';
$class_name = $_POST['class_name'] ?? '';
$rows       = $_POST['rows'] ?? [];

$missing = [];

if (!$exam_name)  $missing[] = "exam_id";
if (!$class_name) $missing[] = "class_name";
if (empty($rows)) $missing[] = "rows";

if (!empty($missing)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing required fields: ' . implode(", ", $missing)
    ]);
    exit;
}


$school_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("
    INSERT INTO exam_schedule 
    (school_id, exam_name, class_name, subject_id, exam_date, exam_time, day, total_marks, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");

foreach ($rows as $r) {
    $exam_date  = $r['exam_date'];
    $exam_time  = $r['exam_time'];
    $subject_id = $r['subject_id'];
    $total_marks= $r['total_marks'];
    $day        = date('l', strtotime($exam_date));

    $stmt->bind_param(
        "ississsi",
        $school_id,
        $exam_name,
        $class_name,
        $subject_id,
        $exam_date,
        $exam_time,
        $day,
        $total_marks
    );
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(['status'=>'success','message'=>'Date sheet saved successfully with marks.']);
?>