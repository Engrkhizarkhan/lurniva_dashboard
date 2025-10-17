<?php
session_start();
require_once '../admin/sass/db_config.php'; // adjust path if needed


header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];

// ✅ Flexible input handling
$student_id = intval($_POST['student_id'] ?? $data['student_id'] ?? ($_SESSION['student_id'] ?? 0));
$school_id  = intval($_POST['school_id'] ?? $data['school_id'] ?? ($_SESSION['school_id'] ?? 0));

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Missing required data (student_id or school_id)."]);
    exit;
}

// Step 1: Get student's class and section
$stmt_student = $conn->prepare("SELECT class_grade, section FROM students WHERE id = ? AND school_id = ? LIMIT 1");
$stmt_student->bind_param('ii', $student_id, $school_id);
$stmt_student->execute();
$res_student = $stmt_student->get_result();

if ($res_student->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

$student_data = $res_student->fetch_assoc();
$class_grade  = $student_data['class_grade'];
$section      = $student_data['section'];

// Step 2: Get all teachers for that class/section
$stmt = $conn->prepare("
    SELECT DISTINCT f.id, f.full_name
    FROM class_timetable_meta AS ctm
    JOIN class_timetable_details AS ctd ON ctd.timing_meta_id = ctm.id AND ctm.school_id = ?
    JOIN faculty AS f ON f.id = ctd.teacher_id
    WHERE ctm.class_name = ? AND ctm.section = ? AND f.status = 'Approved'
    ORDER BY f.full_name ASC
");
$stmt->bind_param('iss', $school_id, $class_grade, $section);
$stmt->execute();
$res = $stmt->get_result();

$teachers = [];
while ($row = $res->fetch_assoc()) {
    $teachers[] = [
        "id"   => (int)$row['id'],
        "name" => htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8')
    ];
}

if (empty($teachers)) {
    echo json_encode(["status" => "error", "message" => "No teachers found"]);
    exit;
}

echo json_encode([
    "status"   => "success",
    "count"    => count($teachers),
    "teachers" => $teachers
]);