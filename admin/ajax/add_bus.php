<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) ) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id  = (int)$_SESSION['admin_id'];
$bus_number = mysqli_real_escape_string($conn, $_POST['bus_number'] ?? '');
$capacity   = intval($_POST['capacity'] ?? 0);
$status     = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Active');

if ($bus_number === '' || $capacity <= 0) {
    echo json_encode(['status'=>'error','message'=>'Please provide valid bus number and capacity.']); 
    exit;
}

$sql = "INSERT INTO buses (school_id, bus_number, capacity, status) 
        VALUES ($school_id, '$bus_number', $capacity, '$status')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status'=>'success','message'=>'Bus added successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
