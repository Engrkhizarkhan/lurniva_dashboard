<?php
require_once 'admin/sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['school_id']) || !isset($_GET['class_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$school_id = intval($_GET['school_id']);
$class_name = $conn->real_escape_string($_GET['class_name']);

// Get distinct sections for this school and class
$sql = "SELECT DISTINCT section FROM class_timetable_meta 
        WHERE school_id = $school_id AND class_name = '$class_name' 
        ORDER BY section ASC";

$result = $conn->query($sql);

$sections = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row['section'];
    }
}

echo json_encode(['status' => 'success', 'data' => $sections]);
?>