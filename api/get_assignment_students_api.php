<?php
require_once '../admin/sass/db_config.php';


// --- ✅ Enable CORS (for Flutter/Postman) ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Decode JSON body
$data = json_decode(file_get_contents("php://input"), true);
$assignment_id = intval($data['assignment_id'] ?? 0);
$school_id     = intval($data['school_id'] ?? 0);

if (!$assignment_id || !$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing assignment_id or school_id"
    ]);
    exit;
}

// ✅ Fetch assignment info
$stmt = $conn->prepare("SELECT * FROM teacher_assignments WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $assignment_id, $school_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    echo json_encode([
        "status" => "error",
        "message" => "Assignment not found"
    ]);
    exit;
}

// ✅ Get the class info
$class_meta_id = $assignment['class_meta_id'];
$class_stmt = $conn->prepare("SELECT * FROM class_timetable_meta WHERE id = ? AND school_id = ?");
$class_stmt->bind_param("ii", $class_meta_id, $school_id);
$class_stmt->execute();
$class_info = $class_stmt->get_result()->fetch_assoc();

if (!$class_info) {
    echo json_encode([
        "status" => "error",
        "message" => "Class not found"
    ]);
    exit;
}

// ✅ Fetch students for this class who don’t yet have results
$students_stmt = $conn->prepare("
    SELECT s.id, s.full_name, s.roll_number, s.class_grade, s.section
    FROM students s
    WHERE s.school_id = ? 
      AND s.class_grade = ? 
      AND s.section = ?
      AND s.id NOT IN (
          SELECT student_id 
          FROM student_results 
          WHERE assignment_id = ?
      )
    ORDER BY s.roll_number ASC
");
$students_stmt->bind_param("issi", $school_id, $class_info['class_name'], $class_info['section'], $assignment_id);
$students_stmt->execute();
$students_res = $students_stmt->get_result();

$students = [];
while ($row = $students_res->fetch_assoc()) {
    $students[] = [
        "student_id"   => (int)$row['id'],
        "full_name"    => $row['full_name'],
        "roll_number"  => $row['roll_number'],
        "class_grade"  => $row['class_grade'],
        "section"      => $row['section'],
        "subject"      => $assignment['subject'],
        "type"         => $assignment['type'],
        "title"        => $assignment['title'],
        "due_date"     => $assignment['due_date'],
        "total_marks"  => (float)$assignment['total_marks']
    ];
}

if (empty($students)) {
    echo json_encode([
        "status" => "error",
        "message" => "No students found without results for this assignment"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "assignment" => [
        "id"          => $assignment['id'],
        "title"       => $assignment['title'],
        "subject"     => $assignment['subject'],
        "type"        => $assignment['type'],
        "due_date"    => $assignment['due_date'],
        "total_marks" => (float)$assignment['total_marks']
    ],
    "class_info" => [
        "class_name"  => $class_info['class_name'],
        "section"     => $class_info['section']
    ],
    "students" => $students
]);
?>