<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['student_id'];   // logged-in teacher
$admin_id   = $_SESSION['school_id'];  // school ID

// Get only tasks assigned to this teacher
$sql = "SELECT DISTINCT t.id, t.task_title, t.task_completed_percent, t.created_at, t.due_date
        FROM school_tasks t
        INNER JOIN school_task_assignees a 
            ON t.id = a.task_id 
        WHERE t.school_id = ? 
          AND a.assigned_to_type = 'teacher' 
          AND a.assigned_to_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $admin_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $task_id = $row['id'];

    // Get members for this task
    $member_sql = "SELECT assigned_to_type, assigned_to_id 
                   FROM school_task_assignees 
                   WHERE school_id = ? AND task_id = ?";
    $member_stmt = $conn->prepare($member_sql);
    $member_stmt->bind_param("ii", $admin_id, $task_id);
    $member_stmt->execute();
    $members_result = $member_stmt->get_result();

    $members_html = '<ul class="list-unstyled order-list m-b-0">';
    $count = 0;
    $total_members = 0;

    while ($m = $members_result->fetch_assoc()) {
        $total_members++;
        if ($count < 3) {
            if ($m['assigned_to_type'] === 'teacher') {
                $info_sql = "SELECT full_name, photo FROM faculty WHERE id = ? AND campus_id = ?";
            } else {
                $info_sql = "SELECT full_name, profile_photo AS photo FROM students WHERE id = ? AND school_id = ?";
            }
            $info_stmt = $conn->prepare($info_sql);
            $info_stmt->bind_param("ii", $m['assigned_to_id'], $admin_id);
            $info_stmt->execute();
            $info = $info_stmt->get_result()->fetch_assoc();

            $img  = !empty($info['photo']) ? $info['photo'] : 'assets/img/default.png';
            $name = htmlspecialchars($info['full_name']);

            $members_html .= '<li class="team-member team-member-sm">
                                <img class="rounded-circle" src="../admin/uploads/profile/'.$img.'" alt="'.$name.'" data-toggle="tooltip" title="'.$name.'">
                              </li>';
            $count++;
        }
    }

    if ($total_members > 3) {
        $extra = $total_members - 3;
        $members_html .= '<li class="avatar avatar-sm"><span class="badge badge-primary">+'.$extra.'</span></li>';
    }

    $members_html .= '</ul>';

    $tasks[] = [
        'id' => $task_id,
        'task_title' => $row['task_title'],
        'task_completed_percent' => $row['task_completed_percent'],
        'created_at' => $row['created_at'],
        'due_date' => $row['due_date'],
        'members_html' => $members_html
    ];
}

echo json_encode(['status' => 'success', 'data' => $tasks]);