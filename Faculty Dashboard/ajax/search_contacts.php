<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['campus_id'];   // campus id = school id
$teacher_id = $_SESSION['admin_id'];   // logged-in teacher id
$keyword = trim($_POST['keyword'] ?? '');

$output = "";

// ============================
// 1. Search for Admin
// ============================
if (!empty($keyword) && stripos('admin', $keyword) !== false) {
    $sqlAdmin = "SELECT id, school_name, logo 
                 FROM schools 
                 WHERE id = '$school_id'";
    $resAdmin = mysqli_query($conn, $sqlAdmin);

    if ($resAdmin && mysqli_num_rows($resAdmin) > 0) {
        $admin = mysqli_fetch_assoc($resAdmin);
        $name = $admin['school_name'] . " (Admin)";
        $photo = !empty($admin['logo']) ? $admin['logo'] : 'assets/img/default-user.png';
        $imagePath = '../admin/uploads/logos/';

        $output .= '
        <li class="clearfix open-chat-data"
            data-sender-id="'.$admin['id'].'"
            data-sender-designation="admin">
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

// ============================
// 2. Search for Students of this Teacher
// ============================

// Get all classes taught by this teacher
$sqlClasses = "SELECT DISTINCT ctm.class_name, ctm.section
               FROM class_timetable_details ctd
               INNER JOIN class_timetable_meta ctm 
                      ON ctd.timing_meta_id = ctm.id
               WHERE ctd.teacher_id = '$teacher_id'
                 AND ctm.school_id = '$school_id'";
$resClasses = mysqli_query($conn, $sqlClasses);

$classList = [];
while ($row = mysqli_fetch_assoc($resClasses)) {
    $classList[] = "'" . $row['class_name'] . "'";
}

if (!empty($classList)) {
    $classListStr = implode(",", $classList);

    $sqlStudents = "SELECT id, full_name, profile_photo, class_grade, section 
                    FROM students
                    WHERE school_id = '$school_id'
                      AND class_grade IN ($classListStr)";

    if (!empty($keyword) && stripos($keyword, 'admin') === false) {
        $keywordEsc = mysqli_real_escape_string($conn, $keyword);
        $sqlStudents .= " AND (full_name LIKE '%$keywordEsc%'
                            OR roll_number LIKE '%$keywordEsc%'
                            OR email LIKE '%$keywordEsc%')";
    }

    $resStudents = mysqli_query($conn, $sqlStudents);

    if ($resStudents && mysqli_num_rows($resStudents) > 0) {
        while ($stu = mysqli_fetch_assoc($resStudents)) {
            $name = $stu['full_name'] . " (" . $stu['class_grade'] . "-" . $stu['section'] . ")";
            $photo = !empty($stu['profile_photo']) ? $stu['profile_photo'] : 'assets/img/default-user.png';
            $imagePath = '../student/uploads/profile/';

            $output .= '
            <li class="clearfix open-chat-data"
                data-sender-id="'.$stu['id'].'"
                data-sender-designation="student">
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
}

// ============================
// 3. If nothing found
// ============================
if (empty($output)) {
    $output = "<li>No contacts found</li>";
}

echo $output;