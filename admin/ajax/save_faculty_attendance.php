<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access.");
}
$school_id = $_SESSION['admin_id'];

$attendance_date = $_POST['attendance_date'];
$faculty_ids = $_POST['faculty_ids'];

foreach ($faculty_ids as $fid) {
    $status = $_POST['status_' . $fid];

    // Check if attendance already exists for the day
    $check = $conn->prepare("SELECT id FROM faculty_attendance WHERE faculty_id = ? AND attendance_date = ?");
    $check->bind_param("is", $fid, $attendance_date);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO faculty_attendance (faculty_id, school_id, attendance_date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $fid, $school_id, $attendance_date, $status);
        $stmt->execute();
    }
}

echo "Attendance recorded successfully.";
?>