<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];

// Fetch DISTINCT exam names for this school
$sql = "SELECT DISTINCT exam_name 
        FROM exam_schedule 
        WHERE school_id = ? 
        ORDER BY exam_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row['exam_name'];
}

echo json_encode(['status'=>'success','data'=>$exams]);

$stmt->close();
$conn->close();
?>