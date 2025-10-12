<?php
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS (for Flutter/Postman) ---
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
$teacher_id = intval($data['teacher_id'] ?? 0);
$school_id  = intval($data['school_id'] ?? 0);

// ✅ Validate input
if ($teacher_id <= 0 || $school_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid teacher_id or school_id"
    ]);
    exit;
}

// ✅ Fetch assignments where not all students have results
$query = "
SELECT ta.id, ta.title, ta.type, ta.due_date, ctm.class_name, ctm.section
FROM teacher_assignments ta
JOIN class_timetable_meta ctm ON ta.class_meta_id = ctm.id
WHERE ta.school_id = ? AND ta.teacher_id = ?
AND NOT EXISTS (
    SELECT 1 FROM student_results sr
    WHERE sr.assignment_id = ta.id
)
ORDER BY ta.due_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = [
        "id"          => (int)$row['id'],
        "title"       => $row['title'],
        "type"        => $row['type'],
        "class_name"  => $row['class_name'],
        "section"     => $row['section'],
        "due_date"    => $row['due_date']
    ];
}

if (empty($assignments)) {
    echo json_encode([
        "status" => "error",
        "message" => "No assignments found"
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count"  => count($assignments),
        "data"   => $assignments
    ]);
}

$stmt->close();
$conn->close();
?>