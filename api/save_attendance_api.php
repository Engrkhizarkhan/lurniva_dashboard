<?php
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Parse JSON input ---
$data = json_decode(file_get_contents("php://input"), true);

// --- ✅ Extract Data ---
$teacher_id = intval($data['teacher_id'] ?? 0);
$school_id  = intval($data['school_id'] ?? 0);
$class_id   = intval($data['class_id'] ?? 0);
$date       = $data['date'] ?? date('Y-m-d');
$statuses   = $data['statuses'] ?? [];

// --- ✅ Validate ---
if ($teacher_id <= 0 || $school_id <= 0 || $class_id <= 0 || empty($statuses)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid input data"
    ]);
    exit;
}

// --- ✅ Insert / Update Attendance ---
$stmt = $conn->prepare("
    INSERT INTO student_attendance 
    (school_id, teacher_id, class_meta_id, student_id, status, date, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()
");

$inserted = 0;
foreach ($statuses as $student_id => $status) {
    $student_id = intval($student_id);
    $status = ucfirst(strtolower($status)); // normalize e.g. "present"
    $stmt->bind_param("iiiiss", $school_id, $teacher_id, $class_id, $student_id, $status, $date);
    if ($stmt->execute()) {
        $inserted++;
    }
}

// --- ✅ Response ---
echo json_encode([
    "status" => "success",
    "message" => "Attendance saved successfully.",
    "saved_count" => $inserted,
    "class_id" => $class_id,
    "date" => $date
]);
?>