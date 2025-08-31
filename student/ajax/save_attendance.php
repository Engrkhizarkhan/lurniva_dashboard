<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

$teacher_id = $_SESSION['admin_id'];
$school_id = $_SESSION['admin_id']; // Change if school_id is stored differently

$class_id = $_POST['classSelect'] ?? '';
$date = $_POST['attendanceDate'] ?? '';
$statuses = $_POST['status'] ?? [];

if (!$class_id || !$date || empty($statuses)) {
    exit("Invalid request: missing class or attendance data.");
}

foreach ($statuses as $student_id => $status) {
    $stmt = $conn->prepare("INSERT INTO student_attendance 
        (school_id, teacher_id, class_meta_id, student_id, status, date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiiss", $school_id, $teacher_id, $class_id, $student_id, $status, $date);
    $stmt->execute();
}

echo "Attendance saved successfully.";