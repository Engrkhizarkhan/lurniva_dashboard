<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];

$sql = "SELECT bp.id, b.bus_number, bp.problem, bp.status, bp.created_at
        FROM bus_problems bp
        JOIN buses b ON b.id = bp.bus_id
        WHERE bp.school_id=$school_id AND bp.status='Open'
        ORDER BY bp.created_at DESC";

$res = mysqli_query($conn, $sql);
$data = [];
while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}

echo json_encode(['status'=>'success','data'=>$data]);
?>
