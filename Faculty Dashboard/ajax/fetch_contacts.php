<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['campus_id'];
$admin_id = $_SESSION['admin_id'];

$output = "";

// Fetch all distinct user IDs that admin has chatted with
$sql = "SELECT DISTINCT 
            IF(sender_designation = 'teacher', receiver_id, sender_id) AS user_id,
            IF(sender_designation = 'teacher', receiver_designation, sender_designation) AS designation
        FROM messages
        WHERE (sender_designation = 'teacher' OR receiver_designation = 'teacher') AND  (sender_id = '$admin_id' OR receiver_id = '$admin_id')
        AND school_id = '$school_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['user_id'];
        $designation = $row['designation'];

        if ($designation == 'admin') {
            $user_q = mysqli_query($conn, "SELECT school_name AS full_name, logo AS photo FROM schools WHERE id = '$user_id'");
            $imagePath='../admin/uploads/logos/';
        } elseif ($designation == 'student') {
            $user_q = mysqli_query($conn, "SELECT full_name, profile_photo AS photo FROM students WHERE id = '$user_id'");
            $imagePath='../student/uploads/profile/';

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
                <img src="'.$imagePath.$photo.'" alt="avatar">
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