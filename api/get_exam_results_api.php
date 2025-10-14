<?php
session_start();
require_once '../admin/sass/db_config.php';


// --- CORS & Headers ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read JSON input or fallback to POST
$data = json_decode(file_get_contents("php://input"), true);
$exam_data = $data['exam_id'] ?? ($_POST['exam_id'] ?? '');
$school_id = intval($_SESSION['campus_id'] ?? ($data['school_id'] ?? 0));

if (!$exam_data || !$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing exam_id or school_id"
    ]);
    exit;
}

list($exam_id, $class_name) = explode('|', $exam_data);

// ✅ Fetch exam results
$query = "
    SELECT 
        er.id AS result_id,
        s.full_name, 
        s.roll_number, 
        er.marks_obtained, 
        er.remarks, 
        e.exam_name, 
        es.class_name, 
        es.exam_date, 
        es.total_marks, 
        ctd.period_name
    FROM exam_results er
    JOIN students s 
        ON er.student_id = s.id
    JOIN exam_schedule es 
        ON er.exam_schedule_id = es.id
    JOIN exams e 
        ON es.exam_name = e.id
    JOIN class_timetable_details ctd 
        ON er.subject_id = ctd.id
    JOIN class_timetable_meta ctm 
        ON ctd.timing_meta_id = ctm.id
    WHERE er.school_id = ? 
      AND es.exam_name = ? 
      AND es.class_name = ?
    ORDER BY s.roll_number ASC, ctd.period_number ASC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Query prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $school_id, $exam_id, $class_name);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = [
        "result_id"      => intval($row['result_id']),
        "student_name"   => $row['full_name'],
        "roll_number"    => $row['roll_number'],
        "class_name"     => $row['class_name'],
        "subject"        => $row['period_name'],
        "exam_name"      => $row['exam_name'],
        "exam_date"      => $row['exam_date'],
        "total_marks"    => intval($row['total_marks']),
        "marks_obtained" => intval($row['marks_obtained']),
        "remarks"        => $row['remarks'] ?? ""
    ];
}

$stmt->close();
$conn->close();

if (empty($rows)) {
    echo json_encode([
        "status" => "success",
        "count" => 0,
        "message" => "No results found for this exam",
        "data" => []
    ]);
    exit;
}

echo json_encode(_