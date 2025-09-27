<?php
session_start();
require_once '../sass/db_config.php';

$school_id      = $_SESSION['campus_id'] ?? 0;
$assignment_id  = $_POST['assignment_id'] ?? 0;

if (!$assignment_id) {
    echo "<tr><td colspan='12'>No assignment selected</td></tr>";
    exit;
}

$sql = "
    SELECT sr.id AS result_id,
           s.id AS student_id, s.full_name, s.roll_number, s.class_grade, s.section,
           ta.type, ta.title, ta.due_date, ta.total_marks AS assignment_total,
           ctd.period_name AS subject_name,
           sr.marks_obtained, sr.remarks, sr.attachment
    FROM student_results sr
    INNER JOIN students s ON s.id = sr.student_id
    INNER JOIN teacher_assignments ta ON ta.id = sr.assignment_id
    LEFT JOIN class_timetable_details ctd ON ctd.id = ta.subject
    WHERE sr.school_id = ? AND sr.assignment_id = ?
    ORDER BY s.class_grade, s.section, s.roll_number
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<tr><td colspan='12'>Query prepare failed: " . htmlspecialchars($conn->error) . "</td></tr>";
    exit;
}

$stmt->bind_param("ii", $school_id, $assignment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id            = (int)$row['student_id'];
        $name          = htmlspecialchars($row['full_name']);
        $roll          = htmlspecialchars($row['roll_number']);
        $class         = htmlspecialchars(($row['class_grade'] ?? '') . ' - ' . ($row['section'] ?? ''));
        $subject       = htmlspecialchars($row['subject_name'] ?? 'N/A');
        $type          = htmlspecialchars($row['type'] ?? '');
        $title         = htmlspecialchars($row['title'] ?? '');
        $due_date      = !empty($row['due_date']) ? date('d-M-Y', strtotime($row['due_date'])) : '-';
        $total_marks   = (int)($row['assignment_total'] ?? 0);
        $obtained      = (int)($row['marks_obtained'] ?? 0);
        $remarks       = htmlspecialchars($row['remarks'] ?? '');
        $attachment    = $row['attachment'] ? "<a href='../uploads/{$row['attachment']}' target='_blank'>View</a>" : '-';

        echo "<tr>
                <td>{$id}</td>
                <td>{$name}</td>
                <td>{$roll}</td>
                <td>{$class}</td>
                <td>{$subject}</td>
                <td>{$type}</td>
                <td>{$title}</td>
                <td>{$due_date}</td>
                <td>{$total_marks}</td>
                <td>{$obtained}</td>
                <td>{$remarks}</td>
                <td>{$attachment}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='12'>No results found for this assignment</td></tr>";
}

$stmt->close();
?>