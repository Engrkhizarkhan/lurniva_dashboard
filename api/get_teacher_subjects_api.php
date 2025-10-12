<?php
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);
$teacher_id   = intval($data['teacher_id'] ?? 0);
$class_meta_id = intval($data['class_id'] ?? 0);

// ✅ Validate inputs
if ($teacher_id <= 0 || $class_meta_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid teacher_id or class_id"
    ]);
    exit;
}

// ✅ Fetch distinct subjects for this teacher in the selected class
$stmt = $conn->prepare("
    SELECT DISTINCT ctd.period_name AS subject
    FROM class_timetable_details AS ctd
    WHERE ctd.teacher_id = ? AND ctd.timing_meta_id = ?
    ORDER BY ctd.period_name ASC
");
$stmt->bind_param("ii", $teacher_id, $class_meta_id);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['subject'];
}

if (empty($subjects)) {
    echo json_encode([
        "status" => "error",
        "message" => "No subjects found for this teacher in this class"
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count"  => count($subjects),
        "data"   => $subjects
    ]);
}

$stmt->close();
$conn->close();
?>