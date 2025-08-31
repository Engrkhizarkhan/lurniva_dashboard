<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['campus_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('<div class="alert alert-danger">Invalid request.</div>');
}

$marks    = $_POST['marks'] ?? [];
$remarks  = $_POST['remarks'] ?? [];
$total    = $_POST['total_marks'] ?? [];

if (empty($marks)) {
    exit('<div class="alert alert-warning">No results submitted.</div>');
}

// Prepare insert/update query
$stmt = $conn->prepare("
    INSERT INTO exam_results 
    (school_id, exam_schedule_id, student_id, subject_id, total_marks, marks_obtained, remarks) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        total_marks = VALUES(total_marks),
        marks_obtained = VALUES(marks_obtained), 
        remarks = VALUES(remarks),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;

foreach ($marks as $student_id => $subjects) {
    foreach ($subjects as $subject_id => $marks_obtained) {
        $student_id     = (int)$student_id;
        $subject_id     = (int)$subject_id;
        $marks_obtained = (int)$marks_obtained;
        $remarks_text   = $remarks[$student_id][$subject_id] ?? null;
        $total_marks    = (int)($total[$student_id][$subject_id] ?? 0);

        // Find exam_schedule_id for this subject
        $exam_schedule_id = 0;
        $es = $conn->prepare("SELECT id FROM exam_schedule WHERE school_id = ? AND subject_id = ?");
        $es->bind_param("ii", $school_id, $subject_id);
        $es->execute();
        $es->bind_result($exam_schedule_id);
        $es->fetch();
        $es->close();

        if ($exam_schedule_id) {
            $stmt->bind_param("iiiiiss", 
                $school_id, 
                $exam_schedule_id, 
                $student_id, 
                $subject_id, 
                $total_marks, 
                $marks_obtained, 
                $remarks_text
            );
            if ($stmt->execute()) {
                $inserted++;
            }
        }
    }
}

$stmt->close();
$conn->close();

echo "<div class='alert alert-success'>$inserted result(s) saved successfully.</div>";