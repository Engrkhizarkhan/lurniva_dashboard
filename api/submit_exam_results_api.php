<?php
require_once '../admin/sass/db_config.php';


header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read JSON input (or fallback to form-data)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

$school_id = intval($_SESSION['campus_id'] ?? $data['school_id'] ?? 0);
$results   = $data['results'] ?? [];

if (!$school_id || empty($results)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing school_id or no results provided"
    ]);
    exit;
}

// ✅ Prepare reusable statement
$stmt = $conn->prepare("
    INSERT INTO exam_results 
        (school_id, exam_schedule_id, student_id, subject_id, total_marks, marks_obtained, remarks, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE 
        total_marks = VALUES(total_marks),
        marks_obtained = VALUES(marks_obtained),
        remarks = VALUES(remarks),
        updated_at = CURRENT_TIMESTAMP
");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    exit;
}

$inserted = 0;
$errors = [];

foreach ($results as $r) {
    $student_id     = intval($r['student_id'] ?? 0);
    $subject_id     = intval($r['subject_id'] ?? 0);
    $marks_obtained = intval($r['marks_obtained'] ?? 0);
    $total_marks    = intval($r['total_marks'] ?? 0);
    $remarks_text   = $r['remarks'] ?? '';

    if (!$student_id || !$subject_id) continue;

    // ✅ Get exam_schedule_id for this subject
    $exam_schedule_id = 0;
    $find = $conn->prepare("SELECT id FROM exam_schedule WHERE school_id = ? AND subject_id = ? LIMIT 1");
    $find->bind_param("ii", $school_id, $subject_id);
    $find->execute();
    $find->bind_result($exam_schedule_id);
    $find->fetch();
    $find->close();

    if (!$exam_schedule_id) {
        $errors[] = "No exam schedule found for subject_id $subject_id";
        continue;
    }

    $stmt->bind_param(
        "iiiiiss",
        $school_id,
        $exam_schedule_id,
        $student_id,
        $subject_id,
        $total_marks,
        $marks_obtained,
        $remarks_text
    );

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Error saving result for student_id $student_id, subject_id $subject_id: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    "status" => "success",
    "message" => "$inserted result(s) saved successfully.",
    "errors"  => $errors
], JSON_PRETTY_PRINT);