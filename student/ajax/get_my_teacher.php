<?php
// select_my_teacher.php
session_start();
require_once '../sass/db_config.php';

$student_id = $_SESSION['student_id'] ?? 0;   // current student
$school_id  = $_SESSION['school_id'] ?? 0;    // current school

header('Content-Type: text/html; charset=UTF-8');

if (!$student_id || !$school_id) {
    echo "<option value=''>No session. Please re-login.</option>";
    exit;
}

// First get the class & section of this student
$sql_student = "
    SELECT class_grade, section
    FROM students
    WHERE id = ? AND school_id = ?
    LIMIT 1
";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param('ii', $student_id, $school_id);
$stmt_student->execute();
$res_student = $stmt_student->get_result();

if ($res_student->num_rows === 0) {
    echo "<option value=''>Student not found</option>";
    exit;
}

$student_data = $res_student->fetch_assoc();
$class_grade = $student_data['class_grade'];
$section     = $student_data['section'];

// Now find all teachers assigned to this class/section
$sql = "
SELECT DISTINCT
    f.id,
    f.full_name
FROM class_timetable_meta AS ctm
JOIN class_timetable_details AS ctd
      ON ctd.timing_meta_id = ctm.id
     AND ctm.school_id = ?
JOIN faculty AS f
      ON f.id = ctd.teacher_id
WHERE ctm.class_name = ?
  AND ctm.section    = ?
  AND f.status = 'active'
ORDER BY f.full_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $school_id, $class_grade, $section);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<option value=''>No teachers found</option>";
    exit;
}

echo "<option value=''>Select Teacher</option>";
while ($row = $res->fetch_assoc()) {
    $id   = (int)$row['id'];
    $name = htmlspecialchars($row['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    echo "<option value='{$id}'>{$name}</option>";
}