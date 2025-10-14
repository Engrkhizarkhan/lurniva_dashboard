<?php
session_start();
require_once '../admin/sass/db_config.php';


// --- CORS + Headers ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Get school_id from session or input
$input = json_decode(file_get_contents("php://input"), true);
$school_id = intval($_SESSION['campus_id'] ?? ($input['school_id'] ?? 0));

if (!$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing school_id"
    ]);
    exit;
}

// ✅ Fetch distinct submitted exams
$query = "
    SELECT DISTINCT e.id AS exam_id, e.exam_name, es.class_name
    FROM exam_results er
    JOIN exam_schedule es ON er.exam_schedule_id = es.id
    JOIN exams e ON es.exam_name = e.id
    WHERE er.school_id = ?
    ORDER BY es.exam_date DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = [
        "exam_id"    => intval($row['exam_id']),
        "exam_name"  => $row['exam_name'],
        "class_name" => $row['class_name'],
        "value"      => $row['exam_id'] . "|" . $row['class_name'],
        "label"      => $row['exam_name'] . " - " . $row['class_name']
    ];
}

$stmt->close();
$conn->close();

if (empty($exams)) {
    echo json_encode([
        "status" => "success",
        "count" => 0,
        "message" => "No submitted exams found",
        "data" => []
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "count" => count($exams),
    "data" => $exams
], JSON_PRETTY_PRINT);