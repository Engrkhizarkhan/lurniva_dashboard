<?php
session_start();
require_once '../sass/db_config.php';

$class_id = $_POST['class_id'] ?? 0;
$school_id = $_SESSION['campus_id'] ?? 0;

if($class_id > 0){
    $stmt = $conn->prepare("SELECT id, full_name, roll_number FROM students WHERE class_grade = ? AND section = ? AND school_id = ? AND status = 'active' ORDER BY roll_number ASC");

    // Fetch class meta for grade and section
    $metaStmt = $conn->prepare("SELECT class_name, section FROM class_timetable_meta WHERE id = ? AND school_id = ?");
    $metaStmt->bind_param("ii", $class_id, $school_id);
    $metaStmt->execute();
    $metaResult = $metaStmt->get_result();
    $classMeta = $metaResult->fetch_assoc();

    if($classMeta){
        $class_grade = $classMeta['class_name'];
        $section = $classMeta['section'];

        $stmt->bind_param("ssi", $class_grade, $section, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        while($row = $result->fetch_assoc()){
            $students[] = $row;
        }

        echo json_encode($students);
    } else {
        echo json_encode([]);
    }

} else {
    echo json_encode([]);
}