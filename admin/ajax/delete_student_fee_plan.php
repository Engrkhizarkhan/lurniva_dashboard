<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once '../sass/db_config.php';

$id = $_POST['id'] ?? '';

if (empty($id)) {
  echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM student_fee_plans WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();