<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$task_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($task_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

$stmt = $conn->prepare("SELECT id, task_title, task_description, due_date, task_completed_percent, created_at, created_by 
                        FROM school_tasks WHERE id = ? AND school_id = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $task_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    exit;
}

$task = $result->fetch_assoc();

$assignees = [];
$assigneeStmt = $conn->prepare("SELECT sta.id, sta.assigned_to_type, sta.assigned_to_id, sta.status, sta.created_at,
                                      COALESCE(f.full_name, s.full_name) as person_name
                               FROM school_task_assignees sta
                               LEFT JOIN faculty f ON (sta.assigned_to_type = 'teacher' AND sta.assigned_to_id = f.id)
                               LEFT JOIN students s ON (sta.assigned_to_type = 'student' AND sta.assigned_to_id = s.id)
                               WHERE sta.task_id = ? AND sta.school_id = ?");
if (!$assigneeStmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$assigneeStmt->bind_param("ii", $task_id, $admin_id);
$assigneeStmt->execute();
$assigneeResult = $assigneeStmt->get_result();

while ($row = $assigneeResult->fetch_assoc()) {
    $assignees[] = $row;
}

echo json_encode([
    'status' => 'success',
    'task' => $task,
    'assignees' => $assignees
]);