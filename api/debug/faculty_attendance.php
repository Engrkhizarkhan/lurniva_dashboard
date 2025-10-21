<?php
session_start();
header('Content-Type: application/json');
require_once '../../admin/sass/db_config.php';


// ----------------------------
// ✅ Optional: Admin authentication
// ----------------------------


// ----------------------------
// Fetch faculty attendance
// ----------------------------
$sql = "SELECT `id`, `school_id`, `exam_name`, `total_marks`, `created_at` FROM `exams` WHERE 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Database query failed: ' . mysqli_error($conn)]);
    exit;
}

$attendance = [];
while ($row = mysqli_fetch_assoc($result)) {
    $attendance[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $attendance
]);
?>