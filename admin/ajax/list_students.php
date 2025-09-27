<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);

try {
    $sql = "SELECT id, full_name AS name 
            FROM students 
            WHERE school_id = ? 
            ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
