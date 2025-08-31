<?php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

$school_id = $_SESSION['admin_id'];

$sql = "SELECT DISTINCT id, class_name FROM class_timetable_meta WHERE school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
  $classes[] = $row['class_name'];
}

echo json_encode(['status' => 'success', 'data' => $classes]);

$stmt->close();
$conn->close();