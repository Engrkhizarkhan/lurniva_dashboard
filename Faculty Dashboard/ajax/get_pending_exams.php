<?php
session_start();
require_once '../sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;
$school_id  = $_SESSION['campus_id'] ?? 0;

if (!$teacher_id || !$school_id) {
    exit('<option value="">No exams found</option>');
}

// ✅ Get exams from exams table
$query = "
    SELECT id, exam_name, total_marks, created_at
    FROM exams
    WHERE school_id = ?
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Select Exam</option>';

while ($row = $result->fetch_assoc()) {
    echo '<option value="'.htmlspecialchars($row['id']).'">'
        .htmlspecialchars($row['exam_name']).' (Total: '.$row['total_marks'].')'
        .'</option>';
}

$stmt->close();
$conn->close();
?>