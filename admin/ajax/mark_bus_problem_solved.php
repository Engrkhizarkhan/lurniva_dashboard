<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if($id <= 0){
    echo json_encode(['status'=>'error','message'=>'Invalid ID']);
    exit;
}

$sql = "UPDATE bus_problems SET status='Solved' WHERE id=$id";
if(mysqli_query($conn, $sql)){
    echo json_encode(['status'=>'success','message'=>'Problem marked as solved']);
}else{
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
