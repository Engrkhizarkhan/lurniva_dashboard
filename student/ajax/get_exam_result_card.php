<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: application/json');

$student_id = $_SESSION['student_id'] ?? 0;
$school_id  = $_SESSION['school_id'] ?? 0;
$exam_name  = $_POST['exam_name'] ?? '';

if (!$student_id || !$school_id || !$exam_name) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

/* -------------------
   Get School Info
-------------------- */
$sql = "SELECT school_name, address, city, school_phone, logo 
        FROM schools 
        WHERE id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$school = $stmt->get_result()->fetch_assoc();

/* -------------------
   Get Student Info
-------------------- */
$sql = "SELECT id, full_name, roll_number, class_grade, section, gender, dob 
        FROM students 
        WHERE id=? AND school_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* -------------------
   Get Exam Info + Total Marks from exams table
-------------------- */   
$sql = "SELECT e.exam_name, e.total_marks, es.exam_date
        FROM exams e
        JOIN exam_schedule es ON e.id = es.exam_name
        WHERE e.school_id=? 
          AND es.class_name=? 
          AND e.id=? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $school_id, $student['class_grade'], $exam_name);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

/* -------------------
   Get Results (subjects & marks)
-------------------- */
$sql = "SELECT 
            ctd.period_name,
            es.total_marks,
            er.marks_obtained,
            er.remarks
        FROM exam_results er
        JOIN exam_schedule es ON er.exam_schedule_id = es.id
        JOIN class_timetable_details ctd ON er.subject_id = ctd.id
        WHERE er.school_id=? 
          AND er.student_id=? 
          AND es.exam_name=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $school_id, $student_id, $exam_name);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* -------------------
   Response
-------------------- */
if (!$exam) {
    echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
    exit;
}

if (!$results) {
    echo json_encode(['status' => 'error', 'message' => 'No results found']);
    exit;
}

echo json_encode([
    'status'  => 'success',
    'school'  => $school,
    'student' => $student,
    'exam'    => $exam, // <-- includes total_marks
    'results' => $results
]);