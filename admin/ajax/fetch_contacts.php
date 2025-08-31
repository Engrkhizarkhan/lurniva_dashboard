<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'];
$admin_id = $_SESSION['admin_id'];

$output = "";

// Fetch all distinct user IDs that admin has chatted with
$sql = "SELECT DISTINCT 
            IF(sender_designation = 'admin', receiver_id, sender_id) AS user_id,
            IF(sender_designation = 'admin', receiver_designation, sender_designation) AS designation
        FROM messages
        WHERE (sender_designation = 'admin' OR receiver_designation = 'admin')
        AND school_id = '$school_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['user_id'];
        $designation = $row['designation'];

        if ($designation == 'teacher') {
            $user_q = mysqli_query($conn, "SELECT full_name, photo FROM faculty WHERE id = '$user_id'");
        } elseif ($designation == 'student') {
            $user_q = mysqli_query($conn, "SELECT full_name, profile_photo AS photo FROM students WHERE id = '$user_id'");
        } else {
            continue;
        }

        if (mysqli_num_rows($user_q) > 0) {
            $user = mysqli_fetch_assoc($user_q);
            $name = $user['full_name'];
            $photo = !empty($user['photo']) ? $user['photo'] : 'assets/img/default-user.png';

            $output .= '
            <li class="clearfix open-chat-data"
                data-sender-id="'.$user_id.'"
                data-sender-designation="'.$designation.'">
                <img src="uploads/profile/'.$photo.'" alt="avatar">
                <div class="about">
                    <div class="name">'.$name.'</div>
                    <div class="status">
                        <i class="material-icons online">fiber_manual_record</i> online
                    </div>
                </div>
            </li>';
        }
    }
} else {
    $output = "<li>No chat contacts found</li>";
}

echo $output;