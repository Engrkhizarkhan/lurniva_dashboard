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

// ✅ Read input
$data = json_decode(file_get_contents("php://input"), true);
$teacher_id = intval($data['teacher_id'] ?? 0);

// ✅ Validate input
if ($teacher_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid teacher_id"
    ]);
    exit;
}

// ✅ Fetch distinct classes for teacher
$stmt = $conn->prepare("
    SELECT DISTINCT ctm.id, ctm.class_name, ctm.section
    FROM class_timetable_meta AS ctm
    JOIN class_timetable_details AS ctd ON ctd.timing_meta_id = ctm.id
    WHERE ctd.teacher_id = ?
    ORDER BY ctm.class_name ASC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = [
        "id"         => (int)$row['id'],
        "class_name" => $row['class_name'],
        "section"    => $row['section']
    ];
}

if (empty($classes)) {
    echo json_encode([
        "status" => "error",
        "message" => "No classes found for this teacher"
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count"  => count($classes),
        "data"   => $classes
    ]);
}

$stmt->close();
$conn->close();
?>