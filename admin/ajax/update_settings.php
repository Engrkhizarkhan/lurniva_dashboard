<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

// ✅ Get admin_id from session
$admin_id = $_SESSION['admin_id'] ?? 0;
if (!$admin_id) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

// ✅ Define settings fields
$fields = [
  'layout','sidebar_color','color_theme','mini_sidebar','sticky_header',
  'attendance_enabled','behavior_enabled','chat_enabled','dairy_enabled',
  'exam_enabled','fee_enabled','library_enabled','meeting_enabled',
  'notice_board_enabled','assign_task_enabled','tests_assignments_enabled',
  'timetable_enabled','transport_enabled'
];

// ✅ Collect POST data
$data = [];
foreach($fields as $f) {
  $data[$f] = isset($_POST[$f]) ? 1 : 0;
}
$data['layout'] = $_POST['layout'] ?? 1;
$data['sidebar_color'] = $_POST['sidebar_color'] ?? 2;
$data['color_theme'] = $_POST['color_theme'] ?? 'white';

// ✅ Prepare update SQL
$set = implode(", ", array_map(fn($f) => "$f = ?", array_keys($data)));
$types = str_repeat("s", count($data)) . "i";

// Convert values array
$values = array_values($data);

$resFaculty = $conn->query("SELECT id FROM faculty WHERE campus_id = $admin_id");
while ($row = $resFaculty->fetch_assoc()) {
    $faculty_id = $row['id'];
    $sqlFac = "UPDATE school_settings SET $set, updated_at = NOW() WHERE person = 'facility' AND person_id = ?";
    $stmtFac = $conn->prepare($sqlFac);
    $facultyValues = [...$values, $faculty_id];
    $stmtFac->bind_param($types, ...$facultyValues);
    $stmtFac->execute();
}

// ============================
// 3️⃣ Update Student Settings
// ============================
$resStudents = $conn->query("SELECT id FROM students WHERE school_id = $admin_id");
while ($row = $resStudents->fetch_assoc()) {
    $student_id = $row['id'];
    $sqlStu = "UPDATE school_settings SET $set, updated_at = NOW() WHERE person = 'student' AND person_id = ?";
    $stmtStu = $conn->prepare($sqlStu);
    $studentValues = [...$values, $student_id];
    $stmtStu->bind_param($types, ...$studentValues);
    $stmtStu->execute();
}

// ============================
// 1️⃣ Update Admin Settings
// ============================
$sqlAdmin = "UPDATE school_settings SET $set, updated_at = NOW() WHERE person = 'admin' AND person_id = ?";
$stmtAdmin = $conn->prepare($sqlAdmin);
$adminValues = [...$values, $admin_id];
$stmtAdmin->bind_param($types, ...$adminValues);
$stmtAdmin->execute();

// ============================
// 2️⃣ Update Faculty Settings
// ============================
echo json_encode(['status'=>'success','message'=>'Settings updated for admin, all faculty, and all students']);