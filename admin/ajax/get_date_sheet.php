<?php
require '../sass/db_config.php';
session_start();

$exam_name  = $_POST['exam_name'] ?? '';
$class_name = $_POST['class_name'] ?? '';
$school_id  = $_SESSION['admin_id'] ?? 0;

if (!$exam_name || !$class_name) {
    echo "<div class='alert alert-warning'>Please select exam and class.</div>";
    exit;
}

$sql = "
    SELECT ed.exam_name, d.period_name, e.exam_date, e.exam_time, e.day
    FROM exam_schedule e
    JOIN class_timetable_details d ON d.id = e.subject_id
    JOIN class_timetable_meta m ON m.id = d.timing_meta_id
    JOIN exams ed ON ed.id = e.exam_name
    WHERE e.school_id = ? 
      AND ed.id = ? 
      AND e.class_name = ? 
      AND m.school_id = ?
    ORDER BY e.exam_date, e.exam_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $school_id, $exam_name, $class_name, $school_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "<div class='alert alert-info'>No date sheet found for this class/exam.</div>";
    exit;
}
$row = $res->fetch_assoc();
echo "<h4 class='mb-3 text-center'>{$row["exam_name"]} - Class $class_name</h4>";
echo "<table class='table table-bordered'>
        <thead class='bg-primary text-white'>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Time</th>
                <th>Subject</th>
            </tr>
        </thead>
        <tbody>";

while ($row = $res->fetch_assoc()) {
    echo "<tr>
            <td>" . date('d-M-Y', strtotime($row['exam_date'])) . "</td>
            <td>{$row['day']}</td>
            <td>" . date('h:i A', strtotime($row['exam_time'])) . "</td>
            <td>{$row['period_name']}</td>
          </tr>";
}

echo "</tbody></table>";

$stmt->close();
$conn->close();
?>