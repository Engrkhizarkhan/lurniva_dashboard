<?php
session_start();
require_once '../sass/db_config.php'; // adjust path

if (!isset($_POST['exam_id']) || empty($_POST['exam_id'])) {
    echo '<tr><td colspan="10" class="text-center text-danger">Invalid Exam</td></tr>';
    exit;
}

$exam_id   = intval($_POST['exam_id']);
$school_id = $_SESSION['campus_id'];

// ✅ Get exam info
$examQ  = mysqli_query($conn, "SELECT * FROM exams WHERE id='$exam_id' AND school_id='$school_id'");
$exam   = mysqli_fetch_assoc($examQ);

if (!$exam) {
    echo '<tr><td colspan="10" class="text-center text-danger">Exam not found</td></tr>';
    exit;
}
$exam_name = $exam['exam_name'];

// ✅ Get exam schedules for this exam (use exam_id, not name!)
$scheduleQ = mysqli_query($conn, "SELECT * FROM exam_schedule WHERE exam_name='$exam_id' AND school_id='$school_id'");
if (mysqli_num_rows($scheduleQ) == 0) {
    echo '<tr><td colspan="10" class="text-center">No schedule found for this exam</td></tr>';
    exit;
}

while ($sub = mysqli_fetch_assoc($scheduleQ)) {
    $class_name  = $sub['class_name'];
    $subject_id  = $sub['subject_id'];

    // ✅ Get subject name from class_timetable_details
    $subQ = mysqli_query($conn, "SELECT period_name FROM class_timetable_details WHERE id='$subject_id' LIMIT 1");
    $subRow = mysqli_fetch_assoc($subQ);
    $subject_name = $subRow ? $subRow['period_name'] : 'Unknown';

    // ✅ Get students of that class
    $stuQ = mysqli_query($conn, "SELECT * FROM students WHERE class_grade='$class_name' AND school_id='$school_id'");
    if (mysqli_num_rows($stuQ) == 0) {
        echo '<tr><td colspan="10" class="text-center">No students found for class '.$class_name.'</td></tr>';
        continue;
    }

    while ($student = mysqli_fetch_assoc($stuQ)) {
        echo '<tr>
            <td>'.$student['id'].'</td>
            <td>'.htmlspecialchars($student['full_name']).'</td>
            <td>'.$student['roll_number'].'</td>
            <td>'.htmlspecialchars($class_name).'</td>
            <td>'.htmlspecialchars($subject_name).'</td>
            <td>'.htmlspecialchars($exam_name).'</td>
            <td>'.date("d-M-Y", strtotime($sub['exam_date'])).'</td>
            <td>'.$sub['total_marks'].'</td>
            <td>
                <input type="number" name="marks['.$student['id'].']['.$subject_id.']" 
                       class="form-control" max="'.$sub['total_marks'].'" required>
            </td>
            <td>
                <input type="text" name="remarks['.$student['id'].']['.$subject_id.']" class="form-control">
            </td>
        </tr>';
    }
}