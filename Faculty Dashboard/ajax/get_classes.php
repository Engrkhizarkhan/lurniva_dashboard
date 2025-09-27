<?php
require_once '../sass/db_config.php';
session_start();

$school_id = $_SESSION['campus_id'] ?? 0;

// Fetch only this school’s finalized classes
$q = mysqli_query($conn, "
    SELECT id, class_name, section
    FROM class_timetable_meta
    WHERE school_id=$school_id AND is_finalized=1
    ORDER BY class_name, section
");

$options = "<option value=''>-- Select Class --</option>";
while($row = mysqli_fetch_assoc($q)){
    $class_name = htmlspecialchars($row['class_name']);
    $section = htmlspecialchars($row['section']);
    $options .= "<option value='{$row['id']}'>{$class_name} ({$section})</option>";
}

echo $options;
