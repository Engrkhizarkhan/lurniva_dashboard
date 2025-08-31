<?php
require_once 'admin/sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['school_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'School ID missing']);
    exit;
}

$school_id = intval($_GET['school_id']);

$sql = "SELECT DISTINCT class_name FROM class_timetable_meta WHERE school_id = $school_id ORDER BY class_name ASC";
$result = $conn->query($sql);

$classes = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $classes]);
?>