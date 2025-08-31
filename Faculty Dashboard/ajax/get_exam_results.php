<?php
session_start();
require_once '../sass/db_config.php';

$exam_data = $_POST['exam_id'] ?? ''; // examId|className
$school_id = $_SESSION['campus_id'] ?? 0;

if (!$exam_data || !$school_id) {
    echo '<tr><td colspan="10" class="text-center">No results found</td></tr>';
    exit;
}

list($exam_id, $class_name) = explode('|', $exam_data);

// ✅ Fetch exam results with exam + student + subject
$query = "
    SELECT 
        er.id AS result_id,
        s.full_name, 
        s.roll_number, 
        er.marks_obtained, 
        er.remarks, 
        e.exam_name, 
        es.class_name, 
        es.exam_date, 
        es.total_marks,  -- subject total marks
        ctd.period_name
    FROM exam_results er
    JOIN students s 
        ON er.student_id = s.id
    JOIN exam_schedule es 
        ON er.exam_schedule_id = es.id
    JOIN exams e 
        ON es.exam_name = e.id   -- exam_id -> exams table
    JOIN class_timetable_details ctd 
        ON er.subject_id = ctd.id
    JOIN class_timetable_meta ctm 
        ON ctd.timing_meta_id = ctm.id
    WHERE er.school_id = ? 
      AND es.exam_name = ? 
      AND es.class_name = ?
    ORDER BY s.roll_number ASC, ctd.period_number ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $school_id, $exam_id, $class_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<tr><td colspan="10" class="text-center">No results found for this exam</td></tr>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo '<tr>
        <td>'.htmlspecialchars($row['result_id']).'</td>
        <td>'.htmlspecialchars($row['full_name']).'</td>
        <td>'.htmlspecialchars($row['roll_number']).'</td>
        <td>'.htmlspecialchars($row['class_name']).'</td>
        <td>'.htmlspecialchars($row['period_name']).'</td>
        <td>'.htmlspecialchars($row['exam_name']).'</td>
        <td>'.date("d-M-Y", strtotime($row['exam_date'])).'</td>
        <td>'.htmlspecialchars($row['total_marks']).'</td>
        <td>'.htmlspecialchars($row['marks_obtained']).'</td>
        <td>'.htmlspecialchars($row['remarks']).'</td>
    </tr>';
}
?>