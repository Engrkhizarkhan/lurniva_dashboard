<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Validate request
if (!isset($_POST['task_id']) || !is_numeric($_POST['task_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

$task_id = intval($_POST['task_id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete from school_task_assignees first (if you want cascade delete)
    $stmt = $conn->prepare("DELETE FROM school_task_assignees WHERE school_id = ? AND task_id = ?");
    $stmt->bind_param("ii", $admin_id, $task_id);
    $stmt->execute();
    $stmt->close();

    // Delete from school_tasks
    $stmt = $conn->prepare("DELETE FROM school_tasks WHERE school_id = ? AND id = ?");
    $stmt->bind_param("ii", $admin_id, $task_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>