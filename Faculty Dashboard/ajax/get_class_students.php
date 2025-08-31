<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (!$class_id) {
    exit("<tr><td colspan='6' class='text-center'>Class not selected.</td></tr>");
}

// Get class info
$class_info = $conn->query("SELECT class_name, section FROM class_timetable_meta WHERE id = '$class_id'")->fetch_assoc();
if (!$class_info) {
    exit("<tr><td colspan='6' class='text-center'>Invalid class.</td></tr>");
}

$class_name = $class_info['class_name'];
$section = $class_info['section'];

// Get all students of this class/section
$sql = "SELECT * FROM students 
        WHERE class_grade = '$class_name' 
        AND section = '$section'";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<tr><td colspan='6' class='text-center'>No students found for this class.</td></tr>";
    exit;
}

$counter = 1;
while ($row = $result->fetch_assoc()) {
    $photo = $row['profile_photo'] ? $row['profile_photo'] : 'default.png';

    // Check if attendance already exists for this student
    $att_sql = "SELECT status FROM student_attendance 
                WHERE class_meta_id = '$class_id' 
                AND student_id = '{$row['id']}' 
                AND date = '$date' 
                LIMIT 1";
    $att_result = $conn->query($att_sql);
    $attendance_status = $att_result && $att_result->num_rows > 0 
                        ? $att_result->fetch_assoc()['status'] 
                        : 'Present'; // default

    // Pre-select radio based on saved status
    $present_checked = ($attendance_status == 'Present') ? "checked" : "";
    $absent_checked  = ($attendance_status == 'Absent') ? "checked" : "";
    $leave_checked   = ($attendance_status == 'Leave') ? "checked" : "";

    echo "<tr>
            <td class='text-center'>{$counter}</td>
            <td class='text-center'><img src='uploads/profile/{$photo}' class='rounded-circle' width='35'></td>
            <td>{$row['full_name']}</td>
            <td>{$row['phone']}</td>
            <td class='text-center'>
                <label><input type='radio' name='status[{$row['id']}]' value='Present' {$present_checked}> Present</label>
                <label class='ms-2'><input type='radio' name='status[{$row['id']}]' value='Absent' {$absent_checked}> Absent</label>
                <label class='ms-2'><input type='radio' name='status[{$row['id']}]' value='Leave' {$leave_checked}> Leave</label>
            </td>
          </tr>";
    $counter++;
}
?>