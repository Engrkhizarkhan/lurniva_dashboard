<?php
session_start();
require_once '../sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;
$school_id  = $_SESSION['campus_id'] ?? 0;

if (!$teacher_id) exit('<option value="">No assignments found</option>');

// Fetch assignments where not all students have results
$query = "
SELECT ta.id, ta.title, ta.type, ctm.class_name, ctm.section
FROM teacher_assignments ta
JOIN class_timetable_meta ctm ON ta.class_meta_id = ctm.id
WHERE ta.school_id = ? AND ta.teacher_id = ?
AND NOT EXISTS (
    SELECT 1 FROM student_results sr
    WHERE sr.assignment_id = ta.id
)
ORDER BY ta.due_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Select Test/Assignment</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['type'].' - '.$row['title'].' ('.$row['class_name'].'-'.$row['section'].')').'</option>';
}