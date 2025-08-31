<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

// Get POST
$fee_structure_id = $_POST['fee_structure_id'] ?? '';
$frequency = $_POST['frequency'] ?? '';
$status = $_POST['status'] ?? '';

if (!$fee_structure_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fee_structure_id']);
    exit;
}

$stmt = $conn->prepare("UPDATE fee_structures SET frequency = ?, status = ? WHERE id = ?");
$stmt->bind_param("ssi", $frequency, $status, $fee_structure_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'fee_structure_id' => $fee_structure_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
exit;