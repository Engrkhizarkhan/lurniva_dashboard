<?php


$conn = new mysqli("localhost", "root", "", "lurniva");

if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
  exit;
}

?>