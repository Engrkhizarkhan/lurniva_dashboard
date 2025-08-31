<?php
session_start();
require_once '../sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;

if ($teacher_id > 0) {
    // Query distinct classes for this teacher
    $stmt = $conn->prepare("
        SELECT DISTINCT ctm.id, ctm.class_name, ctm.section
        FROM class_timetable_meta ctm
        JOIN class_timetable_details ctd ON ctd.timing_meta_id = ctm.id
        WHERE ctd.teacher_id = ?
        ORDER BY ctm.class_name ASC
    ");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = '';
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="' . $row['id'] . '">' 
                    . htmlspecialchars($row['class_name'] . ' - ' . $row['section']) 
                    . '</option>';
    }

    echo $options;
} else {
    echo '<option value="">No classes found</option>';
}