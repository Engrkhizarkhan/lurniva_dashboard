<?php
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS (for Flutter, React, Postman, etc.)
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read input (JSON)
$data = json_decode(file_get_contents("php://input"), true);
$class_id  = intval($data['class_id'] ?? 0);
$school_id = intval($data['school_id'] ?? 0); // Must be passed from app (not session)

// ✅ Validate
if ($class_id <= 0 || $school_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid class_id or school_id"
    ]);
    exit;
}

// ✅ Get class grade + section
$metaStmt = $conn->prepare("
    SELECT class_name, section 
    FROM class_timetable_meta 
    WHERE id = ? AND school_id = ?
");
$metaStmt->bind_param("ii", $class_id, $school_id);
$metaStmt->execute();
$metaResult = $metaStmt->get_result();
$classMeta = $metaResult->fetch_assoc();

if (!$classMeta) {
    echo json_encode([
        "status" => "error",
        "message" => "Class not found"
    ]);
    exit;
}

$class_grade = $classMeta['class_name'];
$section     = $classMeta['section'];

// ✅ Fetch students
$stmt = $conn->prepare("
    SELECT id, full_name, roll_number 
    FROM students 
    WHERE class_grade = ? AND section = ? AND school_id = ? 
    ORDER BY roll_number ASC
");
$stmt->bind_param("ssi", $class_grade, $section, $school_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        "id"          => (int)$row['id'],
        "full_name"   => $row['full_name'],
        "roll_number" => $row['roll_number']
    ];
}

if (empty($students)) {
    echo json_encode([
        "status" => "error",
        "message" => "No students found for this class"
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count"  => count($students),
        "data"   => $students
    ]);
}

// ✅ Cleanup
$stmt->close();
$metaStmt->close();
$conn->close();
?>