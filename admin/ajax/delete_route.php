<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid route ID']);
    exit;
}

if (mysqli_query($conn, "DELETE FROM transport_routes WHERE id=$id")) {
    echo json_encode(['status' => 'success', 'message' => 'Route deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete route']);
}
