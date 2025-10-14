<?php
session_start();
require_once '../admin/sass/db_config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse input (supports raw JSON and form-data)
$input = json_decode(file_get_contents("php://input"), true);
$exam_id   = intval($input['exam_id'] ?? $_POST['exam_id'] ?? 0);
$school_id = intval($_SESSION['campus_id'] ?? $input['school_id'] ?? 0);

if (!$exam_id || !$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing exam_id or school_id"
    ]);
    exit;
}

// ✅ Get exam info
$stmt = $conn->prepare("SELECT id, exam_name FROM exams WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $exam_id, $school_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo json_encode(["status" => "error", "message" => "Exam not found"]);
    exit;
}
$exam_name = $exam['exam_name'];

// ✅ Get exam schedule
$schedule_stmt = $conn->prepare("
    SELECT id, class_name, subject_id, exam_date, total_marks 
    FROM exam_schedule 
    WHERE exam_name = ? AND school_id = ?
");
$schedule_stmt->bind_param("ii", $exam_id, $school_id);
$schedule_stmt->execute();
$schedules = $schedule_stmt->get_result();

if ($schedules->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No schedule found for this exam"]);
    exit;
}

$response = [
    "status" => "success",
    "exam" => [
        "id" => $exam_id,
        "name" => $exam_name
    ],
    "data" => []
];

while ($sub = $schedules->fetch_assoc()) {
    $class_name = $sub['class_name'];
    $subject_id = $sub['subject_id'];
    $exam_date  = $sub['exam_date'];
    $total_marks = intval($sub['total_marks']);

    // ✅ Get subject name
    $subject_stmt = $conn->prepare("SELECT period_name FROM class_timetable_details WHERE id = ? LIMIT 1");
    $subject_stmt->bind_param("i", $subject_id);
    $subject_stmt->execute();
    $subject_row = $subject_stmt->get_result()->fetch_assoc();
    $subject_name = $subject_row ? $subject_row['period_name'] : "Unknown";

    // ✅ Get students for the class
    $students_stmt = $conn->prepare("SELECT id, full_name, roll_number, class_grade FROM students WHERE class_grade = ? AND school_id = ?");
    $students_stmt->bind_param("si", $class_name, $school_id);
    $students_stmt->execute();
    $students = $students_stmt->get_result();

    $student_list = [];
    while ($student = $students->fetch_assoc()) {
        $student_list[] = [
            "student_id" => intval($student['id']),
            "name"       => $student['full_name'],
            "roll_number"=> $student['roll_number'],
            "class"      => $student['class_grade'],
            "subject"    => $subject_name,
            "exam_date"  => $exam_date,
            "total_marks"=> $total_marks
        ];
    }

    $response["data"][] = [
        "class_name" => $class_name,
        "subject_id" => $subject_id,
        "subject_name" => $subject_name,
        "exam_date" => $exam_date,
        "total_marks" => $total_marks,
        "students" => $student_list
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);