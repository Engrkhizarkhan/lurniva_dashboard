<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "<tr><td colspan='7'>Unauthorized</td></tr>";
    exit;
}

$admin_id = $_SESSION['admin_id'];

$tasks = $conn->query("SELECT id, task_title, task_completed_percent, created_at, due_date 
                       FROM school_tasks 
                       WHERE school_id = $admin_id");

if ($tasks->num_rows > 0) {
    while ($task = $tasks->fetch_assoc()) {

        // Fetch members for the task
        $members_html = '';
        $members = [];
        $assignees = $conn->query("SELECT assigned_to_type, assigned_to_id 
                                   FROM school_task_assignees 
                                   WHERE school_id = $admin_id 
                                   AND task_id = {$task['id']}");

        while ($a = $assignees->fetch_assoc()) {
            if ($a['assigned_to_type'] == 'teacher') {
                $user = $conn->query("SELECT full_name, photo 
                                      FROM faculty 
                                      WHERE id = {$a['assigned_to_id']} 
                                      AND campus_id = $admin_id")->fetch_assoc();
                if ($user) {
                    $members[] = ['name' => $user['full_name'], 'photo' => $user['photo']];
                }
            } else {
                $user = $conn->query("SELECT full_name, profile_photo 
                                      FROM students 
                                      WHERE id = {$a['assigned_to_id']} 
                                      AND school_id = $admin_id")->fetch_assoc();
                if ($user) {
                    $members[] = ['name' => $user['full_name'], 'photo' => $user['profile_photo']];
                }
            }
        }

        // Limit to first 3 members
        $display_count = min(3, count($members));
        for ($i = 0; $i < $display_count; $i++) {
            $members_html .= '<li class="team-member team-member-sm">
                                <img class="rounded-circle" src="'.$members[$i]['photo'].'" 
                                     alt="user" title="'.$members[$i]['name'].'">
                              </li>';
        }
        if (count($members) > 3) {
            $members_html .= '<li class="avatar avatar-sm">
                                <span class="badge badge-primary">+'.(count($members)-3).'</span>
                              </li>';
        }

        echo "<tr>
                <td>{$task['task_title']}</td>
                <td class='text-truncate'>
                    <ul class='list-unstyled order-list m-b-0'>{$members_html}</ul>
                </td>
                <td>
                    <input type='range' class='form-control' min='0' max='100' value='{$task['task_completed_percent']}'
                           data-task-id='{$task['id']}'  oninput='this.nextElementSibling.textContent=this.value+\"%\"' >
                    <span>{$task['task_completed_percent']}%</span>
                </td>
                <td>{$task['created_at']}</td>
                <td>{$task['due_date']}</td>
                <td>
                <!-- Put this button in your tasks table for each row -->
<button type='button' class='btn btn-sm btn-primary edit-task-btn' data-task-id='{$task['id']}'>
    Edit
</button>
             
                    <button class='btn btn-sm btn-danger' onclick='deleteTask({$task['id']})'>Delete</button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No tasks found</td></tr>";
}
?>