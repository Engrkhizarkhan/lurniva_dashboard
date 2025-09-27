<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;

    if (!$id) {
        echo json_encode(['status' => 'danger', 'message' => 'Invalid ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM transport_student_routes WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Assignment removed successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'danger', 'message' => $e->getMessage()]);
    }
}
