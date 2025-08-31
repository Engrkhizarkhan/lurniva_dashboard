<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_POST['admin_id'];
$class_name = isset($_POST['class_grade']) ? $conn->real_escape_string($_POST['class_grade']) : '';

$sql = "SELECT DISTINCT section FROM class_timetable_meta 
        WHERE school_id = '$school_id' AND class_name = '$class_name'";
        
$result = $conn->query($sql);

$sections = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = ['section' => $row['section']];
    }
    echo json_encode(['status' => 'success', 'sections' => $sections]);
} else {
    echo json_encode(['status' => 'error', 'sections' => []]);
}

$conn->close();