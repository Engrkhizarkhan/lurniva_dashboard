<?php
// get_my_students_api.php
session_start();
require_once '../admin/sass/db_config.php';


// --- CORS CONFIGURATION (for Flutter/Postman) ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read session or JSON body input (for Flutter)
$data = json_decode(file_get_contents("php://input"), true);
$teacher_id = $_SESSION['admin_id'] ?? ($data['teacher_id'] ?? 0);
$school_id  = $_SESSION['campus_id'] ?? ($data['school_id'] ?? 0);

if (!$teacher_id || !$school_id) {
    echo json_encode([
        "status"  => "error",
        "message" => "Missing session or parameters. Please re-login."
    ]);
    exit;
}

$sql = "
SELECT DISTINCT
    s.id,
    s.full_name,
    s.roll_number,
    s.class_grade,
    s.section
FROM class_timetable_details AS ctd
JOIN class_timetable_meta AS ctm
      ON ctm.id = ctd.timing_meta_id
     AND ctm.school_id = ?
JOIN students AS s
      ON s.school_id   = ctm.school_id
     AND s.class_grade = ctm.class_name
     AND s.section     = ctm.section
WHERE ctd.teacher_id = ?
ORDER BY
    s.class_grade ASC,
    s.section ASC,
    s.roll_number ASC,
    s.full_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $school_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = [
        "id"          => (int)$row['id'],
        "full_name"   => $row['full_name'],
        "roll_number" => $row['roll_number'],
        "class_grade" => $row['class_grade'],
        "section"     => $row['section']
    ];
}

if (empty($students)) {
    echo json_encode([
        "status"  => "error",
        "message" => "No students found for your assigned classes."
    ]);
    exit;
}

echo json_encode([
    "status"  => "success",
    "count"   => count($students),
    "data"    => $students
]);

$stmt->close();
$conn->close();
?>