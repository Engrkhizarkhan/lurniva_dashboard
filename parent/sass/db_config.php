<?php


// $conn = new mysqli("localhost", "root", "", "lurniva");
$conn = new mysqli("localhost", "lurnivauser", "lurniva@testVM", "lurnivaDB"); 

if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
  exit;
}

?>