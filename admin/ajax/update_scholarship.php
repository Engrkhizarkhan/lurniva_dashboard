<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$id = $_POST['id'];
$type = $_POST['type'];
$amount = $_POST['amount'];
$reason = $_POST['reason'];
$status = 'pending';

$stmt = $conn->prepare("UPDATE scholarships SET type = ?, amount = ?, reason = ?, status = ? WHERE id = ?");
$stmt->bind_param("sdssi", $type, $amount, $reason, $status, $id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}