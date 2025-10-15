<?php
require_once '../admin/sass/db_config.php';


// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle Preflight ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Parse Input ---
$data = json_decode(file_get_contents("php://input"), true);

$teacher_id = intval($data['teacher_id'] ?? 0);
$school_id  = intval($data['school_id'] ?? 0);
$filter_type  = trim($data['filter_type'] ?? '');
$filter_value = trim($data['filter_value'] ?? '');

if ($teacher_id <= 0 || $school_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid teacher_id or school_id."
    ]);
    exit;
}

// --- ✅ Fetch classes assigned to teacher ---
$sql_classes = "
    SELECT DISTINCT ctm.id AS class_id, ctm.class_name, ctm.section, ctm.total_periods
    FROM class_timetable_meta ctm
    INNER JOIN class_timetable_details ctd ON ctd.timing_meta_id = ctm.id
    WHERE ctd.teacher_id = ? AND ctm.school_id = ?
";
$stmt_classes = $conn->prepare($sql_classes);
$stmt_classes->bind_param("ii", $teacher_id, $school_id);
$stmt_classes->execute();
$res_classes = $stmt_classes->get_result();

$classes = [];

while ($class = $res_classes->fetch_assoc()) {
    $class_id = $class['class_id'];
    $class_name = $class['class_name'];
    $section = $class['section'];

    // ✅ Get periods
    $stmt_periods = $conn->prepare("
        SELECT period_number, period_name, start_time, end_time
        FROM class_timetable_details
        WHERE timing_meta_id = ? AND teacher_id = ?
        ORDER BY period_number ASC
    ");
    $stmt_periods->bind_param("ii", $class_id, $teacher_id);
    $stmt_periods->execute();
    $res_periods = $stmt_periods->get_result();
    $periods = $res_periods->fetch_all(MYSQLI_ASSOC);

    // ✅ Build student query with filter
    $sql_students = "SELECT * FROM students WHERE class_grade = ? AND section = ?";
    $types = "ss";
    $params = [$class_name, $section];

    if (!empty($filter_type) && !empty($filter_value)) {
        $sql_students .= " AND {$filter_type} LIKE ?";
        $types .= "s";
        $params[] = "%{$filter_value}%";
    }

    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param($types, ...$params);
    $stmt_students->execute();
    $res_students = $stmt_students->get_result();

    $students = [];
    while ($student = $res_students->fetch_assoc()) {
        $jsonData = json_encode([
            'id' => $student['id'],
            'full_name' => $student['full_name'],
            'parent_name' => $student['parent_name'],
            'class' => $student['class_grade'],
            'section' => $student['section'],
            'roll_number' => $student['roll_number']
        ], JSON_UNESCAPED_UNICODE);

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . rawurlencode($jsonData) . "&size=150x150";

        $students[] = [
            "id" => (int)$student['id'],
            "full_name" => $student['full_name'],
            "parent_name" => $student['parent_name'],
            "class_grade" => $student['class_grade'],
            "section" => $student['section'],
            "roll_number" => $student['roll_number'],
            "profile_photo" => $student['profile_photo'],
            "qr_code_url" => $qrUrl
        ];
    }

    $classes[] = [
        "class_id" => (int)$class_id,
        "class_name" => $class_name,
        "section" => $section,
        "total_periods" => (int)$class['total_periods'],
        "periods" => $periods,
        "students" => $students
    ];
}

echo json_encode([
    "status" => "success",
    "count" => count($classes),
    "data" => $classes
], JSON_PRETTY_PRINT);
?>