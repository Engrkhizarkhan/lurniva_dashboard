<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

if (!isset($_POST['assignee_id']) || empty($_POST['assignee_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Assignee ID is required']);
    exit;
}

$assignee_id = (int) $_POST['assignee_id'];

// First, fetch the task_id linked to this assignee (to send back on success)
$sql = "SELECT task_id FROM school_task_assignees WHERE id = ? AND school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignee_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Assignee not found']);
    exit;
}

$row = $result->fetch_assoc();
$task_id = $row['task_id'];

// Delete the assignee
$delSql = "DELETE FROM school_task_assignees WHERE id = ? AND school_id = ?";
$delStmt = $conn->prepare($delSql);
$delStmt->bind_param("ii", $assignee_id, $admin_id);

if ($delStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Assignee deleted', 'task_id' => $task_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete assignee']);
}
?>