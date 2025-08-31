<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php'; // Make sure this sets up $conn = mysqli_connect(...)

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

$school_id = $admin_id; // You may want to replace this with actual school ID from session or DB
$task_title = trim($_POST['task_title'] ?? '');
$task_description = trim($_POST['task_description'] ?? '');
$due_date = $_POST['due_date'] ?? '';
$assignments_json = $_POST['assignments_json'] ?? '[]';
$assignments = json_decode($assignments_json, true);

if (!$task_title || !$task_description || !$due_date || empty($assignments)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert into school_tasks
    $sql_task = "INSERT INTO school_tasks 
        (school_id, task_title, task_description, due_date, task_completed_percent, created_by, created_at)
        VALUES (?, ?, ?, ?, 0, ?, NOW())";
    $stmt_task = mysqli_prepare($conn, $sql_task);
    mysqli_stmt_bind_param($stmt_task, "isssi", $school_id, $task_title, $task_description, $due_date, $admin_id);

    if (!mysqli_stmt_execute($stmt_task)) {
        throw new Exception("Error inserting task: " . mysqli_error($conn));
    }

    $task_id = mysqli_insert_id($conn);

    // Insert assignments
    $sql_assign = "INSERT INTO school_task_assignees
        (task_id, school_id, assigned_to_type, assigned_to_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_assign = mysqli_prepare($conn, $sql_assign);

    foreach ($assignments as $a) {
        $assign_to_type = $a['assign_to_type'];
        $person_id = (int)$a['person_id'];
        $status = $a['status'];
        $created_at = $a['created_at'];

        mysqli_stmt_bind_param($stmt_assign, "iisiis", $task_id, $school_id, $assign_to_type, $person_id, $status, $created_at);

        if (!mysqli_stmt_execute($stmt_assign)) {
            throw new Exception("Error inserting assignment: " . mysqli_error($conn));
        }
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'Task saved successfully']);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}