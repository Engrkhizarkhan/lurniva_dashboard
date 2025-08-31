<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id = $_SESSION['campus_id']; 
$student_id = $_POST['student_id'] ?? 0;

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid student ID']);
    exit;
}

// 1. Student info
$student_sql = "SELECT * FROM students WHERE id = ? AND school_id = ?";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

// 2. Class info
$class_sql = "SELECT * FROM class_timetable_meta WHERE class_name = ? AND section = ? AND school_id = ?";
$stmt = $conn->prepare($class_sql);
$stmt->bind_param("ssi", $student['class_grade'], $student['section'], $school_id);
$stmt->execute();
$class_data = $stmt->get_result()->fetch_assoc();

if ($class_data) {
    $teacher_sql = "SELECT f.full_name 
                    FROM class_timetable_details d 
                    JOIN faculty f ON d.teacher_id = f.id 
                    WHERE d.timing_meta_id = ? 
                    LIMIT 1";
    $stmt = $conn->prepare($teacher_sql);
    $stmt->bind_param("i", $class_data['id']);
    $stmt->execute();
    $teacher_row = $stmt->get_result()->fetch_assoc();
    $class_data['teacher_name'] = $teacher_row['full_name'] ?? '';
} else {
    $class_data = ['class_name' => $student['class_grade'], 'section' => $student['section'], 'teacher_name' => ''];
}

// 3. Subjects with ID
$subjects = [];
if (!empty($class_data['id'])) {
    $subjects_sql = "SELECT d.id, d.period_name, f.full_name as teacher_name, f.rating
                     FROM class_timetable_details d
                     JOIN faculty f ON d.teacher_id = f.id
                     WHERE d.timing_meta_id = ? 
                       AND d.period_type != 'Break'";
    $stmt = $conn->prepare($subjects_sql);
    $stmt->bind_param("i", $class_data['id']);
    $stmt->execute();
    $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'student' => $student,
        'class' => $class_data,
        'subjects' => $subjects
    ]
]);