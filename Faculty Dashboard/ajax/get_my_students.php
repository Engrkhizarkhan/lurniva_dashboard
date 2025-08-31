<?php
// get_my_students.php
session_start();
require_once '../sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;   // current teacher
$school_id  = $_SESSION['campus_id'] ?? 0;  // current school

header('Content-Type: text/html; charset=UTF-8');

if (!$teacher_id || !$school_id) {
    echo "<option value=''>No session. Please re-login.</option>";
    exit;
}

/*
 We find classes taught by this teacher (from class_timetable_details),
 map them to class/section (from class_timetable_meta),
 then list students whose class_grade/section match those class/section
 within the same school.
*/
$sql = "
SELECT DISTINCT
    s.id,
    s.full_name,
    s.roll_number,
    s.class_grade,
    s.section
FROM class_timetable_details AS ctd
JOIN class_timetable_meta AS ctm
      ON ctm.id = ctd.timing_meta_id
     AND ctm.school_id = ?
JOIN students AS s
      ON s.school_id   = ctm.school_id
     AND s.class_grade = ctm.class_name
     AND s.section     = ctm.section
WHERE ctd.teacher_id = ?
ORDER BY
    s.class_grade ASC,
    s.section ASC,
    s.roll_number ASC,
    s.full_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $school_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<option value=''>No students found for your classes</option>";
    exit;
}

echo "<option value=''>Select Student</option>";
while ($row = $res->fetch_assoc()) {
    // Safe output
    $id    = (int)$row['id'];
    $name  = htmlspecialchars($row['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $roll  = htmlspecialchars($row['roll_number'] ?? '', ENT_QUOTES, 'UTF-8');
    $grade = htmlspecialchars($row['class_grade'] ?? '', ENT_QUOTES, 'UTF-8');
    $sec   = htmlspecialchars($row['section'] ?? '', ENT_QUOTES, 'UTF-8');

    echo "<option value='{$id}' >"
       . "{$name} ({$grade}-{$sec}, Roll: {$roll})"
       . "</option>";
}