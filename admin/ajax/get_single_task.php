<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

if ($_GET['action'] === 'get_task' && isset($_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);

    // Get task details
    $taskQuery = $conn->prepare("
        SELECT st.id, st.task_title, st.task_description, st.due_date, st.task_completed_percent,
               sta.assigned_to_type AS assign_to_type, 
               COALESCE(f.full_name, s.full_name) AS person_name
        FROM school_tasks st
        LEFT JOIN school_task_assignees sta ON st.id = sta.task_id
        LEFT JOIN faculty f ON sta.assigned_to_type = 'teacher' AND sta.assigned_to_id = f.id
        LEFT JOIN students s ON sta.assigned_to_type = 'student' AND sta.assigned_to_id = s.id
        WHERE st.school_id = ? AND st.id = ?
        LIMIT 1
    ");
    $taskQuery->bind_param("ii", $admin_id, $task_id);
    $taskQuery->execute();
    $taskResult = $taskQuery->get_result();

    if ($taskResult->num_rows > 0) {
        $task = $taskResult->fetch_assoc();

        // Get assignees list
        $assigneesQuery = $conn->prepare("
            SELECT sta.id, sta.assigned_to_type, 
                   COALESCE(f.full_name, s.full_name) AS person_name,
                   sta.status, sta.created_at
            FROM school_task_assignees sta
            LEFT JOIN faculty f ON sta.assigned_to_type = 'teacher' AND sta.assigned_to_id = f.id
            LEFT JOIN students s ON sta.assigned_to_type = 'student' AND sta.assigned_to_id = s.id
            WHERE sta.school_id = ? AND sta.task_id = ?
        ");
        $assigneesQuery->bind_param("ii", $admin_id, $task_id);
        $assigneesQuery->execute();
        $assigneesResult = $assigneesQuery->get_result();
        $assignees = [];
        while ($row = $assigneesResult->fetch_assoc()) {
            $assignees[] = $row;
        }

        echo json_encode(['status' => 'success', 'task' => $task, 'assignees' => $assignees]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}