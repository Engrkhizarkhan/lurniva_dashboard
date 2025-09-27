<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['school_id']; 
$student_id = $_SESSION['student_id'];
$keyword = trim($_POST['keyword'] ?? '');

$output = "";

// ============================
// 1. Search Admin
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
                <div class="status"><i class="material-icons online">fiber_manual_record</i> online</div>
            </div>
        </li>';
    }
}

// ============================
// 2. Get Student Class Info
// ============================
$stuRes = mysqli_query($conn, "SELECT class_grade, section FROM students WHERE id = '$student_id' AND school_id = '$school_id'");
$stuData = mysqli_fetch_assoc($stuRes);
$class_grade = $stuData['class_grade'];
$section     = $stuData['section'];

// ============================
// 3. Search Teachers of this class
// ============================
$sqlTeachers = "SELECT DISTINCT f.id, f.full_name, f.photo 
                FROM class_timetable_details ctd
                INNER JOIN class_timetable_meta ctm ON ctd.timing_meta_id = ctm.id
                INNER JOIN faculty f ON ctd.teacher_id = f.id
                WHERE ctm.class_name = '$class_grade'
                  AND ctm.section = '$section'
                  AND ctm.school_id = '$school_id'";

if (!empty($keyword) && stripos($keyword, 'admin') === false) {
    $keywordEsc = mysqli_real_escape_string($conn, $keyword);
    $sqlTeachers .= " AND f.full_name LIKE '%$keywordEsc%'";
}

$resTeachers = mysqli_query($conn, $sqlTeachers);
if ($resTeachers && mysqli_num_rows($resTeachers) > 0) {
    while ($t = mysqli_fetch_assoc($resTeachers)) {
        $name = $t['full_name'];
        $photo = !empty($t['photo']) ? $t['photo'] : 'assets/img/default-user.png';
        $imagePath = '../Faculty Dashboard/uploads/profile/';

        $output .= '
        <li class="clearfix open-chat-data"
            data-sender-id="'.$t['id'].'"
            data-sender-designation="teacher">
            <img src="'.$imagePath.$photo.'" alt="avatar">
            <div class="about">
                <div class="name">'.$name.'</div>
                <div class="status"><i class="material-icons online">fiber_manual_record</i> online</div>
            </div>
        </li>';
    }
}

// ============================
// 4. Search Classmates (students in same class & section)
// ============================
$sqlMates = "SELECT id, full_name, profile_photo 
             FROM students
             WHERE school_id = '$school_id'
               AND class_grade = '$class_grade'
               AND section = '$section'
               AND id != '$student_id'";

if (!empty($keyword) && stripos($keyword, 'admin') === false) {
    $keywordEsc = mysqli_real_escape_string($conn, $keyword);
    $sqlMates .= " AND (full_name LIKE '%$keywordEsc%' OR roll_number LIKE '%$keywordEsc%')";
}

$resMates = mysqli_query($conn, $sqlMates);
if ($resMates && mysqli_num_rows($resMates) > 0) {
    while ($m = mysqli_fetch_assoc($resMates)) {
        $name = $m['full_name'];
        $photo = !empty($m['profile_photo']) ? $m['profile_photo'] : 'assets/img/default-user.png';
        $imagePath = 'uploads/profile/';

        $output .= '
        <li class="clearfix open-chat-data"
            data-sender-id="'.$m['id'].'"
            data-sender-designation="student">
            <img src="'.$imagePath.$photo.'" alt="avatar">
            <div class="about">
                <div class="name">'.$name.'</div>
                <div class="status"><i class="material-icons online">fiber_manual_record</i> online</div>
            </div>
        </li>';
    }
}

// ============================
// 5. If nothing found
// ============================
if (empty($output)) {
    $output = "<li>No contacts found</li>";
}

echo $output;