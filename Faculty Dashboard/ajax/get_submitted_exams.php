<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['campus_id'] ?? 0;

if (!$school_id) {
    exit('<option value="">No exams found</option>');
}

// ✅ Fetch distinct submitted exams (from exam_results)
$query = "
    SELECT DISTINCT e.id AS exam_id, e.exam_name, es.class_name
    FROM exam_results er
    JOIN exam_schedule es ON er.exam_schedule_id = es.id
    JOIN exams e ON es.exam_name = e.id
    WHERE er.school_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<option value="">No submitted exams found</option>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    // value can be exam_id|class_name (so you can parse later)
    $value = $row['exam_id'] . '|' . $row['class_name'];
    $label = $row['exam_name'] . ' - ' . $row['class_name'];
    echo '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($label) . '</option>';
}