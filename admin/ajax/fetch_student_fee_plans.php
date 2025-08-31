<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'];

$sql = "SELECT sfp.*, s.full_name 
        FROM student_fee_plans sfp 
        JOIN students s ON sfp.student_id = s.id 
        WHERE sfp.school_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$plans = [];

while ($row = $result->fetch_assoc()) {
  $plans[] = $row;
}

echo json_encode(['status' => 'success', 'plans' => $plans]);
$stmt->close();
$conn->close();