<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (!$class_id) {
    exit("<tr><td colspan='5' class='text-center'>Class not selected.</td></tr>");
}

// Get class info
$class_info = $conn->query("SELECT class_name, section FROM class_timetable_meta WHERE id = '$class_id'")->fetch_assoc();
if (!$class_info) {
    exit("<tr><td colspan='5' class='text-center'>Invalid class.</td></tr>");
}

$class_name = $class_info['class_name'];
$section = $class_info['section'];

// Get students who have not been marked for today
$sql = "SELECT * FROM students 
        WHERE class_grade = '$class_name' 
        AND section = '$section'
        AND id NOT IN (
            SELECT student_id FROM student_attendance WHERE class_meta_id = '$class_id' AND date = '$date'
        )";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<tr><td colspan='5' class='text-center'>All students have been marked for today.</td></tr>";
    exit;
}

$counter = 1;
while ($row = $result->fetch_assoc()) {
    $photo = $row['profile_photo'] ? $row['profile_photo'] : 'default.png';
    echo "<tr>
            <td class='text-center'>{$counter}</td>
            <td class='text-center'><img src='uploads/profile/{$photo}' class='rounded-circle' width='35'></td>
            <td>{$row['full_name']}</td>
            <td>{$row['phone']}</td>
            <td class='text-center'>
                <label><input type='radio' name='status[{$row['id']}]' value='Present' checked> Present</label>
                <label class='ms-2'><input type='radio' name='status[{$row['id']}]' value='Absent'> Absent</label>
                <label class='ms-2'><input type='radio' name='status[{$row['id']}]' value='Leave'> Leave</label>
            </td>
          </tr>";
    $counter++;
}