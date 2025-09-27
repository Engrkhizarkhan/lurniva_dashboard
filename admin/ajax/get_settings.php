<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

$school_id = $_SESSION['admin_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM school_settings WHERE person_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0) {
    echo json_encode(['status' => 'success', 'data' => $res->fetch_assoc()]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Settings not found']);
}
