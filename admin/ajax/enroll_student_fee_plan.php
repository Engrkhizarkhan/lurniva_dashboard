<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once '../sass/db_config.php';

// ✅ Get POST data safely
$school_id = $_POST['school_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$fee_component = $_POST['fee_component'] ?? '';
$base_amount = $_POST['base_amount'] ?? '';
$frequency = $_POST['frequency'] ?? '';
$status = $_POST['status'] ?? '';

// ✅ Basic validation
if (empty($school_id) || empty($student_id) || empty($fee_component) || empty($base_amount) || empty($frequency) || empty($status)) {
  echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
  exit;
}

// ✅ Insert into DB
$stmt = $conn->prepare("INSERT INTO student_fee_plans (school_id, student_id, fee_component, base_amount, frequency, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
  echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
  exit;
}

$stmt->bind_param("iisdss", $school_id, $student_id, $fee_component, $base_amount, $frequency, $status);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success', 'message' => 'Student fee plan enrolled successfully']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();