<?php
session_start();
require_once '../sass/db_config.php';

$teacher_id   = $_SESSION['admin_id'] ?? 0;
$class_meta_id = $_POST['class_id'] ?? 0;

if ($teacher_id > 0 && $class_meta_id > 0) {
    // Query subjects for this teacher in the selected class
    $stmt = $conn->prepare("
        SELECT DISTINCT ctd.period_name AS subject
        FROM class_timetable_details ctd
        WHERE ctd.teacher_id = ? AND ctd.timing_meta_id = ?
        ORDER BY ctd.period_name ASC
    ");
    $stmt->bind_param("ii", $teacher_id, $class_meta_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="' . htmlspecialchars($row['subject']) . '">'
                    . htmlspecialchars($row['subject']) . '</option>';
    }

    echo $options;
} else {
    echo '<option value="">No subjects found</option>';
}