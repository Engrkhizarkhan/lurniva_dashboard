<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id   = (int)$_SESSION['admin_id'];
$id          = (int)($_POST['id'] ?? 0);
$route_name  = trim($_POST['route_name'] ?? '');
$stops       = trim($_POST['stops'] ?? '');
$status      = $_POST['status'] ?? 'Active';

if ($id <= 0 || $route_name == '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE transport_routes 
                               SET route_name=?, stops=?, status=? 
                               WHERE id=? AND school_id=?");
mysqli_stmt_bind_param($stmt, "sssii", $route_name, $stops, $status, $id, $school_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Route updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update route']);
}
