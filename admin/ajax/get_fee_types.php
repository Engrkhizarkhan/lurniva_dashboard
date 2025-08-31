<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'];

$sql = "SELECT id, fee_name, status FROM fee_types WHERE school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$fee_types = [];
while ($row = $result->fetch_assoc()) {
  $fee_types[] = $row;
}

echo json_encode($fee_types);

$stmt->close();
$conn->close();