<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed
header('Content-Type: application/json');

// --- ✅ CORS Support ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

$school_id  = intval($data['school_id'] ?? 0);
$student_id = intval($data['student_id'] ?? 0);

if (!$school_id || !$student_id) {
    echo json_encode(["status" => "error", "message" => "Missing school_id or student_id"]);
    exit;
}

// ✅ Fetch student's class and section
$stmt = $conn->prepare("SELECT class_grade, section FROM students WHERE id = ? AND school_id = ?");
$stmt->bind_param('ii', $student_id, $school_id);
$stmt->execute();
$stu = $stmt->get_result()->fetch_assoc();

if (!$stu) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

$class_grade = $stu['class_grade'];
$section     = $stu['section'];

// ✅ Find matching class_timetable_meta
$metaStmt = $conn->prepare("
    SELECT id 
    FROM class_timetable_meta 
    WHERE school_id = ? AND class_name = ? AND section = ?
");
$metaStmt->bind_param('iss', $school_id, $class_grade, $section);
$metaStmt->execute();
$metaRes = $metaStmt->get_result();

$metaIds = [];
while ($m = $metaRes->fetch_assoc()) {
    $metaIds[] = (int)$m['id'];
}

if (empty($metaIds)) {
    echo json_encode(["status" => "error", "message" => "No timetable found for this class"]);
    exit;
}

// ✅ Get distinct teacher IDs
$in = implode(',', array_fill(0, count($metaIds), '?'));
$types = str_repeat('i', count($metaIds));
$sql = "
    SELECT DISTINCT teacher_id 
    FROM class_timetable_details 
    WHERE timing_meta_id IN ($in) 
      AND teacher_id IS NOT NULL 
      AND teacher_id <> 0
";
$detStmt = $conn->prepare($sql);
$detStmt->bind_param($types, ...$metaIds);
$detStmt->execute();
$detRes = $detStmt->get_result();

$teacherIds = [];
while ($d = $detRes->fetch_assoc()) {
    $teacherIds[] = (int)$d['teacher_id'];
}

if (empty($teacherIds)) {
    echo json_encode(["status" => "error", "message" => "No teachers found"]);
    exit;
}

// ✅ Fetch teachers’ names from faculty
$in2 = implode(',', array_fill(0, count($teacherIds), '?'));
$types2 = str_repeat('i', count($teacherIds) + 1); // +1 for school_id
$sql2 = "
    SELECT id, full_name 
    FROM faculty 
    WHERE campus_id = ? AND id IN ($in2)
    ORDER BY full_name ASC
";
$stmt2 = $conn->prepare($sql2);
$params = array_merge([$school_id], $teacherIds);
$stmt2->bind_param($types2, ...$params);
$stmt2->execute();
$res2 = $stmt2->get_result();

$teachers = [];
while ($t = $res2->fetch_assoc()) {
    $teachers[] = [
        "id" => (int)$t['id'],
        "name" => $t['full_name']
    ];
}

// ✅ Return JSON
echo json_encode([
    "status" => "success",
    "count"  => count($teachers),
    "data"   => $teachers
]);
?>