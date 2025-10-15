<?php
require_once '../admin/sass/db_config.php';


// --- ✅ Enable CORS ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Accept teacher_id & school_id via JSON or GET ---
$input = json_decode(file_get_contents("php://input"), true);
$teacher_id = intval($input['teacher_id'] ?? $_GET['teacher_id'] ?? 0);
$school_id  = intval($input['school_id'] ?? $_GET['school_id'] ?? 0);

if ($teacher_id <= 0 || $school_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid teacher_id or school_id"
    ]);
    exit;
}

// --- ✅ Fetch student leave requests ---
$sql = "
SELECT 
    sl.id, sl.leave_type, sl.start_date, sl.end_date, sl.reason, sl.status,
    s.full_name, s.class_grade, s.section, s.roll_number
FROM student_leaves sl
JOIN students s ON sl.student_id = s.id
WHERE sl.school_id = ? AND sl.teacher_id = ?
ORDER BY 
    CASE 
        WHEN sl.status = 'Pending' THEN 1 
        ELSE 2 
    END,
    sl.start_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$leaves = [];
while ($row = $result->fetch_assoc()) {
    $leaves[] = [
        "id"           => (int)$row['id'],
        "student_name" => $row['full_name'],
        "class"        => $row['class_grade'] . " - " . $row['section'],
        "roll_number"  => $row['roll_number'],
        "leave_type"   => $row['leave_type'],
        "start_date"   => $row['start_date'],
        "end_date"     => $row['end_date'],
        "reason"       => $row['reason'],
        "status"       => ucfirst($row['status'])
    ];
}

// --- ✅ Output JSON ---
if (empty($leaves)) {
    echo json_encode([
        "status" => "success",
        "message" => "No leave requests found",
        "data" => []
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count" => count($leaves),
        "data" => $leaves
    ]);
}

$stmt->close();
$conn->close();
?>