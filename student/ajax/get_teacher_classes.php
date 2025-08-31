<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("<option value=''>Unauthorized</option>");
}

$teacher_id = $_SESSION['admin_id'];
$current_date = date('Y-m-d');

// Get classes assigned to this teacher
$sql = "SELECT DISTINCT ctm.id AS class_meta_id, ctm.class_name, ctm.section
        FROM class_timetable_meta ctm
        JOIN class_timetable_details ctd ON ctd.timing_meta_id = ctm.id
        WHERE ctd.teacher_id = ?
        ORDER BY ctm.class_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$options = "<option value=''>-- Select Class --</option>";

while ($row = $result->fetch_assoc()) {
    $class_id = $row['class_meta_id'];

    // Count total students
    $total_students = $conn->query("SELECT COUNT(*) AS cnt 
                                    FROM students 
                                    WHERE class_grade = '{$row['class_name']}' 
                                    AND section = '{$row['section']}'")->fetch_assoc()['cnt'];

    // Count attendance marked today
    $marked_students = $conn->query("SELECT COUNT(*) AS cnt 
                                     FROM student_attendance 
                                     WHERE class_meta_id = '$class_id' 
                                     AND date = '$current_date'")->fetch_assoc()['cnt'];

    // If not all students marked, show class
    if ($marked_students < $total_students) {
        $options .= "<option value='{$class_id}'>{$row['class_name']} - {$row['section']}</option>";
    }
}

echo $options;