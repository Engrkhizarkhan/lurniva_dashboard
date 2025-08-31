<?php
session_start();
require_once '../sass/db_config.php';

$school_id       = $_SESSION['campus_id'] ?? 0;
$exam_schedule_id = $_POST['exam_schedule_id'] ?? 0;

if (!$exam_schedule_id) {
    echo "<tr><td colspan='11'>No exam selected</td></tr>";
    exit;
}

$sql = "
    SELECT er.id AS result_id, er.marks_obtained, er.remarks,
           s.id AS student_id, s.full_name, s.roll_number, s.class_grade, s.section,
           es.exam_date, es.total_marks AS subject_total, es.subject_id,
           e.exam_name, e.total_marks AS exam_total
    FROM exam_results er
    INNER JOIN students s ON s.id = er.student_id
    INNER JOIN exam_schedule es ON es.id = er.exam_schedule_id
    INNER JOIN exams e ON e.id = es.exam_name   -- exam_name is exam_id FK
    WHERE er.school_id = ? AND er.exam_schedule_id = ?
    ORDER BY s.class_grade, s.section, s.roll_number
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $exam_schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['student_id']}</td>
                <td>" . htmlspecialchars($row['full_name']) . "</td>
                <td>{$row['roll_number']}</td>
                <td>{$row['class_grade']} - {$row['section']}</td>
                <td>{$row['subject_id']}</td>
                <td>" . htmlspecialchars($row['exam_name']) . "dfdsaf</td>
                <td>" . date('d-M-Y', strtotime($row['exam_date'])) . "</td>
                <td>{$row['subject_total']}</td>
                <td>{$row['marks_obtained']}</td>
                <td>{$row['remarks']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='11'>No submitted results found for this exam</td></tr>";
}
?>