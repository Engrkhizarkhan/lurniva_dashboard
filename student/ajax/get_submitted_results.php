<?php
session_start();
require_once '../sass/db_config.php';

$school_id    = $_SESSION['campus_id'];
$assignment_id = $_POST['assignment_id'] ?? 0;

if (!$assignment_id) {
    echo "<tr><td colspan='12'>No assignment selected</td></tr>";
    exit;
}

// Fetch student results along with student info and assignment info
$sql = "
    SELECT sr.*, s.full_name, s.roll_number, s.class_grade, s.section,
           ta.subject, ta.type, ta.title, ta.due_date, ta.total_marks, ta.attachment AS assignment_attachment
    FROM student_results sr
    INNER JOIN students s ON s.id = sr.student_id
    INNER JOIN teacher_assignments ta ON ta.id = sr.assignment_id
    WHERE sr.school_id = ? AND sr.assignment_id = ?
    ORDER BY s.class_grade, s.section, s.roll_number
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $assignment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['student_id']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['roll_number']}</td>
                <td>{$row['class_grade']} - {$row['section']}</td>
                <td>{$row['subject']}</td>
                <td>{$row['type']}</td>
                <td>{$row['title']}</td>
                <td>{$row['due_date']}</td>
                <td>{$row['total_marks']}</td>
                <td>{$row['marks_obtained']}</td>
                <td>{$row['remarks']}</td>
                <td>";
        if (!empty($row['attachment'])) {
            echo "<a href='../uploads/{$row['attachment']}' target='_blank'>View</a>";
        } elseif (!empty($row['assignment_attachment'])) {
            echo "<a href='../uploads/{$row['assignment_attachment']}' target='_blank'>View</a>";
        } else {
            echo "N/A";
        }
        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='12'>No submitted results found for this assignment</td></tr>";
}