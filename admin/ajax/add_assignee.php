<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

$task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
$assigned_to_type = isset($_POST['assigned_to_type']) ? $_POST['assigned_to_type'] : '';
$assigned_to_id = isset($_POST['assigned_to_id']) ? (int) $_POST['assigned_to_id'] : 0;

if ($task_id <= 0 || !$assigned_to_type || $assigned_to_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Optional: Validate that task belongs to this admin (school_id)
$stmt = $conn->prepare("SELECT id FROM school_tasks WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $task_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    exit;
}

// Optional: check if assigned_to_id exists in teacher/student table for this school/admin
if ($assigned_to_type === 'teacher') {
    $checkStmt = $conn->prepare("SELECT id FROM faculty WHERE id = ? AND campus_id = ?");
    $checkStmt->bind_param("ii", $assigned_to_id, $admin_id);
} else if ($assigned_to_type === 'student') {
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE id = ? AND school_id = ?");
    $checkStmt->bind_param("ii", $assigned_to_id, $admin_id);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid assigned_to_type']);
    exit;
}

$checkStmt->execute();
$checkRes = $checkStmt->get_result();
if ($checkRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Person not found']);
    exit;
}

// Check if this assignee already exists for this task to prevent duplicates
$dupStmt = $conn->prepare("SELECT id FROM school_task_assignees WHERE task_id = ? AND assigned_to_type = ? AND assigned_to_id = ? AND school_id = ?");
$dupStmt->bind_param("isii", $task_id, $assigned_to_type, $assigned_to_id, $admin_id);
$dupStmt->execute();
$dupRes = $dupStmt->get_result();
if ($dupRes->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This person is already assigned']);
    exit;
}

// Insert assignee
$insertStmt = $conn->prepare("INSERT INTO school_task_assignees (task_id, assigned_to_type, assigned_to_id, school_id, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
$insertStmt->bind_param("isii", $task_id, $assigned_to_type, $assigned_to_id, $admin_id);

if ($insertStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Assignee added']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add assignee']);
}