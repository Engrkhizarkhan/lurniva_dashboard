<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$student_id = $_POST['student_id'] ?? null;
$school_id = $_POST['school_id'] ?? null;

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID missing']);
    exit;
}

// If school_id is empty, keep current school
if (!empty($school_id)) {
    $stmt = $conn->prepare("UPDATE students SET school_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $school_id, $student_id);
} else {
    echo json_encode(['status' => 'success', 'message' => 'No migration performed (school not selected).']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Student migrated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Migration failed']);
}