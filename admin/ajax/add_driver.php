<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'danger', 'message' => 'Session expired']);
    exit;
}

$school_id  = (int)$_SESSION['admin_id'];
$name       = trim($_POST['name'] ?? '');
$license_no = trim($_POST['license_no'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$bus_id     = (int)($_POST['bus_id'] ?? 0);
$status     = trim($_POST['status'] ?? 'Active');

if ($name == '' || $license_no == '') {
    echo json_encode(['status' => 'danger', 'message' => 'Name and License No are required']);
    exit;
}

$sql = "INSERT INTO drivers (school_id, name, license_no, phone, bus_id, status, created_at) 
        VALUES (?,?,?,?,?,?,NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssis", $school_id, $name, $license_no, $phone, $bus_id, $status);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Driver added successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Failed to add driver']);
}
$stmt->close();
$conn->close();
