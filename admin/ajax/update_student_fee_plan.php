<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once '../sass/db_config.php';

$id = $_POST['id'] ?? '';
$base_amount = $_POST['base_amount'] ?? '';
$frequency = $_POST['frequency'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($id) || empty($base_amount) || empty($frequency) || empty($status)) {
  echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
  exit;
}

$stmt = $conn->prepare("UPDATE student_fee_plans SET base_amount = ?, frequency = ?, status = ? WHERE id = ?");
$stmt->bind_param("dssi", $base_amount, $frequency, $status, $id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();