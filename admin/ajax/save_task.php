<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php'; // Make sure this sets up $conn = mysqli_connect(...)

// ----------------------------
// ✅ Check if admin is logged in
// ----------------------------
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$school_id = $admin_id; // Replace with actual school ID if needed

// ----------------------------
// ✅ Get POST data
// ----------------------------
$task_title = trim($_POST['task_title'] ?? '');
$task_description = trim($_POST['task_description'] ?? '');
$due_date = $_POST['due_date'] ?? '';
$assignments_json = $_POST['assignments_json'] ?? '[]';
$assignments = json_decode($assignments_json, true);

// ----------------------------
// ✅ Validate input
// ----------------------------
if (!$task_title || !$task_description || !$due_date || empty($assignments)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// ----------------------------
// ✅ Start transaction
// ----------------------------
mysqli_begin_transaction($conn);

try {
    // ----------------------------
    // Insert main task
    // ----------------------------
    $sql_task = "INSERT INTO school_tasks 
        (school_id, task_title, task_description, due_date, task_completed_percent, created_by, created_at)
        VALUES (?, ?, ?, ?, 0, ?, NOW())";

    $stmt_task = mysqli_prepare($conn, $sql_task);
    mysqli_stmt_bind_param($stmt_task, "isssi", $school_id, $task_title, $task_description, $due_date, $admin_id);

    if (!mysqli_stmt_execute($stmt_task)) {
        throw new Exception("Error inserting task: " . mysqli_error($conn));
    }

    $task_id = mysqli_insert_id($conn);

    // ----------------------------
    // Insert assignees
    // ----------------------------
    $sql_assign = "INSERT INTO school_task_assignees
        (task_id, school_id, assigned_to_type, assigned_to_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmt_assign = mysqli_prepare($conn, $sql_assign);

    foreach ($assignments as $a) {
        $assign_to_type = $a['assign_to_type']; // string: 'student', 'faculty', etc.
        $person_id = (int)$a['person_id'];      // integer ID
        $status = 'Active';                      // ENUM('Active','Not Active')
        $created_at = $a['created_at'] ?? date('Y-m-d H:i:s'); // fallback to now

        // Correct bind types: iisiss (int,int,string,int,string,string)
        mysqli_stmt_bind_param($stmt_assign, "iisiss", $task_id, $school_id, $assign_to_type, $person_id, $status, $created_at);

        if (!mysqli_stmt_execute($stmt_assign)) {
            throw new Exception("Error inserting assignment: " . mysqli_error($conn));
        }
    }

    // ----------------------------
    // Commit transaction
    // ----------------------------
    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'Task saved successfully', 'task_id' => $task_id]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>