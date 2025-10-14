<?php
require_once '../admin/sass/db_config.php';

// ✅ Allow Flutter/Postman CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Accept JSON or form-data
$input = json_decode(file_get_contents("php://input"), true);
$school_id  = intval($input['school_id'] ?? ($_POST['school_id'] ?? 0));

// ✅ Validate
if (!$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing school_id"
    ]);
    exit;
}

// ✅ Get exams from DB
$stmt = $conn->prepare("
    SELECT id, exam_name, total_marks, created_at
    FROM exams
    WHERE school_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = [
        "id" => intval($row['id']),
        "exam_name" => $row['exam_name'],
        "total_marks" => intval($row['total_marks']),
        "created_at" => $row['created_at']
    ];
}

if (empty($exams)) {
    echo json_encode([
        "status" => "success",
        "count" => 0,
        "data" => []
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count" => count($exams),
        "data" => $exams
    ]);
}

$stmt->close();
$conn->close();
?>