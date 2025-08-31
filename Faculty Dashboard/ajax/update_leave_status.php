<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired!"]);
    exit;
}

// Get and validate inputs
$id = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

$validStatuses = ['Pending', 'Approved', 'Rejected'];
if ($id <= 0 || !in_array($status, $validStatuses)) {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
    exit;
}

// Update leave status
$sql = "UPDATE student_leaves SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Leave status updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Database error."]);
}