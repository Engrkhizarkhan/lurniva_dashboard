<?php


// $conn = new mysqli("localhost", "root", "", "lurniva");
$conn = new mysqli("localhost", "dashboard_user", "lurniva@testVM", "lurniva_dashboard_db"); 

if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
  exit;
}

?>