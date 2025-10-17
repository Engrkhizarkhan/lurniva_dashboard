<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Use correct DB path

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Allow both session & JSON body (for Flutter/Postman)
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Session or input missing."]);
    exit;
}

// ✅ Find student's class & section
$sql_student = "
    SELECT class_grade, section
    FROM students
    WHERE id = ? AND school_id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql_student);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

$student_data = $res->fetch_assoc();
$class_grade = $student_data['class_grade'];
$section     = $student_data['section'];

// ✅ Fetch assignments/tests for student
$sql = "
SELECT 
    ta.id,
    ta.teacher_id,
    ta.type,
    ta.title,
    ta.description,
    ta.due_date,
    ta.total_marks,
    ta.attachment AS assignment_file,
    f.full_name AS teacher_name,
    sr.marks_obtained,
    sr.remarks,
    sr.attachment AS result_file
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
$stmt->bind_param("issi", $student_id, $class_grade, $section, $school_id);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = [
        "id"             => (int)$row['id'],
        "type"           => $row['type'],
        "title"          => $row['title'],
        "description"    => $row['description'],
        "due_date"       => $row['due_date'],
        "total_marks"    => (int)$row['total_marks'],
        "teacher_name"   => $row['teacher_name'],
        "assignment_file"=> $row['assignment_file'] ? "../Faculty Dashboard/uploads/assignment/" . $row['assignment_file'] : null,
        "marks_obtained" => $row['marks_obtained'] !== null ? (float)$row['marks_obtained'] : null,
        "remarks"        => $row['remarks'] ?? "",
        "result_file"    => $row['result_file'] ? "../Faculty Dashboard/uploads/results/" . $row['result_file'] : null
    ];
}

echo json_encode([
    "status" => "success",
    "count"  => count($assignments),
    "data"   => $assignments
]);
?>