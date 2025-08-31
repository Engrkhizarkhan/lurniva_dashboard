<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$id = intval($_POST['id']);
$school_id = intval($_SESSION['admin_id']);

if ($id > 0) {
  $stmt = $conn->prepare("DELETE FROM fee_types WHERE id = ? AND school_id = ?");
  $stmt->bind_param("ii", $id, $school_id);
  $stmt->execute();
  if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Fee type deleted']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Could not delete']);
  }
  $stmt->close();
} else {
  echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
}

$conn->close();