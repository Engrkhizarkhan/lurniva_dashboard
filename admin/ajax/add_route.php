<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$school_id  = (int)$_SESSION['admin_id'];
$route_name = trim($_POST['route_name'] ?? '');
$stops      = trim($_POST['stops'] ?? '');
$status     = $_POST['status'] ?? 'Active';

if ($route_name == '') {
    echo json_encode(['status' => 'error', 'message' => 'Route name is required']);
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO transport_routes (school_id, route_name, stops, status) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "isss", $school_id, $route_name, $stops, $status);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Route added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add route: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
