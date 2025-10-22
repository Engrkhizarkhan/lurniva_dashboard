<?php
session_start();
require_once '../sass/db_config.php';

$student_id = $_SESSION['student_id'] ?? 0;
$school_id  = $_SESSION['school_id'] ?? 0;

if (!$student_id || !$school_id) {
    exit("<option value=''>No exams</option>");
}

// ✅ Get student's class & section
$sql = "SELECT class_grade, section 
        FROM students 
        WHERE id=? AND school_id=? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    exit("<option value=''>No exams</option>");
}

$class_grade = $student['class_grade'];
$section     = $student['section'];

// ✅ Get DISTINCT exams for this class (real exam name)
$q = "SELECT DISTINCT e.id AS exam_id, e.exam_name
      FROM exam_schedule es
      INNER JOIN exams e ON es.exam_name = e.id
      WHERE es.class_name=? AND es.school_id=?";
$stmt = $conn->prepare($q);
$stmt->bind_param("si", $class_grade, $school_id);
$stmt->execute();
$res = $stmt->get_result();

$options = "";
while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['exam_id']}'>" . htmlspecialchars($row['exam_name']) . "</option>";
}

echo $options ?: "<option value=''>No exams found</option>";