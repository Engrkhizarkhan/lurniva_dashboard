<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)$_SESSION['admin_id'];

$q = mysqli_query($conn, "
    SELECT id, title, available 
    FROM books 
    WHERE available > 0 AND school_id = $school_id 
    ORDER BY title
");

$data = [];
while ($row = mysqli_fetch_assoc($q)) {
    $data[] = $row;
}

echo json_encode(['status'=>'success','data'=>$data]);
