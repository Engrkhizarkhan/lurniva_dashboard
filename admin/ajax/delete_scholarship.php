<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$id = $_POST['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}