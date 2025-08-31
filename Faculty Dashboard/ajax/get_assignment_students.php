<?php
session_start();
require_once '../sass/db_config.php';

$assignment_id = $_POST['assignment_id'] ?? 0;
$school_id     = $_SESSION['campus_id'] ?? 0;

if (!$assignment_id) {
    echo '<tr><td colspan="12">No students found</td></tr>';
    exit;
}

// Fetch assignment info
$stmt = $conn->prepare("SELECT * FROM teacher_assignments WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $assignment_id, $school_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    echo '<tr><td colspan="12">Assignment not found</td></tr>';
    exit;
}

// Get the class info
$class_meta_id = $assignment['class_meta_id'];
$class_stmt = $conn->prepare("SELECT * FROM class_timetable_meta WHERE id = ? AND school_id = ?");
$class_stmt->bind_param("ii", $class_meta_id, $school_id);
$class_stmt->execute();
$class_info = $class_stmt->get_result()->fetch_assoc();

if (!$class_info) {
    echo '<tr><td colspan="12">Class not found</td></tr>';
    exit;
}

// Fetch students in that class
$students_res = $conn->prepare("SELECT * FROM students WHERE school_id = ? AND class_grade = ? AND section = ?");
$students_res->bind_param("iss", $school_id, $class_info['class_name'], $class_info['section']);
$students_res->execute();
$students = $students_res->get_result();

if ($students->num_rows == 0) {
    echo '<tr><td colspan="12">No students found in this class</td></tr>';
    exit;
}

while ($student = $students->fetch_assoc()) {
    // Check if student already has result
    $check_res = $conn->prepare("SELECT id FROM student_results WHERE assignment_id = ? AND student_id = ?");
    $check_res->bind_param("ii", $assignment_id, $student['id']);
    $check_res->execute();
    $check_res->store_result();
    if ($check_res->num_rows > 0) continue; // skip if result exists

    echo '<tr>
        <td>'.$student['id'].'</td>
        <td>'.htmlspecialchars($student['full_name']).'</td>
        <td>'.$student['roll_number'].'</td>
        <td>'.htmlspecialchars($class_info['class_name']).'</td>
        <td>'.htmlspecialchars($assignment['subject']).'</td>
        <td>'.htmlspecialchars($assignment['type']).'</td>
        <td>'.htmlspecialchars($assignment['title']).'</td>
        <td>'.$assignment['due_date'].'</td>
        <td>'.$assignment['total_marks'].'</td>
        <td><input type="number" name="marks['.$student['id'].']" class="form-control" max="'.$assignment['total_marks'].'" required></td>
        <td><input type="text" name="remarks['.$student['id'].']" class="form-control"></td>
        <td><input type="file" name="attachment['.$student['id'].']" class="form-control"></td>
    </tr>';
}