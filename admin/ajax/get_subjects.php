<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$class_name = $_POST['class_name'] ?? '';
$school_id = $_SESSION['admin_id'];

$sql = "SELECT d.id, d.period_name 
        FROM class_timetable_details d
        JOIN class_timetable_meta m ON d.timing_meta_id = m.id
        WHERE m.school_id = ? AND m.class_name = ? AND d.period_type = 'Normal'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $school_id, $class_name);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while($row = $result->fetch_assoc()){
    $subjects[] = $row;
}

echo json_encode(['status'=>'success','data'=>$subjects]);

$stmt->close();
$conn->close();
?>