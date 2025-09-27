<?php
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'danger', 'message' => 'Invalid driver ID']);
    exit;
}

$sql = "DELETE FROM drivers WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Driver deleted successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Failed to delete driver']);
}
$stmt->close();
$conn->close();
