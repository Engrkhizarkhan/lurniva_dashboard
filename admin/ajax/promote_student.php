<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$student_id = $_POST['student_id'] ?? null;
$class_id   = $_POST['class_id'] ?? null;

if (!$student_id || !$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

// Fetch class + section from timetable_meta
$stmt = $conn->prepare("SELECT class_name, section FROM class_timetable_meta WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classData = $result->fetch_assoc();

if (!$classData) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid class selected']);
    exit;
}

$newClass = $classData['class_name'];
$newSection = $classData['section'];

// Update student
$stmt = $conn->prepare("UPDATE students SET class_grade = ?, section = ?, status='Pending Verification' WHERE id = ?");
$stmt->bind_param("ssi", $newClass, $newSection, $student_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Student promoted to ' . $newClass . ' - ' . $newSection]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Promotion failed']);
}