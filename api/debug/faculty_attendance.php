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
$sql = "SELECT id, faculty_id, school_id, attendance_date, status, remarks, created_at FROM faculty_attendance ORDER BY attendance_date DESC";
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