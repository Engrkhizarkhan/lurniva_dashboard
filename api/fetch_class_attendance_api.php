<?php
require_once '../admin/sass/db_config.php';


// --- ✅ Enable CORS ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Input parameters ---
$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

// --- ✅ Validate ---
if (empty($class_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing class_id"
    ]);
    exit;
}

// --- ✅ Fetch class info ---
$class_stmt = $conn->prepare("SELECT class_name, section FROM class_timetable_meta WHERE id = ?");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_info = $class_stmt->get_result()->fetch_assoc();

if (!$class_info) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid class_id"
    ]);
    exit;
}

$class_name = $class_info['class_name'];
$section = $class_info['section'];

// --- ✅ Fetch all students for this class ---
$student_stmt = $conn->prepare("
    SELECT id, full_name, phone, profile_photo 
    FROM students 
    WHERE class_grade = ? AND section = ?
    ORDER BY roll_number ASC
");
$student_stmt->bind_param("ss", $class_name, $section);
$student_stmt->execute();
$students = $student_stmt->get_result();

if ($students->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No students found for this class."
    ]);
    exit;
}

// --- ✅ Prepare data ---
$data = [];
while ($row = $students->fetch_assoc()) {
    // Get saved attendance
    $att_stmt = $conn->prepare("
        SELECT status 
        FROM student_attendance 
        WHERE class_meta_id = ? AND student_id = ? AND date = ?
        LIMIT 1
    ");
    $att_stmt->bind_param("iis", $class_id, $row['id'], $date);
    $att_stmt->execute();
    $att_result = $att_stmt->get_result();
    $attendance_status = $att_result->num_rows > 0 
        ? $att_result->fetch_assoc()['status'] 
        : 'Present'; // default

    $data[] = [
        "student_id" => (int)$row['id'],
        "full_name" => $row['full_name'],
        "phone" => $row['phone'],
        "photo" => $row['profile_photo'] ?: 'default.png',
        "attendance_status" => $attendance_status
    ];
}

// --- ✅ Response ---
echo json_encode([
    "status" => "success",
    "date" => $date,
    "class_id" => (int)$class_id,
    "class_name" => $class_name,
    "section" => $section,
    "count" => count($data),
    "students" => $data
]);
?>