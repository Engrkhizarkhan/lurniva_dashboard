<?php
session_start();

require_once '../sass/db_config.php';

$type = $_POST['type'] ?? '';
$school_id = $_SESSION['admin_id']; // or dynamically get this if needed


if ($type == 'class') {
    $query = "SELECT id, class_name, section FROM class_timetable_meta WHERE school_id=$school_id ORDER BY class_name ASC";
    $result = $conn->query($query);

    echo '<option value="">-- Select Class --</option>';
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['class_name']} ({$row['section']})</option>";
    }
}

if ($type == 'student') {
    $query = "SELECT id, full_name, roll_number FROM students WHERE school_id=$school_id ORDER BY full_name ASC";
    $result = $conn->query($query);

    echo '<option value="">-- Select Student --</option>';
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['full_name']} (Roll: {$row['roll_number']})</option>";
    }
}