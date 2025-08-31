<?php
session_start();
require_once '../sass/db_config.php';

$school_id  = $_SESSION['campus_id'];
$teacher_id = $_SESSION['admin_id'];

// Get assignments for this teacher that have at least one submitted result
$sql = "
    SELECT DISTINCT ta.id, ta.title 
    FROM teacher_assignments ta
    INNER JOIN student_results sr ON sr.assignment_id = ta.id
    WHERE ta.school_id = ? AND ta.teacher_id = ?
    ORDER BY ta.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$options = "";
while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['title']}</option>";
}

echo $options;