<?php
session_start();
require_once '../sass/db_config.php';

$student_id = $_SESSION['student_id'] ?? 0;
$school_id  = $_SESSION['school_id'] ?? 0;

// header('Content-Type: application/json');

if (!$student_id || !$school_id) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

// Find student's class & section
$sql_student = "
    SELECT class_grade, section
    FROM students
    WHERE id = ? AND school_id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql_student);
$stmt->bind_param('ii', $student_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$student_data = $res->fetch_assoc();
$class_grade  = $student_data['class_grade'];
$section      = $student_data['section'];

// Get assignments/tests related to student's class/section
$sql = "
SELECT 
    ta.id,
    ta.teacher_id,
    ta.type,
    ta.title,
    ta.description,
    ta.due_date,
    ta.total_marks,
    ta.attachment,
    f.full_name AS teacher_name,
    sr.marks_obtained,
    sr.remarks,
    sr.attachment AS result_attachment
FROM teacher_assignments AS ta
JOIN class_timetable_meta AS ctm
      ON ctm.id = ta.class_meta_id
     AND ctm.school_id = ta.school_id
JOIN faculty AS f
      ON f.id = ta.teacher_id
LEFT JOIN student_results AS sr
      ON sr.assignment_id = ta.id
     AND sr.student_id = ?
WHERE ctm.class_name = ?
  AND ctm.section = ?
  AND ta.school_id = ?
ORDER BY ta.due_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('issi', $student_id, $class_grade, $section, $school_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $data]);