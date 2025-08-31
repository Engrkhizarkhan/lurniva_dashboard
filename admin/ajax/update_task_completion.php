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
$task_completed_percent = isset($_POST['task_completed_percent']) ? (int) $_POST['task_completed_percent'] : -1;

if ($task_id <= 0 || $task_completed_percent < 0 || $task_completed_percent > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Verify task belongs to this admin
$stmt = $conn->prepare("SELECT id FROM school_tasks WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $task_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    exit;
}

// Update completion percent
$updateStmt = $conn->prepare("UPDATE school_tasks SET task_completed_percent = ? WHERE id = ? AND school_id = ?");
$updateStmt->bind_param("iii", $task_completed_percent, $task_id, $admin_id);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Completion updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update completion']);
}