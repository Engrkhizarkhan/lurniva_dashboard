<?php


// $conn = new mysqli("localhost", "root", "", "lurniva");
$conn = new mysqli("localhost", "lurnivauser", "lurniva@testVM", "lurnivaDB"); 

if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
  exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

?>