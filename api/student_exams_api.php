<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Adjust path if needed

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Support both session and JSON body (for Flutter/Postman)
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Session or input missing."]);
    exit;
}

// ✅ Get student's class & section
$sql = "SELECT class_grade, section 
        FROM students 
        WHERE id = ? AND school_id = ? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

$class_grade = $student['class_grade'];

// ✅ Get distinct exams for this class
$q = "SELECT DISTINCT e.id AS exam_id, e.exam_name, e.created_at
      FROM exam_schedule es
      INNER JOIN exams e ON es.exam_name = e.id
      WHERE es.class_name = ? AND es.school_id = ?
      ORDER BY e.created_at DESC";
$stmt = $conn->prepare($q);
$stmt->bind_param("si", $class_grade, $school_id);
$stmt->execute();
$res = $stmt->get_result();

$exams = [];
while ($row = $res->fetch_assoc()) {
    $exams[] = [
        "exam_id"   => (int)$row['exam_id'],
        "exam_name" => $row['exam_name'],
        "created_at"=> $row['created_at']
    ];
}

if (empty($exams)) {
    echo json_encode(["status" => "error", "message" => "No exams found"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "count"  => count($exams),
    "data"   => $exams
]);
?>