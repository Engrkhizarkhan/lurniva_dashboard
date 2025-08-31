<?php
require_once '../sass/db_config.php';

$res = $conn->query("SELECT id, exam_name, total_marks, created_at FROM exams ORDER BY id DESC");

$exams = [];
while($row = $res->fetch_assoc()){
    $exams[] = $row;
}

echo json_encode([
    "status" => "success",
    "data"   => $exams
]);
?>
