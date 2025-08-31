<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status, ['Approved', 'Rejected'])) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
  exit;
}

$sql = "UPDATE faculty_leaves SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => "Leave has been $status"]);
?>