<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['school_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id  = (int)$_SESSION['admin_id'];
$id         = (int)($_POST['id'] ?? 0);
$name       = trim($_POST['name'] ?? '');
$license_no = trim($_POST['license_no'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$bus_id     = (int)($_POST['bus_id'] ?? 0);
$status     = trim($_POST['status'] ?? 'Active');

if ($id <= 0 || $name == '' || $license_no == '') {
    echo json_encode(['status' => 'danger', 'message' => 'Invalid data']);
    exit;
}

$sql = "UPDATE drivers 
        SET name=?, license_no=?, phone=?, bus_id=?, status=? 
        WHERE id=? AND school_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssisii", $name, $license_no, $phone, $bus_id, $status, $id, $school_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Driver updated successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Failed to update driver']);
}

$stmt->close();
$conn->close();
