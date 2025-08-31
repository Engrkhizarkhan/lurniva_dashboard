<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$school_id = $_POST['school_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$type = $_POST['type'] ?? '';
$amount = $_POST['amount'] ?? '';
$reason = $_POST['reason'] ?? '';
$status =  'pending';

if (!$school_id || !$student_id || !$type || !$amount || !$reason || !$status) {
  echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO scholarships (school_id, student_id, type, amount, reason, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdss", $school_id, $student_id, $type, $amount, $reason, $status);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>