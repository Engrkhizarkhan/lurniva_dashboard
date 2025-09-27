<?php
require_once '../sass/db_config.php';
session_start();

$school_id = $_SESSION['campus_id'] ?? 0;
$class_id  = intval($_GET['class_id'] ?? 0);

if($school_id <= 0 || $class_id <= 0){
    echo "<option value=''>Invalid request</option>";
    exit;
}

// Step 1: Get class_name + section
$classQ = mysqli_query($conn, "
    SELECT class_name, section 
    FROM class_timetable_meta 
    WHERE id=$class_id AND school_id=$school_id
    LIMIT 1
");
$class = mysqli_fetch_assoc($classQ);

if(!$class){
    echo "<option value=''>Class not found</option>";
    exit;
}

$class_name = mysqli_real_escape_string($conn, $class['class_name']);
$section    = mysqli_real_escape_string($conn, $class['section']);

// Step 2: Fetch students of this class + section
$q = mysqli_query($conn, "
    SELECT id, full_name, roll_number 
    FROM students 
    WHERE class_grade='$class_name' 
      AND section='$section' 
      AND school_id=$school_id
    ORDER BY full_name ASC
");

$options = "";
while($row = mysqli_fetch_assoc($q)){
    $roll   = htmlspecialchars($row['roll_number']);
    $name   = htmlspecialchars($row['full_name']);
    $id     = $row['id'];
    $options .= "<option value='{$id}'>{$roll} - {$name}</option>";
}

echo $options ?: "<option value=''>No Students Found</option>";
