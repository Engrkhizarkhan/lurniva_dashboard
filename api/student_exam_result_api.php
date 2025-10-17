<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Adjust path if needed

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Support both session & JSON body
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? 0);
$exam_name  = $data['exam_name'] ?? ($_POST['exam_name'] ?? '');

if (!$student_id || !$school_id || !$exam_name) {
    echo json_encode(["status" => "error", "message" => "Missing student_id, school_id, or exam_name."]);
    exit;
}

/* -------------------
   1️⃣ Get School Info
-------------------- */
$stmt = $conn->prepare("SELECT school_name, address, city, school_phone, logo 
                        FROM schools 
                        WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$school = $stmt->get_result()->fetch_assoc();

/* -------------------
   2️⃣ Get Student Info
-------------------- */
$stmt = $conn->prepare("SELECT id, full_name, roll_number, class_grade, section, gender, dob 
                        FROM students 
                        WHERE id = ? AND school_id = ? LIMIT 1");
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(["status" => "error", "message" => "Student not found."]);
    exit;
}

/* -------------------
   3️⃣ Get Exam Info
-------------------- */   
$stmt = $conn->prepare("SELECT e.id AS exam_id, e.exam_name, e.total_marks, es.exam_date
                        FROM exams e
                        JOIN exam_schedule es ON e.id = es.exam_name
                        WHERE e.school_id = ? 
                          AND es.class_name = ? 
                          AND e.id = ?
                        LIMIT 1");
$stmt->bind_param("isi", $school_id, $student['class_grade'], $exam_name);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo json_encode(["status" => "error", "message" => "Exam not found."]);
    exit;
}

/* -------------------
   4️⃣ Get Subject Results
-------------------- */
$stmt = $conn->prepare("SELECT 
                            ctd.period_name AS subject_name,
                            es.total_marks,
                            er.marks_obtained,
                            er.remarks
                        FROM exam_results er
                        JOIN exam_schedule es ON er.exam_schedule_id = es.id
                        JOIN class_timetable_details ctd ON er.subject_id = ctd.id
                        WHERE er.school_id = ? 
                          AND er.student_id = ? 
                          AND es.exam_name = ?");
$stmt->bind_param("iis", $school_id, $student_id, $exam_name);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$results) {
    echo json_encode(["status" => "error", "message" => "No results found."]);
    exit;
}

/* -------------------
   5️⃣ Calculate Summary
-------------------- */
$totalObtained = 0;
$totalPossible = 0;

foreach ($results as $r) {
    $totalObtained += (float)$r['marks_obtained'];
    $totalPossible += (float)$r['total_marks'];
}

$percentage = $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 2) : 0;

/* -------------------
   ✅ Response
-------------------- */
echo json_encode([
    "status"  => "success",
    "school"  => $school,
    "student" => $student,
    "exam"    => [
        "exam_id" => $exam['exam_id'],
        "exam_name" => $exam['exam_name'],
        "exam_date" => $exam['exam_date'],
        "total_marks" => $exam['total_marks'],
        "percentage" => $percentage
    ],
    "results" => $results
]);
?>