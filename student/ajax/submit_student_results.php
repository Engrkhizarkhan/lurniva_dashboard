<?php
session_start();
require_once '../sass/db_config.php';

$school_id     = $_SESSION['campus_id'] ?? 0;
$assignment_id = $_POST['assignment_id'] ?? 0;
$marks_arr     = $_POST['marks'] ?? [];
$remarks_arr   = $_POST['remarks'] ?? [];

if (!$assignment_id || empty($marks_arr)) {
    echo '<div class="alert alert-danger">No data to submit.</div>';
    exit;
}

// Loop through each student
foreach ($marks_arr as $student_id => $marks) {
    $remarks = $remarks_arr[$student_id] ?? '';

    // Handle attachment
    $attachment = '';
    if (isset($_FILES['attachment']['name'][$student_id]) && $_FILES['attachment']['name'][$student_id] != '') {
        $filename = time().'_'.basename($_FILES['attachment']['name'][$student_id]);
        $target = '../uploads/results/'.$filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'][$student_id], $target)) {
            $attachment = $filename;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO student_results (school_id, assignment_id, student_id, marks_obtained, remarks, attachment, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiiiss", $school_id, $assignment_id, $student_id, $marks, $remarks, $attachment);
    $stmt->execute();
}

echo '<div class="alert alert-success">Results submitted successfully.</div>';