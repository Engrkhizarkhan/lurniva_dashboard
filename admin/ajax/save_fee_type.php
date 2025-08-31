<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once '../sass/db_config.php';

// ✅ Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

$school_id = $data['school_id'] ?? '';
$fee_name = trim($data['fee_name'] ?? '');
$status = $data['status'] ?? '';
$edit_id = $data['id'] ?? '';

// ✅ Validate
if (empty($school_id) || empty($fee_name) || empty($status)) {
  echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
  exit;
}

// ✅ Insert or Update
if (empty($edit_id)) {
  // INSERT
  $stmt = $conn->prepare("INSERT INTO fee_types (school_id, fee_name, status, created_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("iss", $school_id, $fee_name, $status);

  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Fee type added successfully']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
  }
  $stmt->close();
} else {
  // UPDATE
  $stmt = $conn->prepare("UPDATE fee_types SET fee_name = ?, status = ? WHERE id = ? AND school_id = ?");
  $stmt->bind_param("ssii", $fee_name, $status, $edit_id, $school_id);

  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Fee type updated successfully']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
  }
  $stmt->close();
}

$conn->close();