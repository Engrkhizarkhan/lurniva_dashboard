<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'] ?? 0;
$keyword   = trim($_POST['keyword'] ?? '');

$output = "";

if (empty($keyword)) {
    echo "<li>No results</li>";
    exit;
}

// Escape for LIKE
$keyword = "%".$conn->real_escape_string($keyword)."%";

// =====================
// Search Students
// =====================
$sql_students = "SELECT id, full_name, profile_photo 
                 FROM students 
                 WHERE school_id = '$school_id' 
                   AND (full_name LIKE '$keyword' OR roll_number LIKE '$keyword')";
$res_students = mysqli_query($conn, $sql_students);

// =====================
// Search Faculty
// =====================
$sql_faculty = "SELECT id, full_name, photo 
                FROM faculty 
                WHERE campus_id = '$school_id' 
                  AND (full_name LIKE '$keyword' OR subjects LIKE '$keyword')";
$res_faculty = mysqli_query($conn, $sql_faculty);

// =====================
// Build Student Results
// =====================
if ($res_students && mysqli_num_rows($res_students) > 0) {
    while ($row = mysqli_fetch_assoc($res_students)) {
        $photo = !empty($row['profile_photo']) ? "uploads/profile/".$row['profile_photo'] : "assets/img/default-user.png";
        $output .= '
        <li class="clearfix open-chat-data"
            data-sender-id="'.$row['id'].'"
            data-sender-designation="student">
            <img src="'.$photo.'" alt="avatar">
            <div class="about">
                <div class="name">'.$row['full_name'].'</div>
                <div class="status"><i class="material-icons online">fiber_manual_record</i> student</div>
            </div>
        </li>';
    }
}

// =====================
// Build Faculty Results
// =====================
if ($res_faculty && mysqli_num_rows($res_faculty) > 0) {
    while ($row = mysqli_fetch_assoc($res_faculty)) {
        $photo = !empty($row['photo']) ? "uploads/profile/".$row['photo'] : "assets/img/default-user.png";
        $output .= '
        <li class="clearfix open-chat-data"
            data-sender-id="'.$row['id'].'"
            data-sender-designation="teacher">
            <img src="'.$photo.'" alt="avatar">
            <div class="about">
                <div class="name">'.$row['full_name'].'</div>
                <div class="status"><i class="material-icons online">fiber_manual_record</i> teacher</div>
            </div>
        </li>';
    }
}

if (empty($output)) {
    $output = "<li>No matching users</li>";
}

echo $output;